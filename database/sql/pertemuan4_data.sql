
USE praktikum_web_2401020106;

-- Memasukkan dua program studi
INSERT INTO program_studi (nama_prodi) VALUES
    ('Teknik Informatika'),
    ('Sistem Informasi');

-- Memasukkan empat mahasiswa
INSERT INTO mahasiswa
    (nim, nama, email, usia, program_studi_id)
VALUES
    ('2401020101', 'Rina Amelia', 'rina.amelia@example.com', 19, 1),
    ('2401020102', 'Dimas Prakoso', 'dimas.prakoso@example.com', 20, 1),
    ('2401020103', 'Nadia Putri', 'nadia.putri@example.com', 21, 2),
    ('2401020199', 'Mahasiswa Sementara', 'sementara99@example.com', 22, 2);

-- Memperbarui email Rina
UPDATE mahasiswa
SET email = 'rina.amelia.updated@example.com'
WHERE nim = '2401020101';

-- Menghapus satu data sementara
DELETE FROM mahasiswa
WHERE nim = '2401020199';

-- Menampilkan tiga mahasiswa beserta program studinya
SELECT
    m.nim,
    m.nama,
    m.email,
    m.usia,
    p.nama_prodi
FROM mahasiswa AS m
JOIN program_studi AS p
    ON p.id = m.program_studi_id
ORDER BY m.nim;
