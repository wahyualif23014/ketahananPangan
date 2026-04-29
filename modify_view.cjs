const fs = require('fs');

const path = 'resources/views/view/kelola-lahan/potensi/index.blade.php';
let f = fs.readFileSync(path, 'utf8');

// 1. Ubah route('admin.*') ke route('view.*')
f = f.replace(/route\('admin\./g, "route('view.");
f = f.replace(/href="\/admin\//g, 'href="/view/');
f = f.replace(/action="\/admin\//g, 'action="/view/');

// 2. Hapus tombol Tambah
f = f.replace(/<button @click="openModal\(\)"[^>]*>[\s\S]*?Tambah\s*<\/button>/g, '');

// 3. Hapus form validasi
f = f.replace(/@if\(!\$item\['valid_oleh'\]\)\s*<form action="\/view\/kelola-lahan\/potensi\/validasi\/\{\{ \$item\['id_lahan'\] \}\}" method="POST" class="inline m-0">[\s\S]*?<\/form>\s*@endif/g, '');

// 4. Hapus tombol Edit
f = f.replace(/<button data-item="\{\{ json_encode\(\$item\) \}\}" onclick='openEditModal\(JSON\.parse\(this\.dataset\.item\)\)'[\s\S]*?Edit\s*<\/button>/g, '');

// 5. Hapus form Hapus
f = f.replace(/<form action="\/view\/kelola-lahan\/potensi\/destroy\/\{\{ \$item\['id_lahan'\] \}\}" method="POST" class="inline m-0" onsubmit="return confirm\('Yakin hapus data lahan ini\?'\)">[\s\S]*?<\/form>/g, '');

// 6. Potong file pada bagian Edit Modal (baris 751+)
const editModalIndex = f.indexOf('<!-- 2. EDIT MODAL -->');
if (editModalIndex !== -1) {
    f = f.substring(0, editModalIndex);
    f += '</div>\n@endsection\n';
}

fs.writeFileSync(path, f);
console.log('Done modifying the file');
