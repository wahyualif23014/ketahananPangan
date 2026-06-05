<?php
error_reporting(E_ALL);
$user = null;
echo $user->nama_anggota ?? 'Safe';
echo "\n";
