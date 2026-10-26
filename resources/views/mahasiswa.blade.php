
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa</title>
</head>
<body>

    <h1>Data Mahasiswa</h1>

    {{-- Pesan berhasil --}}
    @if (session('berhasil'))
        <p style="color: green;">
            {{ session('berhasil') }}
        </p>
    @endif

    {{-- Pesan gagal --}}
    @if (session('gagal'))
        <p style="color: red;">
            {{ session('gagal') }}
        </p>
    @endif

    {{-- Pesan validasi --}}
    @if ($errors->any())
        <h3>Data belum valid</h3>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <hr>

    <h2>Tambah Mahasiswa</h2>

    <form action="{{ route('mahasiswa.store') }}" method="POST">
        @csrf

        <p>
            <label for="nim">NIM</label><br>
            <input
                type="text"
                id="nim"
                name="nim"
                value="{{ old('nim') }}"
                required
            >
        </p>

        <p>
            <label for="nama">Nama</label><br>
            <input
                type="text"
                id="nama"
                name="nama"
                value="{{ old('nama') }}"
                required
            >
        </p>

        <p>
            <label for="email">Email</label><br>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
            >
        </p>

        <p>
            <label for="usia">Usia</label><br>
            <input
                type="number"
                id="usia"
                name="usia"
                value="{{ old('usia') }}"
                min="15"
                max="100"
                required
            >
        </p>

        <p>
            <label for="program_studi_id">Program Studi</label><br>
            <select id="program_studi_id" name="program_studi_id" required>
                <option value="">-- Pilih Program Studi --</option>

                @foreach ($daftarProgramStudi as $programStudi)
                    <option
                        value="{{ $programStudi['id'] }}"
                        @selected(
                            (string) old('program_studi_id') ===
                            (string) $programStudi['id']
                        )
                    >
                        {{ $programStudi['nama_prodi'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Tambah Data</button>
    </form>

    <hr>

    <h2>Pencarian dan Filter</h2>

    <form action="{{ route('mahasiswa.index') }}" method="GET">
        <p>
            <label for="q">Cari Mahasiswa</label><br>
            <input
                type="text"
                id="q"
                name="q"
                placeholder="Masukkan NIM, nama, atau email"
                value="{{ $kataKunci ?? '' }}"
            >
        </p>

        <p>
            <label for="filter_prodi">Filter Program Studi</label><br>
            <select id="filter_prodi" name="program_studi_id">
                <option value="">-- Semua Program Studi --</option>

                @foreach ($daftarProgramStudi as $programStudi)
                    <option
                        value="{{ $programStudi['id'] }}"
                        @selected(
                            (string) ($programStudiId ?? '') ===
                            (string) $programStudi['id']
                        )
                    >
                        {{ $programStudi['nama_prodi'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Cari / Filter</button>
        <a href="{{ route('mahasiswa.index') }}">Reset</a>
    </form>

    <br>

    <h2>Daftar Mahasiswa</h2>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>No.</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Usia</th>
                <th>Program Studi</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($daftarMahasiswa as $mahasiswa)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $mahasiswa['nim'] }}</td>
                    <td>{{ $mahasiswa['nama'] }}</td>
                    <td>{{ $mahasiswa['email'] }}</td>
                    <td>{{ $mahasiswa['usia'] }}</td>
                    <td>{{ $mahasiswa['nama_prodi'] }}</td>
                    <td>
                        <a href="{{ route('mahasiswa.edit', $mahasiswa['id']) }}">
                            Ubah
                        </a>

                        <form
                            action="{{ route('mahasiswa.destroy', $mahasiswa['id']) }}"
                            method="POST"
                            style="display: inline;"
                            onsubmit="return confirm('Yakin ingin menghapus data mahasiswa ini?');"
                        >
                            @csrf
                            @method('DELETE')

                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Data mahasiswa tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
