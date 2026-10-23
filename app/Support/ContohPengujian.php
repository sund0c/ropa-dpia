<?php

namespace App\Support;

/**
 * Contoh hasil pengujian (fiktif) untuk tombol "Isi dengan contoh" pada tab A01–A10.
 * Skenario: aplikasi perizinan fiktif, diuji di staging dengan akun uji. Hanya ringkasan hasil/verdict.
 */
class ContohPengujian
{
    private const DATA = [
        'a01' => [
            1 => ['hasil' => 'aman', 'objek' => 'GET /admin/konfigurasi', 'peran' => 'Admin vs Pemohon', 'bukti' => 'Request fungsi admin diulang dengan sesi pemohon: ditolak 403, isi halaman penolakan.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            2 => ['hasil' => 'rentan', 'objek' => 'GET /api/v1/pengajuan/{id}', 'peran' => 'Pemohon A vs Pemohon B', 'bukti' => 'Objek milik akun lain terbuka saat diakses akun uji berbeda; ID berurutan.', 'risiko' => 'tinggi', 'rekomendasi' => 'Terapkan pengecekan kepemilikan objek di server pada setiap endpoint pengajuan.', 'alasan' => ''],
            3 => ['hasil' => 'aman', 'objek' => '/admin/* dan /api/v1/admin/*', 'peran' => 'Anonim', 'bukti' => 'Seluruh endpoint tanpa sesi: 401 atau diarahkan ke halaman login.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            4 => ['hasil' => 'aman', 'objek' => 'PUT /api/v1/profil', 'peran' => 'Pemohon', 'bukti' => 'Field peran tambahan pada body diabaikan server; peran tetap setelah login ulang.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            5 => ['hasil' => 'aman', 'objek' => '/backup, /config, /laporan/2026/01', 'peran' => 'Anonim, Pemohon', 'bukti' => 'Seluruh path: 404 atau diarahkan ke login.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            6 => ['hasil' => 'aman', 'objek' => 'GET /api/v1/profil', 'peran' => 'Pemohon', 'bukti' => 'Header origin uji tidak dipantulkan pada respons.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            7 => ['hasil' => 'tidak_berlaku', 'objek' => '', 'peran' => '', 'bukti' => '', 'risiko' => '', 'rekomendasi' => '', 'alasan' => 'Aplikasi tidak memiliki fitur yang mengambil URL dari input pengguna.'],
            8 => ['hasil' => 'tinjau', 'objek' => 'Policy & middleware', 'peran' => 'Wawancara developer', 'bukti' => 'Sebagian controller API belum memanggil pemeriksaan otorisasi; perlu tinjauan kode.', 'risiko' => '', 'rekomendasi' => 'Audit seluruh controller agar konsisten memeriksa otorisasi di server.', 'alasan' => ''],
        ],
        'a02' => [
            1 => ['hasil' => 'rentan', 'objek' => 'GET / (seluruh halaman)', 'peran' => 'Anonim', 'bukti' => 'Beberapa header keamanan respons belum disetel.', 'risiko' => 'rendah', 'rekomendasi' => 'Tambahkan header keamanan yang kurang di konfigurasi server web.', 'alasan' => ''],
            2 => ['hasil' => 'aman', 'objek' => 'GET /api/v1/pengajuan/xx', 'peran' => 'Pemohon', 'bukti' => 'Input tidak valid menghasilkan pesan error umum tanpa detail internal.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            3 => ['hasil' => 'aman', 'objek' => '/uploads/, /berkas/', 'peran' => 'Anonim', 'bukti' => 'Akses direktori tanpa berkas indeks: ditolak, tidak menampilkan daftar isi.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            4 => ['hasil' => 'aman', 'objek' => '/.env, /.git/config, /backup', 'peran' => 'Anonim', 'bukti' => 'Seluruh berkas sensitif: 404.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            5 => ['hasil' => 'aman', 'objek' => 'OPTIONS, PUT, DELETE pada /', 'peran' => 'Anonim', 'bukti' => 'Metode HTTP yang tidak diperlukan ditolak.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            6 => ['hasil' => 'tidak_diuji', 'objek' => '', 'peran' => '', 'bukti' => '', 'risiko' => '', 'rekomendasi' => '', 'alasan' => 'Perlu koordinasi dengan pengelola; dijadwalkan pada pengujian lanjutan.'],
            7 => ['hasil' => 'aman', 'objek' => 'Halaman error aplikasi', 'peran' => 'Anonim', 'bukti' => 'Mode debug tidak aktif; error menampilkan halaman umum.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            8 => ['hasil' => 'tinjau', 'objek' => 'Konfigurasi server', 'peran' => 'Wawancara developer', 'bukti' => 'Hardening dasar sudah diterapkan; sebagian baseline perlu dikonfirmasi.', 'risiko' => '', 'rekomendasi' => 'Lengkapi dan dokumentasikan baseline konfigurasi aman server.', 'alasan' => ''],
        ],
        'a03' => [
            1 => ['hasil' => 'tinjau', 'objek' => 'Komponen front-end terlihat', 'peran' => 'Anonim', 'bukti' => 'Versi beberapa pustaka klien terlihat di sumber halaman; perlu dicek terhadap daftar kerentanan.', 'risiko' => '', 'rekomendasi' => 'Perbarui pustaka ke versi terbaru yang didukung.', 'alasan' => ''],
            2 => ['hasil' => 'tinjau', 'objek' => 'Inventaris dependensi', 'peran' => 'Wawancara developer', 'bukti' => 'Daftar dependensi ada, pembaruan belum terjadwal rutin.', 'risiko' => '', 'rekomendasi' => 'Jadwalkan pemeriksaan dependensi berkala dan pemutakhiran.', 'alasan' => ''],
            3 => ['hasil' => 'tidak_diuji', 'objek' => '', 'peran' => '', 'bukti' => '', 'risiko' => '', 'rekomendasi' => '', 'alasan' => 'Memerlukan akses ke pipeline CI/CD; di luar cakupan pengujian ini.'],
            4 => ['hasil' => 'tinjau', 'objek' => 'Sumber paket', 'peran' => 'Wawancara developer', 'bukti' => 'Paket diambil dari registri resmi; penguncian versi belum konsisten.', 'risiko' => '', 'rekomendasi' => 'Kunci versi dependensi dan gunakan berkas lock.', 'alasan' => ''],
            5 => ['hasil' => 'tidak_diuji', 'objek' => '', 'peran' => '', 'bukti' => '', 'risiko' => '', 'rekomendasi' => '', 'alasan' => 'Memerlukan akses rilis internal; di luar cakupan.'],
        ],
        'a04' => [
            1 => ['hasil' => 'aman', 'objek' => 'TLS pada domain uji', 'peran' => 'Anonim', 'bukti' => 'Konfigurasi TLS memakai protokol dan cipher yang masih didukung.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            2 => ['hasil' => 'aman', 'objek' => 'Seluruh alur', 'peran' => 'Anonim', 'bukti' => 'Seluruh trafik melalui HTTPS; tidak ada submit melalui kanal tidak aman.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            3 => ['hasil' => 'aman', 'objek' => 'Respons API & URL', 'peran' => 'Pemohon', 'bukti' => 'Data sensitif tidak muncul di URL maupun respons yang tidak perlu.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            4 => ['hasil' => 'tinjau', 'objek' => 'Enkripsi data tersimpan', 'peran' => 'Wawancara developer', 'bukti' => 'Sebagian kolom sensitif belum dikonfirmasi terenkripsi di basis data.', 'risiko' => '', 'rekomendasi' => 'Pastikan data sensitif terenkripsi saat disimpan.', 'alasan' => ''],
            5 => ['hasil' => 'tinjau', 'objek' => 'Penyimpanan kata sandi', 'peran' => 'Wawancara developer', 'bukti' => 'Kata sandi di-hash; algoritma perlu dikonfirmasi sesuai praktik terbaru.', 'risiko' => '', 'rekomendasi' => 'Gunakan algoritma hash kata sandi yang kuat dan ber-salt.', 'alasan' => ''],
            6 => ['hasil' => 'tidak_diuji', 'objek' => '', 'peran' => '', 'bukti' => '', 'risiko' => '', 'rekomendasi' => '', 'alasan' => 'Manajemen kunci di luar jangkauan pengujian dari luar.'],
        ],
        'a05' => [
            1 => ['hasil' => 'aman', 'objek' => 'Form pencarian & filter', 'peran' => 'Pemohon', 'bukti' => 'Input uji pada parameter tidak mengubah hasil kueri; memakai parameterisasi.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            2 => ['hasil' => 'aman', 'objek' => 'Parameter yang dipantulkan', 'peran' => 'Pemohon', 'bukti' => 'Input uji tampil sebagai teks biasa, bukan dieksekusi di peramban.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            3 => ['hasil' => 'aman', 'objek' => 'Kolom komentar/catatan', 'peran' => 'Pemohon', 'bukti' => 'Masukan uji tersimpan ditampilkan sebagai teks, sudah di-escape.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            4 => ['hasil' => 'tidak_berlaku', 'objek' => '', 'peran' => '', 'bukti' => '', 'risiko' => '', 'rekomendasi' => '', 'alasan' => 'Tidak ada fitur yang menjalankan perintah sistem atau template dari input.'],
            5 => ['hasil' => 'aman', 'objek' => 'Parameter nama berkas', 'peran' => 'Pemohon', 'bukti' => 'Permintaan berkas di luar direktori yang diizinkan ditolak.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            6 => ['hasil' => 'tinjau', 'objek' => 'Lapisan validasi', 'peran' => 'Wawancara developer', 'bukti' => 'Validasi sisi server ada; konsistensi parameterisasi perlu ditinjau.', 'risiko' => '', 'rekomendasi' => 'Pastikan seluruh kueri memakai parameter terikat.', 'alasan' => ''],
        ],
        'a06' => [
            1 => ['hasil' => 'tinjau', 'objek' => 'Dokumen desain', 'peran' => 'Wawancara developer', 'bukti' => 'Pemodelan ancaman belum terdokumentasi.', 'risiko' => '', 'rekomendasi' => 'Lakukan pemodelan ancaman pada alur bisnis utama.', 'alasan' => ''],
            2 => ['hasil' => 'aman', 'objek' => 'Alur persetujuan izin', 'peran' => 'Pemohon, Verifikator', 'bukti' => 'Langkah alur tidak bisa dilewati; status diverifikasi di server.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            3 => ['hasil' => 'tinjau', 'objek' => 'Fungsi berbiaya tinggi', 'peran' => 'Pemohon', 'bukti' => 'Pembatasan laju pada fungsi tertentu belum konsisten.', 'risiko' => '', 'rekomendasi' => 'Terapkan pembatasan laju pada fungsi mahal.', 'alasan' => ''],
            4 => ['hasil' => 'tidak_berlaku', 'objek' => '', 'peran' => '', 'bukti' => '', 'risiko' => '', 'rekomendasi' => '', 'alasan' => 'Aplikasi satu instansi, tidak multi-tenant.'],
            5 => ['hasil' => 'tinjau', 'objek' => 'Kebijakan akses default', 'peran' => 'Wawancara developer', 'bukti' => 'Prinsip tolak-secara-default perlu dikonfirmasi di kode.', 'risiko' => '', 'rekomendasi' => 'Pastikan akses default adalah menolak.', 'alasan' => ''],
        ],
        'a07' => [
            1 => ['hasil' => 'aman', 'objek' => 'Cookie sesi', 'peran' => 'Pemohon', 'bukti' => 'Atribut keamanan cookie sesi sudah disetel.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            2 => ['hasil' => 'aman', 'objek' => 'Alur login', 'peran' => 'Pemohon', 'bukti' => 'ID sesi berganti setelah berhasil login.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            3 => ['hasil' => 'aman', 'objek' => 'Login & reset password', 'peran' => 'Anonim', 'bukti' => 'Pesan seragam; tidak membedakan akun ada atau tidak.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            4 => ['hasil' => 'rentan', 'objek' => 'POST /login', 'peran' => 'Anonim (akun uji)', 'bukti' => 'Percobaan login gagal berkali-kali tanpa pembatasan atau penguncian.', 'risiko' => 'sedang', 'rekomendasi' => 'Terapkan pembatasan laju dan penguncian sementara pada login.', 'alasan' => ''],
            5 => ['hasil' => 'aman', 'objek' => 'Form registrasi', 'peran' => 'Anonim', 'bukti' => 'Kebijakan kata sandi menolak kata sandi lemah.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            6 => ['hasil' => 'tinjau', 'objek' => 'Autentikasi faktor kedua', 'peran' => 'Amati alur', 'bukti' => 'Faktor kedua tersedia namun belum diwajibkan untuk admin.', 'risiko' => '', 'rekomendasi' => 'Wajibkan faktor kedua untuk akun administrator.', 'alasan' => ''],
            7 => ['hasil' => 'aman', 'objek' => 'Sesi & logout', 'peran' => 'Pemohon', 'bukti' => 'Sesi berakhir setelah idle; logout membatalkan sesi.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            8 => ['hasil' => 'aman', 'objek' => 'Alur reset password', 'peran' => 'Pemohon', 'bukti' => 'Token reset sekali pakai dan kedaluwarsa.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
        ],
        'a08' => [
            1 => ['hasil' => 'tinjau', 'objek' => 'Skrip pihak ketiga', 'peran' => 'Anonim', 'bukti' => 'Sebagian skrip eksternal belum memakai atribut integritas.', 'risiko' => '', 'rekomendasi' => 'Tambahkan atribut integritas pada sumber daya eksternal.', 'alasan' => ''],
            2 => ['hasil' => 'tidak_berlaku', 'objek' => '', 'peran' => '', 'bukti' => '', 'risiko' => '', 'rekomendasi' => '', 'alasan' => 'Tidak ditemukan indikasi deserialisasi objek dari input pengguna.'],
            3 => ['hasil' => 'tidak_diuji', 'objek' => '', 'peran' => '', 'bukti' => '', 'risiko' => '', 'rekomendasi' => '', 'alasan' => 'Mekanisme pembaruan di luar cakupan.'],
            4 => ['hasil' => 'tidak_diuji', 'objek' => '', 'peran' => '', 'bukti' => '', 'risiko' => '', 'rekomendasi' => '', 'alasan' => 'Integritas pipeline memerlukan akses internal; di luar cakupan.'],
            5 => ['hasil' => 'tidak_berlaku', 'objek' => '', 'peran' => '', 'bukti' => '', 'risiko' => '', 'rekomendasi' => '', 'alasan' => 'Aplikasi tidak memiliki mekanisme pembaruan otomatis.'],
        ],
        'a09' => [
            1 => ['hasil' => 'tinjau', 'objek' => 'Pencatatan peristiwa', 'peran' => 'Wawancara developer', 'bukti' => 'Peristiwa keamanan utama dicatat; cakupan perlu dikonfirmasi.', 'risiko' => '', 'rekomendasi' => 'Pastikan peristiwa autentikasi dan akses tercatat.', 'alasan' => ''],
            2 => ['hasil' => 'tinjau', 'objek' => 'Isi log', 'peran' => 'Wawancara developer', 'bukti' => 'Perlu pengecekan agar data sensitif tidak masuk ke log.', 'risiko' => '', 'rekomendasi' => 'Saring data sensitif sebelum ditulis ke log.', 'alasan' => ''],
            3 => ['hasil' => 'tinjau', 'objek' => 'Perlindungan log', 'peran' => 'Wawancara developer', 'bukti' => 'Akses ke berkas log perlu dibatasi.', 'risiko' => '', 'rekomendasi' => 'Batasi akses dan simpan log secara terpisah.', 'alasan' => ''],
            4 => ['hasil' => 'tinjau', 'objek' => 'Alerting', 'peran' => 'Tinjau monitoring', 'bukti' => 'Notifikasi belum aktif untuk pola mencurigakan.', 'risiko' => '', 'rekomendasi' => 'Aktifkan notifikasi untuk pola login gagal beruntun.', 'alasan' => ''],
            5 => ['hasil' => 'tinjau', 'objek' => 'Integrasi SOC', 'peran' => 'Verifikasi SOC', 'bukti' => 'Log aplikasi belum diteruskan ke SIEM.', 'risiko' => '', 'rekomendasi' => 'Integrasikan log ke SIEM/SOC.', 'alasan' => ''],
        ],
        'a10' => [
            1 => ['hasil' => 'aman', 'objek' => 'Halaman error', 'peran' => 'Anonim', 'bukti' => 'Error menampilkan pesan umum tanpa jejak internal.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            2 => ['hasil' => 'tinjau', 'objek' => 'Jalur penanganan error', 'peran' => 'Wawancara developer', 'bukti' => 'Perlu dipastikan default saat gagal adalah menolak.', 'risiko' => '', 'rekomendasi' => 'Pastikan kegagalan berujung pada menolak akses.', 'alasan' => ''],
            3 => ['hasil' => 'tinjau', 'objek' => 'Konsistensi exception', 'peran' => 'Wawancara developer', 'bukti' => 'Penanganan exception belum seragam di semua modul.', 'risiko' => '', 'rekomendasi' => 'Seragamkan penanganan exception.', 'alasan' => ''],
            4 => ['hasil' => 'aman', 'objek' => 'Input besar/lambat (terbatas)', 'peran' => 'Pemohon', 'bukti' => 'Beban uji terbatas ditangani wajar tanpa gangguan.', 'risiko' => '', 'rekomendasi' => '', 'alasan' => ''],
            5 => ['hasil' => 'tinjau', 'objek' => 'Bypass via error', 'peran' => 'Wawancara developer', 'bukti' => 'Perlu dipastikan error tidak melewati pemeriksaan keamanan.', 'risiko' => '', 'rekomendasi' => 'Verifikasi kontrol tetap berlaku saat terjadi error.', 'alasan' => ''],
        ],
    ];

    /** Contoh satu kategori, dikunci "<KODE>-<no>" agar cocok dengan data-bt-kode di view. */
    public static function untuk(string $tab): array
    {
        $data = self::DATA[$tab] ?? [];
        if ($data === []) return [];
        $kode = strtoupper($tab);
        $out = [];
        foreach ($data as $no => $isi) {
            $out["$kode-$no"] = $isi;
        }
        return $out;
    }
}
