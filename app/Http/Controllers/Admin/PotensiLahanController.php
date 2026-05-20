<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasLog;
use App\Models\PotensiLahan;
use App\Models\Pesan;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PotensiLahanController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getIndexData($request);
        return view('admin.kelola-lahan.potensi.index', $data);
    }

    public function indexOperator(Request $request)
    {
        $data = $this->getIndexData($request);
        return view('operator.kelola-lahan.operator_potensi.operator_kelola_index', $data);
    }

    public function indexView(Request $request)
    {
        $data = $this->getIndexData($request);
        return view('view.kelola-lahan.view_potensi.view_kelola_index', $data);
    }

    private function getIndexData(Request $request)
    {
        // ===========================
        // DATA STATISTIK (di blade)
        // ===========================
        $summary = ['total_ha' => '0'];
        $cats    = [];

        // ===========================
        // DATA TABEL: DAFTAR LAHAN
        // Grouped per Kabupaten
        // ===========================

        // 1. Ambil semua anggota untuk lookup nama
        $anggotaMap = DB::table('anggota')->pluck('nama_anggota', 'id_anggota');

        // 3. Ambil semua wilayah untuk lookup nama kab/kec/desa
        $wilayahMap = DB::table('wilayah')->pluck('nama_wilayah', 'id_wilayah');

        $search = $request->input('search', '');
        $resorFilter = $request->input('resor', '');
        $sektorFilter = $request->input('sektor', '');
        $jenisFilter = $request->input('jenis', '');
        $validasiFilter = $request->input('validasi', '');
        $startDate = $request->input('start_date', '');
        $endDate = $request->input('end_date', '');

        // 2. Ambil semua data lahan (aktif) dipaginasi dengan filter search
        $lahanQuery = DB::table('lahan')
            ->leftJoin('poktan', 'lahan.id_poktan', '=', 'poktan.id_poktan')
            ->select('lahan.*', 'poktan.nama_poktan')
            ->where('lahan.deletestatus', '!=', '0')
            ->orderBy('lahan.id_wilayah');

        if ($search) {
            $lahanQuery->where(function ($q) use ($search, $wilayahMap) {
                $q->where('id_lahan', $search)
                    ->orWhere('alamat_lahan', 'like', "%{$search}%")
                    ->orWhere('cp_polisi', 'like', "%{$search}%")
                    ->orWhere('cp_lahan', 'like', "%{$search}%")
                    ->orWhere('poktan', 'like', "%{$search}%")
                    ->orWhere('id_wilayah', 'like', "%{$search}%");

                foreach ($wilayahMap as $wId => $wNama) {
                    if (stripos($wNama, $search) !== false) {
                        $q->orWhere('id_wilayah', 'like', "{$wId}%");
                    }
                }
            });
        }

        if ($resorFilter) {
            $lahanQuery->where('id_tingkat', 'like', $resorFilter . '%');
        }
        if ($sektorFilter) {
            $lahanQuery->where('id_tingkat', $sektorFilter);
        }
        if ($jenisFilter) {
            $lahanQuery->where('id_jenis_lahan', $jenisFilter);
        }
        if ($validasiFilter === 'sudah') {
            $lahanQuery->whereNotNull('valid_oleh');
        } elseif ($validasiFilter === 'belum') {
            $lahanQuery->whereNull('valid_oleh');
        }
        if ($startDate) {
            $lahanQuery->whereDate('tgl_edit', '>=', $startDate);
        }
        if ($endDate) {
            $lahanQuery->whereDate('tgl_edit', '<=', $endDate);
        }

        $allLahanData = (clone $lahanQuery)->get();
        $lahanList = $lahanQuery->paginate(25)->withQueryString();

        // Lookup nama tingkat dan komoditi
        $tingkatMap  = DB::table('tingkat')->pluck('nama_tingkat', 'id_tingkat');
        $komoditiMap = DB::table('komoditi')->get()->keyBy('id_komoditi');

        // 4. Transform data untuk view
        $lahanList->getCollection()->transform(function ($lahan) use ($wilayahMap, $anggotaMap, $tingkatMap, $komoditiMap) {
            $parts = explode('.', $lahan->id_wilayah);

            // Resolve Kabupaten (format: XX.XX)
            $kabId   = count($parts) >= 2 ? $parts[0] . '.' . $parts[1] : $lahan->id_wilayah;
            $kabNama = $wilayahMap[$kabId] ?? ('Wilayah ' . $kabId);

            // Resolve Kecamatan (format: XX.XX.XXX)
            $kecId   = count($parts) >= 3 ? $parts[0] . '.' . $parts[1] . '.' . $parts[2] : ($kabId . '.000');
            $kecNama = $wilayahMap[$kecId] ?? ('Kec. ' . $kecId);

            // Resolve Desa (full id_wilayah)
            $desaNama = $wilayahMap[$lahan->id_wilayah] ?? $lahan->id_wilayah;

            // Resolve edit_oleh & valid_oleh
            $editNama  = $lahan->edit_oleh  ? ($anggotaMap[$lahan->edit_oleh]  ?? $lahan->edit_oleh)  : null;
            $validNama = $lahan->valid_oleh ? ($anggotaMap[$lahan->valid_oleh] ?? $lahan->valid_oleh) : null;

            // Resolve Polres & Polsek dari id_tingkat
            $idTingkat  = $lahan->id_tingkat ?? '';
            $dotCount   = substr_count($idTingkat, '.');
            if ($dotCount >= 2) {
                // Polsek: X.XX.XX → Polres: X.XX
                $parts2 = explode('.', $idTingkat);
                $polresId   = $parts2[0] . '.' . $parts2[1];
                $polsekId   = $idTingkat;
            } else {
                $polresId   = $idTingkat;
                $polsekId   = null;
            }
            $namaPolres = $tingkatMap[$polresId] ?? $polresId;
            $namaPolsek = $polsekId ? ($tingkatMap[$polsekId] ?? $polsekId) : '-';

            // Resolve Komoditi
            $km = $komoditiMap[$lahan->id_komoditi] ?? null;
            $namaKomoditi = $km ? ($km->jenis_komoditi . ' - ' . $km->nama_komoditi) : '-';

            return [
                'id_lahan'           => $lahan->id_lahan,
                'id_tingkat'         => $lahan->id_tingkat,
                'nama_polres'        => $namaPolres,
                'nama_polsek'        => $namaPolsek,
                'cp_lahan'           => $lahan->cp_lahan,
                'no_cp_lahan'        => $lahan->no_cp_lahan,
                'cp_polisi'          => $lahan->cp_polisi,
                'no_cp_polisi'       => $lahan->no_cp_polisi,
                'ket_polisi'         => $lahan->ket_polisi,
                'alamat_lahan'       => $lahan->alamat_lahan,
                'longitude'          => $lahan->longitude,
                'latitude'           => $lahan->latitude,
                'luas_lahan'         => $lahan->luas_lahan,
                'id_poktan'          => $lahan->id_poktan,
                'poktan'             => $lahan->nama_poktan,
                'jml_petani'         => $lahan->jml_petani,
                'id_jenis_lahan'     => $lahan->id_jenis_lahan,
                'nama_komoditi'      => $namaKomoditi,
                'keterangan_lahan'   => $lahan->keterangan_lahan,
                'ket_polisi'         => $lahan->ket_polisi,
                'dokumentasi_lahan'  => $lahan->dokumentasi_lahan,
                'status_lahan'       => $lahan->status_lahan,
                'edit_oleh'          => $editNama,
                'tgl_edit'           => $lahan->tgl_edit,
                'valid_oleh'         => $validNama,
                'tgl_valid'          => $lahan->tgl_valid,
                'kec_nama'           => $kecNama,
                'desa_nama'          => $desaNama,
                'kab_nama'           => $kabNama,
                'id_wilayah'         => $lahan->id_wilayah,
                'id_komoditi'        => $lahan->id_komoditi,
                'wilayah_label'      => 'Desa ' . $desaNama . ' Kecamatan ' . $kecNama . ' Kabupaten ' . $kabNama,
            ];
        });

        $polresList = DB::table('tingkat')
            ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+$'")
            ->orderBy('id_tingkat')
            ->get();

        $polsekList = DB::table('tingkat')
            ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+$'")
            ->orderBy('id_tingkat')
            ->get();

        $kategoriMapping = [
            1 => 'PRODUKTIF (POKTAN BINAAN POLRI)',
            2 => 'HUTAN (PERHUTANAN SOSIAL)',
            3 => 'LUAS BAKU SAWAH (LBS)',
            4 => 'PESANTREN',
            5 => 'MILIK POLRI',
            6 => 'PRODUKTIF (MASYARAKAT BINAAN POLRI)',
            7 => 'PRODUKTIF (TUMPANG SARI)',
            8 => 'HUTAN (PERHUTANI/INHUTANI)',
            9 => 'LAHAN LAINNYA'
        ];

        $komoditiList = DB::table('komoditi')->where('deletestatus', '!=', '0')->get();

        $wilayahSemua = DB::table('wilayah')->get();
        // Format Kabupaten: 35.XX (1 dot)
        $kabupatenList = $wilayahSemua->filter(function ($w) {
            return substr_count($w->id_wilayah, '.') == 1;
        })->values();
        // Format Kecamatan: 35.XX.XX (2 dots)
        $kecamatanList = $wilayahSemua->filter(function ($w) {
            return substr_count($w->id_wilayah, '.') == 2;
        })->values();
        // Format Desa: 35.XX.XX.XXXX (3 dots)
        $desaList = $wilayahSemua->filter(function ($w) {
            return substr_count($w->id_wilayah, '.') == 3;
        })->values();

        $anggotaList = DB::table('anggota')
            ->where('deletestatus', '!=', '0')
            ->select('id_anggota', 'nama_anggota', 'no_telp_anggota', 'id_tugas')
            ->get();

        $poktanList = \App\Models\Poktan::all();

        $filters = [
            'search' => $search,
            'resor' => $resorFilter,
            'sektor' => $sektorFilter,
            'jenis' => $jenisFilter,
            'validasi' => $validasiFilter,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        return compact('summary', 'cats', 'lahanList', 'polresList', 'polsekList', 'kategoriMapping', 'komoditiList', 'kabupatenList', 'kecamatanList', 'desaList', 'anggotaList', 'poktanList', 'filters', 'allLahanData');
    }

    private function resolvePoktan(Request $request)
    {
        $idPoktan = $request->id_poktan;
        $namaPoktan = $request->jml_poktan ? strtoupper($request->jml_poktan) : null;
        
        if (empty($idPoktan) || $idPoktan === 'new') {
            if (!empty($namaPoktan)) {
                $idSektor = $request->id_sektor;
                $idResor = $request->id_resor;
                $idPolda = null;
                
                if ($idResor) {
                    $parts = explode('.', $idResor);
                    if (count($parts) > 0) $idPolda = $parts[0];
                }
                
                $newPoktan = \App\Models\Poktan::create([
                    'id_polda' => $idPolda,
                    'id_polres' => $idResor,
                    'id_polsek' => $idSektor,
                    'nama_poktan' => $namaPoktan,
                    'luas_lahan' => $request->luas_lahan,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]);
                return $newPoktan->id_poktan;
            } else {
                return null;
            }
        }
        return $idPoktan;
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_sektor'        => 'nullable|string',
            'id_resor'         => 'nullable|string',
            'id_desa'          => 'required|string',
            'id_jenis_lahan'   => 'required|integer',
            'luas_lahan'       => 'required|numeric|min:0',
            'id_anggota'       => 'nullable|integer',
            'cp_lahan'         => 'nullable|string|max:255',
            'no_cp_lahan'      => 'nullable|string|max:50',
            'cp_polisi'        => 'nullable|string|max:255',
            'no_cp_polisi'     => 'nullable|string|max:50',
            'latitude'         => 'nullable|string|max:50',
            'longitude'        => 'nullable|string|max:50',
            'alamat_lahan'     => 'required|string|max:500',
            'ket_pj'           => 'nullable|string|max:1000',
            'jml_poktan'       => 'nullable|string|max:255',
            'id_poktan'        => 'nullable|string',
            'jml_petani'       => 'nullable|integer|min:0',
            'id_komoditi'      => 'nullable|integer',
            'keterangan_lain'  => 'nullable|string|max:1000',
            'dokumentasi_lahan'=> 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $idPoktan = $this->resolvePoktan($request);

        $data = [
            'id_tingkat'        => $request->id_sektor ?? $request->id_resor,
            'id_wilayah'        => $request->id_desa,
            'id_jenis_lahan'    => $request->id_jenis_lahan,
            'luas_lahan'        => $request->luas_lahan,
            'id_anggota'        => $request->id_anggota,
            'cp_lahan'          => $request->cp_lahan,
            'no_cp_lahan'       => $request->no_cp_lahan,
            'cp_polisi'         => $request->cp_polisi,  // nama polisi penggerak
            'no_cp_polisi'      => $request->no_cp_polisi,
            'latitude'          => $request->latitude,
            'longitude'         => $request->longitude,
            'alamat_lahan'      => $request->alamat_lahan,
            'keterangan_lahan'  => $request->ket_pj,
            'poktan'            => 1, // Fixed: The column is integer for the number of poktans
            'id_poktan'         => $idPoktan,
            'jml_petani'        => $request->jml_petani,
            'id_komoditi'       => $request->id_komoditi,
            'ket_polisi'        => $request->keterangan_lain,
            'edit_oleh'         => auth()->user() ? auth()->user()->id_anggota : null,
            'tgl_edit'          => Carbon::now()
        ];

        if ($request->hasFile('dokumentasi_lahan')) {
            $file = $request->file('dokumentasi_lahan');
            $filename = time() . '_' . $file->hashName(); // Security improvement: use hashName to avoid path traversal and execute attacks
            // Move file to public/storage/dokumentasi (or public/dokumentasi)
            $file->move(public_path('storage/dokumentasi'), $filename);
            $data['dokumentasi_lahan'] = 'storage/dokumentasi/' . $filename;
        }

        $data['id_lahan'] = DB::table('lahan')->max('id_lahan') + 1;

        DB::table('lahan')->insert($data);

        AktivitasLog::catat('create', 'potensi_lahan', [
            'record_id'   => $data['id_lahan'],
            'label_modul' => 'Lahan #' . $data['id_lahan'] . ' - ' . ($request->alamat_lahan ?? ''),
            'data_baru'   => Arr::except($data, ['edit_oleh', 'tgl_edit']),
            'keterangan'  => 'Tambah data potensi lahan baru #' . $data['id_lahan'] . ', luas ' . $request->luas_lahan . ' Ha',
        ]);

        return response()->json(['success' => true, 'message' => 'Data berhasil disimpan']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_sektor'        => 'nullable|string',
            'id_resor'         => 'nullable|string',
            'id_desa'          => 'required|string',
            'id_jenis_lahan'   => 'required|integer',
            'luas_lahan'       => 'required|numeric|min:0',
            'id_anggota'       => 'nullable|integer',
            'cp_lahan'         => 'nullable|string|max:255',
            'no_cp_lahan'      => 'nullable|string|max:50',
            'cp_polisi'        => 'nullable|string|max:255',
            'no_cp_polisi'     => 'nullable|string|max:50',
            'latitude'         => 'nullable|string|max:50',
            'longitude'        => 'nullable|string|max:50',
            'alamat_lahan'     => 'required|string|max:500',
            'ket_pj'           => 'nullable|string|max:1000',
            'jml_poktan'       => 'nullable|string|max:255',
            'id_poktan'        => 'nullable|string',
            'jml_petani'       => 'nullable|integer|min:0',
            'id_komoditi'      => 'nullable|integer',
            'keterangan_lain'  => 'nullable|string|max:1000',
            'dokumentasi_lahan'=> 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $idPoktan = $this->resolvePoktan($request);

        $data = [
            'id_tingkat'        => $request->id_sektor ?? $request->id_resor,
            'id_wilayah'        => $request->id_desa,
            'id_jenis_lahan'    => $request->id_jenis_lahan,
            'luas_lahan'        => $request->luas_lahan,
            'id_anggota'        => $request->id_anggota,
            'cp_lahan'          => $request->cp_lahan,
            'no_cp_lahan'       => $request->no_cp_lahan,
            'cp_polisi'         => $request->cp_polisi,
            'no_cp_polisi'      => $request->no_cp_polisi,
            'latitude'          => $request->latitude,
            'longitude'         => $request->longitude,
            'alamat_lahan'      => $request->alamat_lahan,
            'keterangan_lahan'  => $request->ket_pj,
            'poktan'            => 1, // Fixed: The column is integer for the number of poktans
            'id_poktan'         => $idPoktan,
            'jml_petani'        => $request->jml_petani,
            'id_komoditi'       => $request->id_komoditi,
            'ket_polisi'        => $request->keterangan_lain,
            'edit_oleh'         => auth()->user() ? auth()->user()->id_anggota : null,
            'tgl_edit'          => Carbon::now(),
            'valid_oleh'        => null,
            'tgl_valid'         => null,
            'status_lahan'      => '0',
        ];

        if ($request->hasFile('dokumentasi_lahan')) {
            $file = $request->file('dokumentasi_lahan');
            $filename = time() . '_' . $file->hashName(); // Security improvement: use hashName
            $file->move(public_path('storage/dokumentasi'), $filename);
            $data['dokumentasi_lahan'] = 'storage/dokumentasi/' . $filename;
        }

        $old = DB::table('lahan')->where('id_lahan', $id)->first();
        DB::table('lahan')->where('id_lahan', $id)->update($data);
        
        // Bersihkan poktan yatim jika id_poktan berubah
        if ($old && $old->id_poktan && $old->id_poktan != $idPoktan) {
            $stillUsed = DB::table('lahan')->where('id_poktan', $old->id_poktan)->where('deletestatus', '!=', '0')->exists();
            if (!$stillUsed) {
                DB::table('poktan')->where('id_poktan', $old->id_poktan)->delete();
            }
        }

        AktivitasLog::catat('update', 'potensi_lahan', [
            'record_id'   => $id,
            'label_modul' => 'Lahan #' . $id . ' - ' . ($request->alamat_lahan ?? ''),
            'data_baru'   => array_except($data, ['edit_oleh', 'tgl_edit']),
            'keterangan'  => 'Edit data potensi lahan #' . $id . ', luas jadi ' . $request->luas_lahan . ' Ha',
        ]);

        return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui']);
    }

    public function tolakValidasi(Request $request, $id)
    {
        if (auth()->user() && auth()->user()->role === 'view') {
            abort(403, 'Anda tidak memiliki izin untuk menolak validasi.');
        }

        $request->validate([
            'alasan_penolakan' => 'required|string|max:1000',
        ], [
            'alasan_penolakan.required' => 'Alasan penolakan harus diisi.',
        ]);

        $lahan = DB::table('lahan')->where('id_lahan', $id)->first();
        if (!$lahan) {
            return response()->json(['success' => false, 'message' => 'Data lahan tidak ditemukan.'], 404);
        }

        $user    = auth()->user();
        $alasan  = $request->input('alasan_penolakan');
        $penolak = $user->nama_anggota ?? $user->username ?? 'Admin';

        // Update status lahan menjadi '2' (Ditolak)
        DB::table('lahan')->where('id_lahan', $id)->update([
            'status_lahan' => '2',
            'valid_oleh'   => null,
            'tgl_valid'    => null,
            'ket_polisi'   => '[DITOLAK] ' . $alasan,
            'tgl_edit'     => Carbon::now(),
        ]);

        // Kirim pesan otomatis ke pembuat laporan (edit_oleh)
        $pembuatId = $lahan->edit_oleh;
        if ($pembuatId) {
            // Cari id_anggota dari username jika edit_oleh menyimpan username
            $pembuat = DB::table('anggota')
                ->where('id_anggota', $pembuatId)
                ->orWhere('username', $pembuatId)
                ->first();

            if ($pembuat) {
                $alamat = $lahan->alamat_lahan ?? 'Lahan #' . $id;
                Pesan::create([
                    'id_pesan'     => Str::uuid(),
                    'sender_id'    => $user->id_anggota ?? 0,
                    'recipient_id' => $pembuat->id_anggota,
                    'judul'        => '❌ Penolakan Validasi Potensi Lahan #' . $id,
                    'isi_pesan'    =>
                        "Potensi lahan yang Anda laporkan telah **DITOLAK** oleh {$penolak}.\n\n" .
                        "📍 **Lokasi Lahan:** {$alamat}\n" .
                        "🆔 **ID Lahan:** #{$id}\n\n" .
                        "📝 **Alasan Penolakan:**\n{$alasan}\n\n" .
                        "Silakan perbaiki data dan ajukan kembali laporan potensi lahan Anda.",
                    'is_read'      => false,
                ]);
            }
        }

        AktivitasLog::catat('tolak_validasi', 'potensi_lahan', [
            'record_id'   => $id,
            'label_modul' => 'Lahan #' . $id,
            'keterangan'  => 'Tolak validasi potensi lahan #' . $id . '. Alasan: ' . $alasan,
        ]);

        return response()->json(['success' => true, 'message' => 'Validasi lahan berhasil ditolak dan notifikasi telah dikirim.']);
    }

    public function validasi(Request $request, $id) {
        // Hanya admin dan operator yang boleh memvalidasi
        if (auth()->user() && auth()->user()->role === 'view') {
            abort(403, 'Anda tidak memiliki izin untuk melakukan validasi.');
        }

        DB::table('lahan')->where('id_lahan', $id)->update([
            'valid_oleh' => auth()->user() ? auth()->user()->username : 'system',
            'tgl_valid'  => Carbon::now(),
        ]);

        AktivitasLog::catat('validasi', 'potensi_lahan', [
            'record_id'   => $id,
            'label_modul' => 'Lahan #' . $id,
            'keterangan'  => 'Validasi potensi lahan #' . $id,
        ]);

        return redirect()->back()->with('success', 'Data lahan berhasil divalidasi.');
    }

    public function unvalidasi(Request $request, $id) {
        // Hanya admin dan operator yang boleh membatalkan validasi
        if (auth()->user() && auth()->user()->role === 'view') {
            abort(403, 'Anda tidak memiliki izin untuk membatalkan validasi.');
        }

        DB::table('lahan')->where('id_lahan', $id)->update([
            'valid_oleh' => null,
            'tgl_valid'  => null,
        ]);

        AktivitasLog::catat('unvalidasi', 'potensi_lahan', [
            'record_id'   => $id,
            'label_modul' => 'Lahan #' . $id,
            'keterangan'  => 'Batalkan validasi potensi lahan #' . $id,
        ]);

        return redirect()->back()->with('success', 'Validasi lahan berhasil dibatalkan.');
    }

    public function destroy(Request $request, $id)
    {
        $old = DB::table('lahan')->where('id_lahan', $id)->first();
        DB::transaction(function () use ($id, $old) {
            $tanamIds = DB::table('tanam')->where('id_lahan', $id)->pluck('id_tanam');
            if ($tanamIds->isNotEmpty()) {
                $panenIds = DB::table('panen')->whereIn('id_tanam', $tanamIds)->pluck('id_panen')->toArray();
                if (!empty($panenIds)) {
                    DB::table('distribusi')->whereIn('id_panen', $panenIds)->delete();
                }
                DB::table('panen')->whereIn('id_tanam', $tanamIds)->delete();
                DB::table('tanam')->where('id_lahan', $id)->delete();
            }
            DB::table('lahan')->where('id_lahan', $id)->delete();
            
            // Hapus poktan jika sudah tidak ada lahan yang menggunakannya (yatim)
            if ($old && $old->id_poktan) {
                $stillUsed = DB::table('lahan')->where('id_poktan', $old->id_poktan)->where('deletestatus', '!=', '0')->exists();
                if (!$stillUsed) {
                    DB::table('poktan')->where('id_poktan', $old->id_poktan)->delete();
                }
            }
        });

        AktivitasLog::catat('delete', 'potensi_lahan', [
            'record_id'   => $id,
            'label_modul' => 'Lahan #' . $id . ($old ? ' - ' . ($old->alamat_lahan ?? '') : ''),
            'data_lama'   => $old ? (array)$old : null,
            'keterangan'  => 'Hapus potensi lahan #' . $id . ($old ? ', luas ' . $old->luas_lahan . ' Ha' : ''),
        ]);

        return redirect()->back()->with('success', 'Data lahan berhasil dihapus.');
    }
}
