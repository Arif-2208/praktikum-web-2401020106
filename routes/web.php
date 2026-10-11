
<?php

use Illuminate\Support\Facades\Route;

Route::get('/latihan-php', function () {
    $nama = 'Arif Himawan Aulady';
   $nilai = [60, 65, 70, 55, 50];

    $hitungRataRata = function (array $data): float {
        $total = 0;

        foreach ($data as $angka) {
            $total += $angka;
        }

        return $total / count($data);
    };

    $ratarata = $hitungRataRata($nilai);

    if ($ratarata >= 75) {
        $status = 'Lulus';
    } else {
        $status = 'Perlu Perbaikan';
    }

    return view('latihan-php', compact(
        'nama', 'nilai', 'ratarata', 'status'
    ));
});
