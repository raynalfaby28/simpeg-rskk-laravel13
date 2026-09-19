<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Route cinematically;

$tests = [
    // [description, url, expectedStatus(403=dilarang utk user)]
    ['user index',       '/pegawai',            403],
    ['user show other',  '/pegawai/1',          403],
    ['user audit',       '/audit',              403],
    ['user accounts',    '/admin/accounts',     403],
    ['user reports',     '/laporan',            403],
    ['user master',      '/master/units',       403],
    ['user settings',    '/pengaturan',         403],
];

function asUser(callable $fn) {
    $user = App\Models\User::where('nip','3')->first();
    $auth = app('auth');
    $auth->login($user);
    return $fn();
}

$results = [];
foreach ($tests as [$label, $url, $expect]) {
    $res = asUser(fn () => app(Illuminate\Contracts\Http\Kernel::class)->handle(
        Illuminate\Http\Request::create($url, 'GET')
    ));
    $code = $res->getStatusCode();
    $match = ($expect === 403) ? 'BLOCKED' : 'OPEN';
    $ok = ($code === $expect) || ($expect === 403 && in_array($code, [403], true));
    $results[] = sprintf("%-18s url=%-22s -> %d  %s", $label, $url, $code, $match);
    echo $results[count($results)-1] . "\n";
    $res->headers->set('X-Inertia'); // flush
    $res = null;
}
echo "\nDONE\n";
