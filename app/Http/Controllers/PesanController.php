<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Models\Anggota;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PesanController extends Controller
{
    public function index(Request $request)
    {
        // Hapus pesan yang sudah dibaca dan umurnya lebih dari 1 hari sejak dibaca (updated_at)
        Pesan::where('is_read', true)
            ->where('updated_at', '<=', now()->subDay())
            ->delete();

        $user = Auth::user();
        
        $pesanMasuk = Pesan::with('sender.tingkat')
            ->where('recipient_id', $user->id_anggota)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $pesanTerkirim = Pesan::with('recipient.tingkat')
            ->where('sender_id', $user->id_anggota)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $query = Anggota::where('id_anggota', '!=', $user->id_anggota)
            ->where('deletestatus', '2');

        if ($user->role === 'admin') {
            $anggotas = $query->get();
        } elseif ($user->role === 'operator') {
            $id_tugas = $user->id_tugas;
            $dots = substr_count((string)$id_tugas, '.');
            
            $anggotas = $query->where(function($q) use ($dots, $id_tugas) {
                if ($dots == 1) {
                    // Operator Polres: Ke Admin dan Operator Polsek Jajaran
                    $q->where('role', 'admin')
                      ->orWhere(function($subQ) use ($id_tugas) {
                          $subQ->where('role', 'operator')
                               ->where('id_tugas', 'LIKE', $id_tugas . '.%');
                      });
                } elseif ($dots >= 2) {
                    // Operator Polsek: Ke Operator Polres
                    $parent_tugas = substr($id_tugas, 0, strrpos($id_tugas, '.'));
                    $q->where('role', 'operator')
                      ->where('id_tugas', $parent_tugas);
                } else {
                    $q->where('role', 'admin');
                }
            })->get();
        } else {
            $anggotas = collect();
        }

        $isPolres = ($user->role === 'operator' && substr_count((string)$user->id_tugas, '.') == 1);
        $isPolsek = ($user->role === 'operator' && substr_count((string)$user->id_tugas, '.') >= 2);
        
        $role = $user->role === 'admin' ? 'admin' : 'operator';

        return view($role . '.pesan.index', compact('pesanMasuk', 'pesanTerkirim', 'anggotas', 'role', 'isPolres', 'isPolsek'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|string',
            'judul' => 'nullable|string|max:255',
            'isi_pesan' => 'required|string'
        ]);

        $user = Auth::user();

        // Check if recipient is a role
        if (str_starts_with($request->recipient_id, 'role_')) {
            $roleTarget = str_replace('role_', '', $request->recipient_id);
            $query = Anggota::where('role', $roleTarget)
                ->where('deletestatus', '2')
                ->where('id_anggota', '!=', $user->id_anggota);
                
            if ($user->role === 'operator') {
                $id_tugas = $user->id_tugas;
                $dots = substr_count((string)$id_tugas, '.');
                
                if ($roleTarget === 'operator' && $dots == 1) {
                    // Polres send to all Polsek
                    $query->where('id_tugas', 'LIKE', $id_tugas . '.%');
                } elseif ($roleTarget === 'admin' && $dots == 1) {
                    // allowed
                } else {
                    $query->where('id_tugas', 'invalid_no_access');
                }
            }

            $recipients = $query->get();

            foreach ($recipients as $recipient) {
                Pesan::create([
                    'id_pesan' => Str::uuid(),
                    'sender_id' => $user->id_anggota,
                    'recipient_id' => $recipient->id_anggota,
                    'judul' => $request->judul ?? 'Tanpa Judul',
                    'isi_pesan' => $request->isi_pesan,
                    'is_read' => false
                ]);
            }
        } else {
            // Direct message - validate authorization
            $isAuthorized = false;
            if ($user->role === 'admin') {
                $isAuthorized = true;
            } else {
                $recipient = Anggota::find($request->recipient_id);
                if ($recipient) {
                    $id_tugas = $user->id_tugas;
                    $dots = substr_count((string)$id_tugas, '.');
                    if ($dots == 1) {
                        if ($recipient->role === 'admin' || ($recipient->role === 'operator' && str_starts_with($recipient->id_tugas, $id_tugas . '.'))) {
                            $isAuthorized = true;
                        }
                    } elseif ($dots >= 2) {
                        $parent_tugas = substr($id_tugas, 0, strrpos($id_tugas, '.'));
                        if ($recipient->role === 'operator' && $recipient->id_tugas === $parent_tugas) {
                            $isAuthorized = true;
                        }
                    }
                }
            }

            if (!$isAuthorized) {
                return back()->with('error', 'Anda tidak memiliki izin untuk mengirim pesan ke pengguna tersebut.');
            }

            Pesan::create([
                'id_pesan' => Str::uuid(),
                'sender_id' => $user->id_anggota,
                'recipient_id' => $request->recipient_id,
                'judul' => $request->judul ?? 'Tanpa Judul',
                'isi_pesan' => $request->isi_pesan,
                'is_read' => false
            ]);
        }

        return back()->with('success', 'Pesan berhasil dikirim!');
    }

    public function markAllAsRead()
    {
        Pesan::where('recipient_id', Auth::user()->id_anggota)
            ->where('is_read', false)
            ->update(['is_read' => true]);
            
        return response()->json(['success' => true]);
    }

    public function markAsRead($id)
    {
        $pesan = Pesan::findOrFail($id);
        
        if ($pesan->recipient_id === Auth::user()->id_anggota) {
            $pesan->update(['is_read' => true]);
        }
        
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $pesan = Pesan::findOrFail($id);
        
        // Only allow sender or recipient to delete the message
        if ($pesan->sender_id == Auth::user()->id_anggota || $pesan->recipient_id == Auth::user()->id_anggota) {
            $pesan->delete();
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array'
        ]);

        $ids = $request->input('ids', []);
        $userId = Auth::user()->id_anggota;
        
        Pesan::whereIn('id', $ids)
            ->where(function($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('recipient_id', $userId);
            })
            ->delete();
            
        return response()->json(['success' => true]);
    }
}
