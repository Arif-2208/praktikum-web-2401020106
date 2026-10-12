
<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/mahasiswa/{nim?}', function (?string $nim = null) {
    try {
        // Membuat koneksi PDO melalui konfigurasi Laravel
        $pdo = DB::connection()->getPdo();

        // Query untuk mengambil data mahasiswa dan program studi
        $sql = 'SELECT m.nim, m.nama, m.email, m.usia,
                       p.nama_prodi
                FROM mahasiswa AS m
                JOIN program_studi AS p
                    ON p.id = m.program_studi_id';

        // Jika NIM diberikan, tambahkan filter
        if ($nim !== null) {
            $sql .= ' WHERE m.nim = :nim';
        }

        // Mengurutkan data berdasarkan NIM
        $sql .= ' ORDER BY m.nim';

        // Menyiapkan query menggunakan prepared statement
        $statement = $pdo->prepare($sql);

        // Menjalankan query dan mengikat parameter NIM
        $statement->execute(
            $nim !== null ? ['nim' => $nim] : []
        );

        // Mengambil seluruh hasil dalam bentuk array asosiatif
        $daftarMahasiswa = $statement->fetchAll(
            \PDO::FETCH_ASSOC
        );

        // Mengirim data ke tampilan Blade
        return view(
            'mahasiswa',
            compact('daftarMahasiswa', 'nim')
        );

    } catch (\Throwable $error) {
        // Menyimpan rincian error di log Laravel
        report($error);

        // Menampilkan pesan aman kepada pengguna
        return response(
            'Koneksi atau query basis data gagal. Periksa file .env dan layanan MySQL.',
            500
        );
    }
});
