<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

// Login sebagai USER (nip 3, role user)
$user = User::where('nip', '3')->firstOrFail();
Auth::login($userSource ?? $userPay, false);

$cases = [
    ['Pegawai list (bukan punya)', '/pegawai'],
    ['Profil pegawai lain', '/pegawai/1'],
    ['Edit pegawai lain', '/pegawai/1/edit'],
    ['Audit log', '/audit'],
    ['Manajemen Akun', '/admin/accounts'],
    ['Laporan', '/laporan'],
    ['Master data', '/master/units'],
];

foreach ($cases as [$label, $url]) {
    $resp = $app->handle(Request::create($url, 'GET'));
    $code = $resp->getStatusCode();
    $verdict = $code === 403 ? 'DITOLAK(403) OK' : ($code === 302 || $code === 404 ? 'redirect/404 (ok utk menu anti-role)' : "TERBUKA($code) PENTING");
    echo str_pad($label, 26) . " -> " . str_pad($code, 4) . " $verdict\n";
}
