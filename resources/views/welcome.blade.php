<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pangan Presisi - Polda Jatim</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-slate-900 text-white flex items-center justify-center min-h-screen">
    <div class="text-center">
        <h1 class="text-5xl font-black text-emerald-400 mb-4">PANGAN PRESISI</h1>
        <p class="text-slate-400 mb-8 italic">Sistem Manajemen Ketahanan Pangan Terintegrasi</p>
        
        <div class="flex justify-center gap-4">
            <a href="{{ route('login') }}" class="bg-emerald-500 hover:bg-emerald-600 px-8 py-3 rounded-xl font-bold transition">
                Masuk Sistem
            </a>
            <a href="{{ route('register') }}" class="border border-slate-700 hover:bg-slate-800 px-8 py-3 rounded-xl font-bold transition">
                Daftar Personil
            </a>
        </div>
    </div>
</body>
</html>