<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| READ: Menampilkan, mencari, dan memfilter data mahasiswa
|--------------------------------------------------------------------------
*/
Route::get('/mahasiswa', function (Request $request) {
    try {
        $pdo = DB::connection()->getPdo();

        $kataKunci = trim($request->query('q', ''));
        $programStudiId = $request->query('program_studi_id', '');

        $sql = 'SELECT m.id, m.nim, m.nama, m.email, m.usia,
                       m.program_studi_id, p.nama_prodi
                FROM mahasiswa AS m
                JOIN program_studi AS p
                    ON p.id = m.program_studi_id
                WHERE 1 = 1';

        $parameter = [];

        // Pencarian berdasarkan NIM, nama, atau email.
        if ($kataKunci !== '') {
            $sql .= ' AND (
                m.nim LIKE :nim
                OR m.nama LIKE :nama
                OR m.email LIKE :email
            )';

            $kataCari = '%' . $kataKunci . '%';
            $parameter['nim'] = $kataCari;
            $parameter['nama'] = $kataCari;
            $parameter['email'] = $kataCari;
        }

        // Filter berdasarkan program studi.
        if ($programStudiId !== '') {
            $sql .= ' AND m.program_studi_id = :program_studi_id';
            $parameter['program_studi_id'] = $programStudiId;
        }

        $sql .= ' ORDER BY m.nim';

        $pernyataanMahasiswa = $pdo->prepare($sql);
        $pernyataanMahasiswa->execute($parameter);
        $daftarMahasiswa = $pernyataanMahasiswa->fetchAll(\PDO::FETCH_ASSOC);

        $pernyataanProdi = $pdo->prepare(
            'SELECT id, nama_prodi
             FROM program_studi
             ORDER BY nama_prodi'
        );
        $pernyataanProdi->execute();
        $daftarProgramStudi = $pernyataanProdi->fetchAll(\PDO::FETCH_ASSOC);

        return view('mahasiswa', compact(
            'daftarMahasiswa',
            'daftarProgramStudi',
            'kataKunci',
            'programStudiId'
        ));
    } catch (\Throwable $error) {
        report($error);

        return response(
            'Data tidak dapat dibaca. Periksa koneksi database dan query.',
            500
        );
    }
})->name('mahasiswa.index');


/*
|--------------------------------------------------------------------------
| CREATE: Memvalidasi dan menyimpan data mahasiswa
|--------------------------------------------------------------------------
*/
Route::post('/mahasiswa', function (Request $request) {
    $data = $request->validate([
        'nim' => ['required', 'string', 'size:10'],
        'nama' => ['required', 'string', 'max:100'],
        'email' => ['required', 'email', 'max:100'],
        'usia' => ['required', 'integer', 'min:15', 'max:100'],
        'program_studi_id' => ['required', 'integer'],
    ], [
        'nim.required' => 'NIM wajib diisi.',
        'nim.size' => 'NIM harus tepat 10 karakter.',
        'nama.required' => 'Nama wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'usia.required' => 'Usia wajib diisi.',
        'usia.integer' => 'Usia harus berupa angka.',
        'usia.min' => 'Usia minimal 15 tahun.',
        'usia.max' => 'Usia maksimal 100 tahun.',
        'program_studi_id.required' => 'Program studi wajib dipilih.',
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        // Memastikan program studi tersedia.
        $cekProdi = $pdo->prepare(
            'SELECT COUNT(*) FROM program_studi WHERE id = :id'
        );
        $cekProdi->execute(['id' => $data['program_studi_id']]);

        if ((int) $cekProdi->fetchColumn() === 0) {
            return back()->withInput()->with(
                'gagal',
                'Program studi yang dipilih tidak tersedia.'
            );
        }

        // Memeriksa NIM atau email duplikat.
        $cekDuplikat = $pdo->prepare(
            'SELECT COUNT(*) FROM mahasiswa
             WHERE nim = :nim OR email = :email'
        );
        $cekDuplikat->execute([
            'nim' => $data['nim'],
            'email' => $data['email'],
        ]);

        if ((int) $cekDuplikat->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'NIM atau email sudah digunakan.'
            );
        }

        // Menyimpan data mahasiswa.
        $simpan = $pdo->prepare(
            'INSERT INTO mahasiswa
                (nim, nama, email, usia, program_studi_id)
             VALUES
                (:nim, :nama, :email, :usia, :program_studi_id)'
        );
        $simpan->execute([
            'nim' => $data['nim'],
            'nama' => $data['nama'],
            'email' => $data['email'],
            'usia' => $data['usia'],
            'program_studi_id' => $data['program_studi_id'],
        ]);

        return redirect()->route('mahasiswa.index')->with(
            'berhasil',
            'Data mahasiswa berhasil ditambahkan.'
        );
    } catch (\Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Data gagal disimpan. Periksa koneksi dan data.'
        );
    }
})->name('mahasiswa.store');


/*
|--------------------------------------------------------------------------
| EDIT: Menampilkan form untuk mengubah data mahasiswa
|--------------------------------------------------------------------------
*/
Route::get('/mahasiswa/{id}/edit', function (int $id) {
    try {
        $pdo = DB::connection()->getPdo();

        $ambilMahasiswa = $pdo->prepare(
            'SELECT id, nim, nama, email, usia, program_studi_id
             FROM mahasiswa
             WHERE id = :id'
        );
        $ambilMahasiswa->execute(['id' => $id]);
        $mahasiswa = $ambilMahasiswa->fetch(\PDO::FETCH_ASSOC);

        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.index')->with(
                'gagal',
                'Data mahasiswa tidak ditemukan.'
            );
        }

        $ambilProdi = $pdo->prepare(
            'SELECT id, nama_prodi
             FROM program_studi
             ORDER BY nama_prodi'
        );
        $ambilProdi->execute();
        $daftarProgramStudi = $ambilProdi->fetchAll(\PDO::FETCH_ASSOC);

        return view('mahasiswa-edit', compact(
            'mahasiswa',
            'daftarProgramStudi'
        ));
    } catch (\Throwable $error) {
        report($error);

        return redirect()->route('mahasiswa.index')->with(
            'gagal',
            'Data gagal dibuka untuk diubah.'
        );
    }
})->whereNumber('id')->name('mahasiswa.edit');


/*
|--------------------------------------------------------------------------
| UPDATE: Menyimpan perubahan data mahasiswa
|--------------------------------------------------------------------------
*/
Route::put('/mahasiswa/{id}', function (Request $request, int $id) {
    $data = $request->validate([
        'nim' => ['required', 'string', 'size:10'],
        'nama' => ['required', 'string', 'max:100'],
        'email' => ['required', 'email', 'max:100'],
        'usia' => ['required', 'integer', 'min:15', 'max:100'],
        'program_studi_id' => ['required', 'integer'],
    ], [
        'nim.required' => 'NIM wajib diisi.',
        'nim.size' => 'NIM harus tepat 10 karakter.',
        'nama.required' => 'Nama wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'usia.required' => 'Usia wajib diisi.',
        'usia.integer' => 'Usia harus berupa angka.',
        'usia.min' => 'Usia minimal 15 tahun.',
        'usia.max' => 'Usia maksimal 100 tahun.',
        'program_studi_id.required' => 'Program studi wajib dipilih.',
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        // Memastikan mahasiswa yang akan diubah tersedia.
        $cekMahasiswa = $pdo->prepare(
            'SELECT COUNT(*) FROM mahasiswa WHERE id = :id'
        );
        $cekMahasiswa->execute(['id' => $id]);

        if ((int) $cekMahasiswa->fetchColumn() === 0) {
            return redirect()->route('mahasiswa.index')->with(
                'gagal',
                'Data mahasiswa tidak ditemukan.'
            );
        }

        // Memastikan program studi tersedia.
        $cekProdi = $pdo->prepare(
            'SELECT COUNT(*) FROM program_studi WHERE id = :program_studi_id'
        );
        $cekProdi->execute([
            'program_studi_id' => $data['program_studi_id'],
        ]);

        if ((int) $cekProdi->fetchColumn() === 0) {
            return back()->withInput()->with(
                'gagal',
                'Program studi yang dipilih tidak tersedia.'
            );
        }

        // Cek NIM/email duplikat milik mahasiswa lain.
        $cekDuplikat = $pdo->prepare(
            'SELECT COUNT(*)
             FROM mahasiswa
             WHERE (nim = :nim OR email = :email)
               AND id <> :id'
        );
        $cekDuplikat->execute([
            'nim' => $data['nim'],
            'email' => $data['email'],
            'id' => $id,
        ]);

        if ((int) $cekDuplikat->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'NIM atau email sudah digunakan mahasiswa lain.'
            );
        }

        $ubah = $pdo->prepare(
            'UPDATE mahasiswa
             SET nim = :nim,
                 nama = :nama,
                 email = :email,
                 usia = :usia,
                 program_studi_id = :program_studi_id
             WHERE id = :id'
        );
        $ubah->execute([
            'nim' => $data['nim'],
            'nama' => $data['nama'],
            'email' => $data['email'],
            'usia' => $data['usia'],
            'program_studi_id' => $data['program_studi_id'],
            'id' => $id,
        ]);

        return redirect()->route('mahasiswa.index')->with(
            'berhasil',
            'Data mahasiswa berhasil diperbarui.'
        );
    } catch (\Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Data gagal diperbarui. Periksa koneksi dan data.'
        );
    }
})->whereNumber('id')->name('mahasiswa.update');


/*
|--------------------------------------------------------------------------
| DELETE: Menghapus data mahasiswa
|--------------------------------------------------------------------------
*/
Route::delete('/mahasiswa/{id}', function (int $id) {
    try {
        $pdo = DB::connection()->getPdo();

        $hapus = $pdo->prepare(
            'DELETE FROM mahasiswa WHERE id = :id'
        );
        $hapus->execute(['id' => $id]);

        if ($hapus->rowCount() === 0) {
            return redirect()->route('mahasiswa.index')->with(
                'gagal',
                'Data mahasiswa tidak ditemukan.'
            );
        }

        return redirect()->route('mahasiswa.index')->with(
            'berhasil',
            'Data mahasiswa berhasil dihapus.'
        );
    } catch (\Throwable $error) {
        report($error);

        return redirect()->route('mahasiswa.index')->with(
            'gagal',
            'Data gagal dihapus. Periksa relasi atau koneksi database.'
        );
    }
})->whereNumber('id')->name('mahasiswa.destroy');
