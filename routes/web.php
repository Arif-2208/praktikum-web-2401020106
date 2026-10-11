
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

// PERTEMUAN 2: LATIHAN PHP
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

// PERTEMUAN 3: FORM MAHASISWA
Route::get('/form-mahasiswa', function () {
    return view('form-mahasiswa');
});

Route::post('/form-mahasiswa', function (Request $request) {
    // Sanitasi input sebelum validasi
    $dataBersih = [
        'nim' => trim((string) $request->input('nim')),
        'nama' => strip_tags(
            trim((string) $request->input('nama'))
        ),
        'email' => filter_var(
            (string) $request->input('email'),
            FILTER_SANITIZE_EMAIL
        ),
        'usia' => trim((string) $request->input('usia')),
    ];

    // Validasi data di server Laravel
    $validator = Validator::make($dataBersih, [
        'nim' => [
            'required',
            'regex:/^[0-9]{8,12}$/',
        ],
        'nama' => ['required', 'min:3', 'max:50'],
        'email' => ['required', 'email'],
        'usia' => ['required', 'integer', 'min:17', 'max:60'],
    ], [
        'nim.required' => 'NIM wajib diisi.',
        'nim.regex' => 'NIM harus terdiri dari 8–12 digit angka.',
        'nama.required' => 'Nama wajib diisi.',
        'nama.min' => 'Nama minimal 3 karakter.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'usia.required' => 'Usia wajib diisi.',
        'usia.integer' => 'Usia harus berupa angka.',
        'usia.min' => 'Usia minimal 17 tahun.',
        'usia.max' => 'Usia maksimal 60 tahun.',
    ]);

    if ($validator->fails()) {
        return redirect('/form-mahasiswa')
            ->withErrors($validator)
            ->withInput();
    }

    $data = $validator->validated();
    $data['usia'] = (int) $data['usia'];

    return view('hasil-form', ['data' => $data]);
});
