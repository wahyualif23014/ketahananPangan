import re

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # PHP row
    content = content.replace("str_starts_with($row->keterangan_tanam ?? '', '[DITOLAK]')", "$row->tanam_alasan_tolak")
    content = content.replace("str_starts_with($row->ket_panen ?? '', '[DITOLAK]')", "$row->panen_alasan_tolak")
    content = content.replace("str_starts_with($row->keterangan_distribusi ?? '', '[DITOLAK]')", "$row->serapan_alasan_tolak")

    content = content.replace("str_replace('[DITOLAK] Alasan: ', '', explode(\"\\n\", $row->keterangan_tanam)[0])", "$row->tanam_alasan_tolak")
    content = content.replace("str_replace('[DITOLAK] Alasan: ', '', explode(\"\\n\", $row->ket_panen)[0])", "$row->panen_alasan_tolak")
    content = content.replace("str_replace('[DITOLAK] Alasan: ', '', explode(\"\\n\", $row->keterangan_distribusi)[0])", "$row->serapan_alasan_tolak")

    # PHP object
    content = content.replace("str_starts_with($tanam->keterangan_tanam ?? '', '[DITOLAK]')", "$tanam->alasan_tolak")
    content = content.replace("str_starts_with($panen->ket_panen ?? '', '[DITOLAK]')", "$panen->alasan_tolak")
    content = content.replace("str_starts_with($distribusi->keterangan_distribusi ?? '', '[DITOLAK]')", "$distribusi->alasan_tolak")

    content = content.replace("str_replace('[DITOLAK] Alasan: ', '', explode(\"\\n\", $tanam->keterangan_tanam)[0])", "$tanam->alasan_tolak")
    content = content.replace("str_replace('[DITOLAK] Alasan: ', '', explode(\"\\n\", $panen->ket_panen)[0])", "$panen->alasan_tolak")
    content = content.replace("str_replace('[DITOLAK] Alasan: ', '', explode(\"\\n\", $distribusi->keterangan_distribusi)[0])", "$distribusi->alasan_tolak")

    # Alpine
    content = content.replace("t.keterangan_tanam && t.keterangan_tanam.startsWith('[DITOLAK]')", "t.alasan_tolak")
    content = content.replace("!t.keterangan_tanam || !t.keterangan_tanam.startsWith('[DITOLAK]')", "!t.alasan_tolak")

    content = content.replace("p.ket_panen && p.ket_panen.startsWith('[DITOLAK]')", "p.alasan_tolak")
    content = content.replace("!p.ket_panen || !p.ket_panen.startsWith('[DITOLAK]')", "!p.alasan_tolak")

    content = content.replace("s.keterangan_distribusi && s.keterangan_distribusi.startsWith('[DITOLAK]')", "s.alasan_tolak")
    content = content.replace("!s.keterangan_distribusi || !s.keterangan_distribusi.startsWith('[DITOLAK]')", "!s.alasan_tolak")

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

process_file('resources/views/admin/kelola-lahan/lahan/index.blade.php')
process_file('resources/views/operator/kelola-lahan/operator_kelola/operator_kelola_index.blade.php')
