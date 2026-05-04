<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AktivitasLog extends Model
{
    protected $table = 'aktivitas_log';

    protected $fillable = [
        'user_id', 'username', 'nama_user', 'role',
        'aksi', 'modul', 'label_modul', 'record_id',
        'data_lama', 'data_baru', 'keterangan',
        'ip_address', 'bulan', 'tahun',
    ];

    /**
     * Record a new activity log entry.
     * Self-protecting: failures are silently logged, never propagated.
     *
     * @param string $aksi        create | update | delete | validasi | unvalidasi | verify
     * @param string $modul       e.g. tanam | panen | serapan | potensi_lahan | komoditi | jabatan
     * @param array  $opts        Optional: record_id, label_modul, data_lama, data_baru, keterangan
     */
    public static function catat(string $aksi, string $modul, array $opts = []): void
    {
        try {
            $user = Auth::user();
            $now  = now();

            static::create([
                'user_id'      => $user?->id,
                'username'     => $user?->username,
                // Support both nama_anggota and nama_lengkap field names
                'nama_user'    => $user?->nama_anggota ?? $user?->nama_lengkap ?? $user?->name,
                'role'         => $user?->role,
                'aksi'         => $aksi,
                'modul'        => $modul,
                'label_modul'  => $opts['label_modul'] ?? null,
                'record_id'    => $opts['record_id'] ?? null,
                'data_lama'    => isset($opts['data_lama']) ? json_encode($opts['data_lama'], JSON_UNESCAPED_UNICODE) : null,
                'data_baru'    => isset($opts['data_baru']) ? json_encode($opts['data_baru'], JSON_UNESCAPED_UNICODE) : null,
                'keterangan'   => $opts['keterangan'] ?? null,
                'ip_address'   => request()->ip(),
                'bulan'        => (int) $now->format('n'),
                'tahun'        => (int) $now->format('Y'),
            ]);
        } catch (\Exception $e) {
            // Jangan sampai log menghentikan operasi utama
            Log::error('[AktivitasLog] Gagal mencatat aktivitas', [
                'aksi'   => $aksi,
                'modul'  => $modul,
                'error'  => $e->getMessage(),
                'trace'  => $e->getTraceAsString(),
            ]);
        }
    }
}
