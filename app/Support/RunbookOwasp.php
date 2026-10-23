<?php

namespace App\Support;

/**
 * Pedoman uji manual OWASP Top 10:2025 — diambil dari "Runbook Pengujian Keamanan Manual"
 * Bidang Persandian Diskominfos Provinsi Bali (per 12 Oktober 2026).
 * Setiap butir: metode, tujuan, langkah, dan cara menilai (verdict).
 */
class RunbookOwasp
{
    public const KATEGORI = [
        'a01' => [
            'kode'  => 'A01',
            'nama'  => 'Broken Access Control',
            'desk'  => 'Memastikan pengguna hanya bisa mengakses data dan menjalankan aksi yang diizinkan perannya. Risiko nomor satu; SSRF kini masuk kategori ini.',
            'butir' => [
                1 => [
                    'judul'   => 'Vertical access control',
                    'metode'  => 'Uji aktif (replay lintas-sesi)',
                    'tujuan'  => 'Role rendah tak bisa menjangkau fungsi milik role tinggi.',
                    'langkah' => [
                        'Login sebagai role tinggi (Admin). Jelajahi fungsi sensitif (kelola pengguna, ekspor data, ubah konfigurasi).',
                        'Dengan DevTools/proxy menyala, catat request API di balik tiap fungsi (method + URL + header + body), bukan sekadar URL halaman. Simpan respons suksesnya sebagai patokan.',
                        'Logout. Login sebagai role rendah (Viewer) di profil browser terpisah; catat token/cookie sesinya.',
                        'Ambil request Admin, kirim ulang lewat proxy tapi ganti cookie/token ke sesi Viewer. Bila ada CSRF token, ambil token baru yang valid untuk Viewer, jangan pakai milik Admin.',
                        'Amati respons yang diterima Viewer.',
                    ],
                    'verdict' => [
                        'Ditolak (401/403, atau redirect ke login): kontrol bekerja. AMAN.',
                        'Berhasil dan responsnya mirip patokan Admin (data privileged muncul): KERENTANAN.',
                        '200 tapi isi berupa halaman penolakan/kosong: aman (soft-deny); nilai dari isi, bukan status.',
                        '419/CSRF error: bukan verdict; token salah. Ulangi dengan token Viewer yang benar.',
                    ],
                ],
                2 => [
                    'judul'   => 'Horizontal / IDOR',
                    'metode'  => 'Uji aktif (replay lintas-sesi)',
                    'tujuan'  => 'Pengguna tak bisa mengakses objek milik pengguna lain yang setara perannya.',
                    'langkah' => [
                        'Siapkan dua akun setara (User A dan User B).',
                        'Login sebagai User B, buka objek miliknya; catat request dan identifier objek di URL/body (mis. /dokumen/1002).',
                        'Login sebagai User A di sesi terpisah.',
                        'Ambil request User B, replay dengan sesi User A; identifier tetap milik B.',
                        'Coba variasi identifier (naik/turun angka, tebak pola bila berurutan); amati apakah A menerima data B.',
                    ],
                    'verdict' => [
                        'Ditolak / data bukan milik B / 403: AMAN.',
                        'User A menerima data User B: KERENTANAN IDOR.',
                        'Karena kamu login sebagai B dan tahu isi objeknya, kamu punya patokan pasti; bandingkan isi, bukan status.',
                        'Identifier berupa UUID acak membuat IDOR sulit; tandai \'perlu uji lanjut\' bila ID tak bisa ditebak.',
                    ],
                ],
                3 => [
                    'judul'   => 'Missing function-level authorization',
                    'metode'  => 'Uji aktif (request anonim)',
                    'tujuan'  => 'Endpoint privat benar-benar mengecek autentikasi, bukan hanya menyembunyikan menunya di UI.',
                    'langkah' => [
                        'Kumpulkan daftar endpoint privileged (dari uji vertical AC, atau dokumentasi API/OpenAPI bila ada).',
                        'Untuk tiap endpoint, kirim request tanpa sesi sama sekali (hapus cookie/Authorization) lewat proxy atau curl.',
                        'Uji variasi yang ditebak dari pola (mis. /admin/export, /api/users/all) meski tak muncul di menu.',
                        'Perhatikan endpoint yang hanya dilindungi di frontend (tombol hilang) tapi backend tetap merespons.',
                    ],
                    'verdict' => [
                        '401/403/redirect-login untuk request anonim: AMAN.',
                        'Endpoint berfungsi tanpa autentikasi: KERENTANAN (otorisasi hanya di UI).',
                        'Bedakan dari vertical AC: di sini pembandingnya anonim vs terproteksi.',
                    ],
                ],
                4 => [
                    'judul'   => 'Privilege escalation',
                    'metode'  => 'Uji aktif (ubah data - pakai staging)',
                    'tujuan'  => 'Pengguna tak bisa menaikkan haknya sendiri dengan memanipulasi input.',
                    'langkah' => [
                        'Login sebagai role rendah; buka fungsi yang melibatkan field peran/hak (profil, pendaftaran, update akun).',
                        'Tangkap request update lewat proxy; cari parameter hak: role, is_admin, level, group_id, user_id.',
                        'Ubah nilainya ke lebih tinggi (role=user ke role=admin; is_admin=false ke true) lalu kirim.',
                        'Uji mass-assignment: sisipkan field yang tak ada di form asli (mis. tambahkan role:admin di body JSON).',
                        'Gerbang keselamatan: lakukan di staging/akun uji, lalu kembalikan. Logout-login ulang; cek apakah hak naik.',
                    ],
                    'verdict' => [
                        'Server menolak/mengabaikan field hak / tetap role rendah: AMAN.',
                        'Hak benar-benar naik (bisa akses fungsi admin setelahnya): KERENTANAN KRITIS.',
                        'Respons 200 belum tentu perubahan diterapkan; verifikasi dengan login ulang.',
                    ],
                ],
                5 => [
                    'judul'   => 'Force browsing',
                    'metode'  => 'Uji aktif (akses langsung URL)',
                    'tujuan'  => 'Resource tersembunyi tak bisa diakses langsung lewat URL meski tak ada tautannya.',
                    'langkah' => [
                        'Kumpulkan pola path yang terlihat selama penjelajahan (mis. /laporan/2026/01, /berkas/12).',
                        'Coba akses langsung variasi yang tak tertaut: ubah tanggal/nomor, tebak endpoint umum (/backup, /admin, /config, /.env).',
                        'Lakukan sebagai role rendah dan sebagai anonim.',
                        'Untuk daftar besar barulah pertimbangkan wordlist; untuk pendekatan manual cukup path yang paling mungkin.',
                    ],
                    'verdict' => [
                        'Ditolak / 404 / redirect-login: AMAN.',
                        'Resource terbuka padahal seharusnya terbatas: KERENTANAN (sering tumpang-tindih dengan missing function-level auth).',
                        'Catat persis path dan respons sebagai bukti.',
                    ],
                ],
                6 => [
                    'judul'   => 'CORS misconfiguration',
                    'metode'  => 'Uji aktif (baca header - aman)',
                    'tujuan'  => 'Aplikasi tak mengizinkan origin sembarangan membaca respons terautentikasi.',
                    'langkah' => [
                        'Pilih endpoint yang mengembalikan data sensitif saat terautentikasi.',
                        'Lewat proxy/curl, kirim request dengan header Origin berisi domain uji yang kamu kontrol.',
                        'Periksa header respons: Access-Control-Allow-Origin (ACAO) dan Access-Control-Allow-Credentials (ACAC).',
                        'Uji beberapa pola origin: domain acak, subdomain, null, dan domain mirip (menguji regex longgar).',
                    ],
                    'verdict' => [
                        'ACAO tidak memantulkan origin uji / dibatasi ke domain tepercaya: AMAN.',
                        'ACAO memantulkan origin uji + ACAC true: KERENTANAN (situs lain bisa membaca data terautentikasi).',
                        'ACAO * tanpa kredensial: risiko lebih rendah; nilai sesuai sensitivitas data.',
                    ],
                ],
                7 => [
                    'judul'   => 'SSRF (Server-Side Request Forgery)',
                    'metode'  => 'Uji aktif (kehati-hatian tinggi)',
                    'tujuan'  => 'Server tak bisa dipaksa mengirim request ke tujuan yang ditentukan penyerang (jaringan internal, metadata cloud).',
                    'langkah' => [
                        'Temukan fitur di mana server mengambil URL dari input: import dari URL, webhook, preview link, ambil gambar/avatar, generator PDF.',
                        'Siapkan listener yang kamu kontrol untuk melihat apakah server benar-benar menghubungi tujuan.',
                        'Masukkan URL listener; konfirmasi server menghubunginya (membuktikan ada request keluar).',
                        'Coba arahkan ke tujuan sensitif: 127.0.0.1, localhost, 169.254.169.254 (metadata cloud), IP internal.',
                        'Uji bypass: redirect ke target internal, notasi IP alternatif, DNS yang resolve ke IP internal. Batasi pada tujuan uji.',
                    ],
                    'verdict' => [
                        'Server menolak skema/host non-publik, memvalidasi tujuan: AMAN.',
                        'Server mengambil konten internal/metadata dan mengembalikannya: KERENTANAN KRITIS.',
                        'Server menghubungi listener tapi tujuan internal diblokir: request keluar terbatas; catat sebagai perlu perbaikan validasi.',
                    ],
                ],
                8 => [
                    'judul'   => 'Logika otorisasi di kode',
                    'metode'  => 'Review kode / tanya developer',
                    'tujuan'  => 'Pengecekan akses konsisten di semua jalur dan tak ada bypass. Tidak bisa diuji penuh dari luar.',
                    'langkah' => [
                        'Review: apakah ada middleware/guard otorisasi terpusat, atau cek tersebar dan tak konsisten per-controller?',
                        'Review: apakah prinsip deny-by-default diterapkan (default menolak)?',
                        'Review: apakah setiap endpoint sensitif benar-benar memanggil cek otorisasi, tak ada yang terlewat?',
                        'Review: apakah keputusan akses bergantung pada input yang bisa dimanipulasi klien (role dari request, bukan dari sesi server)?',
                        'Tanya developer bila tak ada akses kode: bagaimana otorisasi diterapkan, siapa sumber kebenaran peran, endpoint mana yang sengaja publik.',
                    ],
                    'verdict' => [
                        'Temuan berbasis review, bukan uji otomatis; tandai \'memerlukan tinjauan kode\' dengan daftar titik di atas.',
                        'Hasil uji manual nomor 1-7 jadi bukti pendukung: bila banyak yang bobol, logika otorisasi di kode kemungkinan lemah.',
                    ],
                ],
            ],
        ],
        'a02' => [
            'kode'  => 'A02',
            'nama'  => 'Security Misconfiguration',
            'desk'  => 'Memastikan konfigurasi aman di semua lapis (aplikasi, framework, server). Sweet spot pengujian; naik ke peringkat dua pada 2025.',
            'butir' => [
                1 => [
                    'judul'   => 'Security headers',
                    'metode'  => 'Uji aktif (baca header - aman)',
                    'tujuan'  => 'Header keamanan penting hadir dan bernilai benar.',
                    'langkah' => [
                        'Buka beberapa halaman (publik dan terautentikasi) lewat proxy/curl, baca header respons.',
                        'Periksa Content-Security-Policy, X-Frame-Options (atau CSP frame-ancestors), X-Content-Type-Options, Referrer-Policy.',
                        'Nilai kualitas isinya, bukan sekadar ada: CSP dengan unsafe-inline atau wildcard berlebihan masih lemah.',
                    ],
                    'verdict' => [
                        'Header ada dan membatasi dengan benar: AMAN.',
                        'Header hilang atau terlalu longgar: KERENTANAN tingkat rendah-sedang.',
                        'Catat header apa yang hilang per endpoint.',
                    ],
                ],
                2 => [
                    'judul'   => 'Error verbose',
                    'metode'  => 'Uji aktif',
                    'tujuan'  => 'Respons error tak membocorkan stack trace, path, atau detail internal.',
                    'langkah' => [
                        'Picu kondisi error: 404, input tak valid, tipe data salah, parameter dihapus.',
                        'Periksa respons untuk stack trace, path server, versi framework, query SQL.',
                    ],
                    'verdict' => [
                        'Error generik tanpa detail internal: AMAN.',
                        'Stack trace / path / query bocor: KERENTANAN (information disclosure).',
                    ],
                ],
                3 => [
                    'judul'   => 'Directory listing',
                    'metode'  => 'Uji aktif',
                    'tujuan'  => 'Folder tak menampilkan daftar isinya.',
                    'langkah' => [
                        'Akses path folder langsung (mis. /uploads/, /files/, /assets/).',
                        'Cari penanda \'Index of /\' atau daftar berkas.',
                    ],
                    'verdict' => [
                        'Folder mengembalikan 403/404 atau halaman normal: AMAN.',
                        'Daftar isi folder tampil: KERENTANAN.',
                    ],
                ],
                4 => [
                    'judul'   => 'File/endpoint sensitif terekspos',
                    'metode'  => 'Uji aktif',
                    'tujuan'  => 'Berkas dan endpoint internal tak terjangkau publik.',
                    'langkah' => [
                        'Coba akses: /.env, /.git/config, /config, backup (.bak, .zip, .sql), /phpinfo.php, /.DS_Store.',
                        'Coba endpoint debug/dokumentasi: /swagger, /actuator, /api/docs.',
                        'Lakukan sebagai anonim.',
                    ],
                    'verdict' => [
                        'Semua ditolak/404: AMAN.',
                        'Berkas/endpoint sensitif terbuka: KERENTANAN (sering kritis bila .env/.git bocor).',
                    ],
                ],
                5 => [
                    'judul'   => 'Metode HTTP berbahaya',
                    'metode'  => 'Uji aktif',
                    'tujuan'  => 'Metode yang tak perlu (TRACE/PUT/DELETE) tak aktif tanpa alasan.',
                    'langkah' => [
                        'Kirim OPTIONS ke endpoint; baca header Allow.',
                        'Uji TRACE, PUT, DELETE pada endpoint; amati apakah diproses.',
                    ],
                    'verdict' => [
                        'Hanya metode yang diperlukan aktif: AMAN.',
                        'TRACE/PUT/DELETE aktif tanpa alasan: KERENTANAN (nilai sesuai dampak).',
                    ],
                ],
                6 => [
                    'judul'   => 'Kredensial / akun default',
                    'metode'  => 'Uji aktif (intrusive - otorisasi)',
                    'tujuan'  => 'Panel atau layanan tak memakai kredensial bawaan.',
                    'langkah' => [
                        'Identifikasi panel/login yang dikenal (admin, phpMyAdmin, panel framework).',
                        'Coba kredensial default umum sesuai produk (admin/admin, dll) secara terbatas; ini intrusive, butuh otorisasi eksplisit.',
                    ],
                    'verdict' => [
                        'Login default ditolak: AMAN.',
                        'Login default berhasil: KERENTANAN KRITIS.',
                    ],
                ],
                7 => [
                    'judul'   => 'Mode debug di produksi',
                    'metode'  => 'Uji aktif + konfirmasi',
                    'tujuan'  => 'Mode debug/trace framework mati di lingkungan produksi.',
                    'langkah' => [
                        'Picu error dan amati apakah muncul halaman debug framework (mis. Whoops/Laravel debug, Django debug).',
                        'Konfirmasi ke developer bila ragu.',
                    ],
                    'verdict' => [
                        'Tak ada halaman debug: AMAN.',
                        'Halaman debug tampil (membocorkan env, konfigurasi, trace): KERENTANAN.',
                    ],
                ],
                8 => [
                    'judul'   => 'Hardening server/platform',
                    'metode'  => 'Review / tanya developer',
                    'tujuan'  => 'Konfigurasi OS, web server, dan container sudah di-harden.',
                    'langkah' => [
                        'Tanya/review: versi web server, konfigurasi TLS, pembatasan modul, hak berkas, isolasi container.',
                        'Sebagian tak terlihat dari luar; andalkan review konfigurasi.',
                    ],
                    'verdict' => [
                        'Temuan berbasis review; catat sebagai perlu tinjauan konfigurasi dengan daftar titik di atas.',
                    ],
                ],
            ],
        ],
        'a03' => [
            'kode'  => 'A03',
            'nama'  => 'Software Supply Chain Failures',
            'desk'  => 'Memastikan semua komponen pihak ketiga (library, paket, image, pipeline) tepercaya dan tak membawa kerentanan. Kategori baru 2025; sebagian besar butuh akses manifest/proses.',
            'butir' => [
                1 => [
                    'judul'   => 'Komponen versi rentan (terlihat)',
                    'metode'  => 'Uji aktif (indikatif)',
                    'tujuan'  => 'Versi komponen rentan yang bocor ke luar teridentifikasi.',
                    'langkah' => [
                        'Amati header, komentar HTML, berkas statis, dan error untuk versi framework/library.',
                        'Cocokkan versi yang bocor dengan basis data kerentanan yang diketahui (indikasi, bukan konfirmasi).',
                    ],
                    'verdict' => [
                        'Tak ada versi rentan terekspos: AMAN dari sisi luar.',
                        'Versi rentan terlihat: KERENTANAN indikatif; konfirmasi dampaknya.',
                    ],
                ],
                2 => [
                    'judul'   => 'Inventaris dependensi / SBOM',
                    'metode'  => 'Review / tanya developer',
                    'tujuan'  => 'Semua dependensi diketahui dan dipindai (SCA).',
                    'langkah' => [
                        'Minta manifest (package-lock.json, composer.lock, requirements.txt) dan jalankan SCA.',
                        'Tanya apakah ada proses pemindaian dependensi berkala.',
                    ],
                    'verdict' => [
                        'Temuan berbasis manifest/review; di luar jangkauan black-box.',
                    ],
                ],
                3 => [
                    'judul'   => 'Integritas CI/CD',
                    'metode'  => 'Tanya developer',
                    'tujuan'  => 'Pipeline build tak bisa disusupi.',
                    'langkah' => [
                        'Tanya: siapa punya akses ke pipeline, bagaimana secret dikelola, apakah build terisolasi.',
                    ],
                    'verdict' => [
                        'Temuan berbasis wawancara proses; catat gap yang ditemukan.',
                    ],
                ],
                4 => [
                    'judul'   => 'Sumber paket tepercaya',
                    'metode'  => 'Review',
                    'tujuan'  => 'Registry dan lockfile terkunci; tak ada paket mencurigakan.',
                    'langkah' => [
                        'Review konfigurasi dependensi: registry yang dipakai, apakah lockfile dikomit, pinning versi.',
                    ],
                    'verdict' => [
                        'Temuan berbasis review repo.',
                    ],
                ],
                5 => [
                    'judul'   => 'Verifikasi tanda tangan artefak',
                    'metode'  => 'Tanya developer',
                    'tujuan'  => 'Update/artefak diverifikasi sebelum dipakai.',
                    'langkah' => [
                        'Tanya apakah artefak/image yang di-deploy diverifikasi tanda tangannya.',
                    ],
                    'verdict' => [
                        'Temuan berbasis wawancara proses deploy.',
                    ],
                ],
            ],
        ],
        'a04' => [
            'kode'  => 'A04',
            'nama'  => 'Cryptographic Failures',
            'desk'  => 'Memastikan data sensitif dilindungi kriptografi yang benar, baik saat dikirim maupun disimpan. Pengujian dari luar hanya menjangkau lapis transport.',
            'butir' => [
                1 => [
                    'judul'   => 'Konfigurasi TLS',
                    'metode'  => 'Uji aktif (alat TLS)',
                    'tujuan'  => 'Versi, cipher, sertifikat, dan HSTS aman.',
                    'langkah' => [
                        'Jalankan testssl.sh terhadap host, atau pakai layanan uji TLS, atau openssl s_client secara manual.',
                        'Periksa: versi (tolak TLS 1.0/1.1), cipher (tak ada RC4/3DES/EXPORT/NULL), sertifikat (valid, hostname cocok, chain lengkap), HSTS.',
                    ],
                    'verdict' => [
                        'Semua parameter kuat: AMAN.',
                        'Protokol/cipher lemah atau sertifikat bermasalah: KERENTANAN.',
                    ],
                ],
                2 => [
                    'judul'   => 'Data sensitif via channel tidak aman',
                    'metode'  => 'Uji aktif',
                    'tujuan'  => 'Tak ada data sensitif dikirim lewat HTTP polos.',
                    'langkah' => [
                        'Saat crawl, amati apakah ada halaman/form sensitif dilayani via HTTP, atau submit ke endpoint HTTP.',
                    ],
                    'verdict' => [
                        'Semua via HTTPS: AMAN.',
                        'Data sensitif via HTTP: KERENTANAN.',
                    ],
                ],
                3 => [
                    'judul'   => 'Data sensitif bocor di respons/URL',
                    'metode'  => 'Uji aktif',
                    'tujuan'  => 'Data sensitif tak muncul di body/URL yang tak semestinya.',
                    'langkah' => [
                        'Periksa respons untuk data pribadi (NIK, token, password) yang tak perlu.',
                        'Periksa apakah token/sesi muncul di query string (bisa bocor via log/referrer).',
                    ],
                    'verdict' => [
                        'Tak ada kebocoran: AMAN.',
                        'Data sensitif/sesi di URL atau body tak semestinya: KERENTANAN. Sanitasi bukti sebelum disimpan.',
                    ],
                ],
                4 => [
                    'judul'   => 'Enkripsi data at-rest',
                    'metode'  => 'Review / tanya developer',
                    'tujuan'  => 'Data sensitif di DB/berkas terenkripsi.',
                    'langkah' => [
                        'Tanya/review apakah kolom sensitif atau berkas disimpan terenkripsi.',
                    ],
                    'verdict' => [
                        'Temuan berbasis review/wawancara.',
                    ],
                ],
                5 => [
                    'judul'   => 'Hashing password',
                    'metode'  => 'Review / tanya developer',
                    'tujuan'  => 'Password di-hash dengan algoritma kuat (bcrypt/argon2), bukan MD5/SHA1 polos.',
                    'langkah' => [
                        'Review kode autentikasi atau tanya algoritma hashing yang dipakai.',
                    ],
                    'verdict' => [
                        'Temuan berbasis review kode.',
                    ],
                ],
                6 => [
                    'judul'   => 'Manajemen kunci',
                    'metode'  => 'Tanya developer',
                    'tujuan'  => 'Kunci kriptografi disimpan dan dirotasi secara aman.',
                    'langkah' => [
                        'Tanya di mana kunci disimpan, siapa yang punya akses, apakah ada rotasi.',
                    ],
                    'verdict' => [
                        'Temuan berbasis wawancara arsitektur.',
                    ],
                ],
            ],
        ],
        'a05' => [
            'kode'  => 'A05',
            'nama'  => 'Injection',
            'desk'  => 'Memastikan input tak tepercaya tak pernah dieksekusi sebagai perintah atau kode (SQL, OS, template, HTML). Scanner memberi indikasi kuat; konfirmasi sering perlu analis.',
            'butir' => [
                1 => [
                    'judul'   => 'SQL / NoSQL injection',
                    'metode'  => 'Uji aktif (hati-hati di produksi)',
                    'tujuan'  => 'Query tak bisa dimanipulasi lewat input.',
                    'langkah' => [
                        'Identifikasi parameter yang kemungkinan masuk query (id, filter, search, login).',
                        'Uji payload aman (tanda kutip tunggal, boolean sederhana) dan amati error atau perubahan hasil.',
                        'Hindari payload destruktif; untuk time-based perhatikan beban server.',
                    ],
                    'verdict' => [
                        'Input ditangani aman (tak ada error/perubahan): AMAN indikatif.',
                        'Error SQL atau perubahan hasil sesuai manipulasi: KERENTANAN. Konfirmasi hati-hati.',
                    ],
                ],
                2 => [
                    'judul'   => 'Reflected / DOM XSS',
                    'metode'  => 'Uji aktif',
                    'tujuan'  => 'Input tak dipantulkan ke halaman tanpa encoding.',
                    'langkah' => [
                        'Temukan titik di mana input muncul kembali di respons (parameter pencarian, pesan error).',
                        'Masukkan payload penanda unik dan periksa apakah dieksekusi atau ter-encode.',
                    ],
                    'verdict' => [
                        'Input ter-encode dengan benar: AMAN.',
                        'Payload dieksekusi: KERENTANAN XSS.',
                    ],
                ],
                3 => [
                    'judul'   => 'Stored XSS',
                    'metode'  => 'Uji aktif + verifikasi manual',
                    'tujuan'  => 'Input tersimpan tak dieksekusi saat ditampilkan kembali.',
                    'langkah' => [
                        'Masukkan payload penanda di field yang tersimpan (komentar, profil, nama).',
                        'Buka halaman yang menampilkannya kembali (termasuk oleh pengguna/role lain).',
                    ],
                    'verdict' => [
                        'Payload ter-encode saat ditampilkan: AMAN.',
                        'Payload dieksekusi saat dimuat: KERENTANAN (stored XSS, dampak lebih tinggi dari reflected).',
                    ],
                ],
                4 => [
                    'judul'   => 'Command injection / SSTI',
                    'metode'  => 'Uji aktif (kehati-hatian tinggi)',
                    'tujuan'  => 'Input tak dieksekusi sebagai perintah OS atau ekspresi template di server.',
                    'langkah' => [
                        'Identifikasi fitur yang mungkin mengeksekusi input (konversi berkas, template email, fungsi sistem).',
                        'Uji payload non-destruktif (ekspresi aritmatika untuk SSTI, delay kecil); hentikan bila ada tanda eksekusi.',
                    ],
                    'verdict' => [
                        'Input ditangani sebagai data: AMAN.',
                        'Ekspresi/perintah dieksekusi: KERENTANAN KRITIS.',
                    ],
                ],
                5 => [
                    'judul'   => 'Path traversal / LFI',
                    'metode'  => 'Uji aktif',
                    'tujuan'  => 'Tak bisa membaca berkas di luar direktori yang diizinkan.',
                    'langkah' => [
                        'Temukan parameter yang menunjuk berkas/path (file, download, template, lang).',
                        'Uji payload traversal terbatas untuk membaca berkas uji; jangan akses berkas sistem sensitif bila tak perlu.',
                    ],
                    'verdict' => [
                        'Akses dibatasi: AMAN.',
                        'Berkas di luar web root terbaca: KERENTANAN.',
                    ],
                ],
                6 => [
                    'judul'   => 'Validasi & parameterisasi',
                    'metode'  => 'Review kode',
                    'tujuan'  => 'Query berparameter dan input tervalidasi di sisi kode.',
                    'langkah' => [
                        'Review apakah query memakai prepared statement/ORM dan input divalidasi.',
                    ],
                    'verdict' => [
                        'Temuan berbasis review kode; uji aktif di atas jadi bukti pendukung.',
                    ],
                ],
            ],
        ],
        'a06' => [
            'kode'  => 'A06',
            'nama'  => 'Insecure Design',
            'desk'  => 'Memastikan keamanan dipikirkan sejak desain (threat modeling, pola aman, kontrol pada alur bisnis). Cacat desain tak bisa di-patch dan hampir seluruhnya di luar jangkauan scanner.',
            'butir' => [
                1 => [
                    'judul'   => 'Threat modeling',
                    'metode'  => 'Tanya developer / tinjau dokumen',
                    'tujuan'  => 'Ancaman diidentifikasi sejak perancangan.',
                    'langkah' => [
                        'Tanya apakah ada dokumen threat model / analisis risiko saat desain.',
                        'Tinjau apakah kontrol keamanan direncanakan, bukan ditambal.',
                    ],
                    'verdict' => [
                        'Temuan berbasis dokumen/wawancara.',
                    ],
                ],
                2 => [
                    'judul'   => 'Kontrol alur bisnis kritis',
                    'metode'  => 'Tanya + uji skenario',
                    'tujuan'  => 'Batas dan aturan pada proses sensitif (transfer, approval, perubahan status) ditegakkan.',
                    'langkah' => [
                        'Petakan alur bisnis kritis bersama pemilik aplikasi.',
                        'Uji skenario penyalahgunaan yang terdefinisi (mis. lewati langkah approval, ulangi aksi yang seharusnya sekali).',
                    ],
                    'verdict' => [
                        'Alur menegakkan aturan: AMAN.',
                        'Langkah bisa dilewati / aturan bisa diakali: KERENTANAN desain.',
                    ],
                ],
                3 => [
                    'judul'   => 'Rate limiting fungsi mahal',
                    'metode'  => 'Uji terbatas + konfirmasi',
                    'tujuan'  => 'Fungsi berat atau rawan abuse dibatasi.',
                    'langkah' => [
                        'Identifikasi fungsi mahal (kirim OTP/email, pencarian berat, generate laporan).',
                        'Uji pengulangan terbatas; amati apakah ada pembatasan. Konfirmasi kebijakan ke developer.',
                    ],
                    'verdict' => [
                        'Ada pembatasan wajar: AMAN.',
                        'Tak ada pembatasan pada fungsi rawan abuse: KERENTANAN.',
                    ],
                ],
                4 => [
                    'judul'   => 'Pemisahan tenant / data',
                    'metode'  => 'Review / tanya developer',
                    'tujuan'  => 'Desain memisahkan data antar entitas/tenant.',
                    'langkah' => [
                        'Review arsitektur atau tanya bagaimana data antar unit/tenant dipisah.',
                    ],
                    'verdict' => [
                        'Temuan berbasis review arsitektur.',
                    ],
                ],
                5 => [
                    'judul'   => 'Deny-by-default',
                    'metode'  => 'Review kode',
                    'tujuan'  => 'Default menolak; akses diberikan secara eksplisit.',
                    'langkah' => [
                        'Review apakah kontrol akses default menolak, lalu membuka secara eksplisit.',
                    ],
                    'verdict' => [
                        'Temuan berbasis review kode.',
                    ],
                ],
            ],
        ],
        'a07' => [
            'kode'  => 'A07',
            'nama'  => 'Authentication Failures',
            'desk'  => 'Memastikan identitas diverifikasi dengan benar dan sesi dikelola aman sehingga akun tak bisa diambil alih. Dengan kredensial yang disediakan, cakupan di sini naik banyak.',
            'butir' => [
                1 => [
                    'judul'   => 'Atribut cookie sesi',
                    'metode'  => 'Uji aktif (baca header - aman)',
                    'tujuan'  => 'Cookie sesi memakai atribut keamanan.',
                    'langkah' => [
                        'Setelah login, baca Set-Cookie pada cookie sesi.',
                        'Periksa atribut Secure, HttpOnly, dan SameSite.',
                    ],
                    'verdict' => [
                        'Ketiga atribut ada: AMAN.',
                        'Salah satu hilang: KERENTANAN tingkat rendah-sedang.',
                    ],
                ],
                2 => [
                    'judul'   => 'Rotasi session ID saat login',
                    'metode'  => 'Uji aktif',
                    'tujuan'  => 'ID sesi berubah setelah autentikasi (anti session fixation).',
                    'langkah' => [
                        'Catat cookie sesi sebelum login.',
                        'Login, lalu bandingkan apakah ID sesi berganti.',
                    ],
                    'verdict' => [
                        'ID sesi berganti setelah login: AMAN.',
                        'ID sesi tetap sama: KERENTANAN (session fixation).',
                    ],
                ],
                3 => [
                    'judul'   => 'Username enumeration',
                    'metode'  => 'Uji aktif',
                    'tujuan'  => 'Respons tak membedakan akun yang ada dari yang tidak.',
                    'langkah' => [
                        'Kirim upaya login/reset untuk user yang pasti ada vs user acak.',
                        'Bandingkan pesan, kode status, dan waktu respons.',
                    ],
                    'verdict' => [
                        'Respons seragam: AMAN.',
                        'Respons membedakan akun ada/tidak: KERENTANAN (enumeration).',
                    ],
                ],
                4 => [
                    'judul'   => 'Rate limit / lockout login',
                    'metode'  => 'Uji aktif (intrusive - otorisasi)',
                    'tujuan'  => 'Ada pembatasan terhadap upaya login gagal.',
                    'langkah' => [
                        'Kirim sejumlah upaya gagal secara terbatas (hormati batas, hindari lockout akun nyata).',
                        'Amati apakah muncul throttle/lockout/CAPTCHA.',
                    ],
                    'verdict' => [
                        'Ada pembatasan: AMAN.',
                        'Tak ada pembatasan: KERENTANAN (rawan brute force).',
                    ],
                ],
                5 => [
                    'judul'   => 'Kebijakan password',
                    'metode'  => 'Uji aktif + konfirmasi',
                    'tujuan'  => 'Panjang/kompleksitas minimum ditegakkan.',
                    'langkah' => [
                        'Coba daftar/ganti password dengan password sangat lemah.',
                        'Konfirmasi kebijakan ke developer bila perlu.',
                    ],
                    'verdict' => [
                        'Password lemah ditolak: AMAN.',
                        'Password lemah diterima: KERENTANAN.',
                    ],
                ],
                6 => [
                    'judul'   => 'MFA tersedia & diterapkan',
                    'metode'  => 'Tanya developer / amati alur',
                    'tujuan'  => 'Faktor kedua tersedia untuk akun sensitif.',
                    'langkah' => [
                        'Amati alur login untuk opsi MFA.',
                        'Tanya apakah MFA diwajibkan untuk peran sensitif.',
                    ],
                    'verdict' => [
                        'Temuan berbasis observasi + wawancara.',
                    ],
                ],
                7 => [
                    'judul'   => 'Session timeout & logout',
                    'metode'  => 'Uji aktif',
                    'tujuan'  => 'Sesi kedaluwarsa dan logout benar-benar membatalkan sesi.',
                    'langkah' => [
                        'Setelah logout, replay request terautentikasi dengan cookie lama.',
                        'Diamkan sesi idle, lalu uji apakah masih berlaku setelah batas waktu.',
                    ],
                    'verdict' => [
                        'Sesi batal setelah logout/timeout: AMAN.',
                        'Cookie lama masih berfungsi: KERENTANAN.',
                    ],
                ],
                8 => [
                    'judul'   => 'Reset password aman',
                    'metode'  => 'Uji aktif + review bila perlu',
                    'tujuan'  => 'Token reset sekali pakai, kedaluwarsa, dan tak mudah ditebak.',
                    'langkah' => [
                        'Jalankan alur reset; periksa token (panjang, acak, sekali pakai, kedaluwarsa).',
                        'Uji apakah token bisa dipakai ulang atau untuk akun lain.',
                    ],
                    'verdict' => [
                        'Token aman dan sekali pakai: AMAN.',
                        'Token lemah/dapat dipakai ulang: KERENTANAN.',
                    ],
                ],
            ],
        ],
        'a08' => [
            'kode'  => 'A08',
            'nama'  => 'Software or Data Integrity Failures',
            'desk'  => 'Memastikan kode, update, dan data kritis tak bisa dimanipulasi tanpa terdeteksi (deserialisasi aman, update bertanda tangan, pipeline tepercaya). Sebagian besar properti kode/proses.',
            'butir' => [
                1 => [
                    'judul'   => 'Subresource Integrity (SRI)',
                    'metode'  => 'Uji aktif (baca HTML - aman)',
                    'tujuan'  => 'Skrip pihak ketiga di HTML memakai atribut integrity.',
                    'langkah' => [
                        'Periksa tag script/link ke sumber eksternal di HTML.',
                        'Cek apakah ada atribut integrity (hash) dan crossorigin.',
                    ],
                    'verdict' => [
                        'Skrip eksternal memakai SRI: AMAN.',
                        'Tanpa SRI: KERENTANAN tingkat rendah-sedang (skrip pihak ketiga bisa diubah tanpa deteksi).',
                    ],
                ],
                2 => [
                    'judul'   => 'Deserialisasi tak aman',
                    'metode'  => 'Review kode (indikasi terbatas)',
                    'tujuan'  => 'Data tak tepercaya tak di-deserialize secara mentah.',
                    'langkah' => [
                        'Review apakah ada deserialisasi objek dari input pengguna (cookie, parameter).',
                        'Indikasi terbatas dari luar; utamakan review kode.',
                    ],
                    'verdict' => [
                        'Temuan berbasis review kode.',
                    ],
                ],
                3 => [
                    'judul'   => 'Update / paket bertanda tangan',
                    'metode'  => 'Tanya developer',
                    'tujuan'  => 'Update dan artefak diverifikasi sebelum dipasang.',
                    'langkah' => [
                        'Tanya apakah proses update memverifikasi tanda tangan/hash.',
                    ],
                    'verdict' => [
                        'Temuan berbasis wawancara proses.',
                    ],
                ],
                4 => [
                    'judul'   => 'Integritas pipeline CI/CD',
                    'metode'  => 'Tanya developer',
                    'tujuan'  => 'Build tak bisa disusupi kode jahat.',
                    'langkah' => [
                        'Tanya kontrol akses pipeline, review kode wajib, dan isolasi build.',
                    ],
                    'verdict' => [
                        'Temuan berbasis wawancara proses.',
                    ],
                ],
                5 => [
                    'judul'   => 'Sumber auto-update tepercaya',
                    'metode'  => 'Review / tanya developer',
                    'tujuan'  => 'Aplikasi tak menarik kode dari sumber tak terverifikasi.',
                    'langkah' => [
                        'Review/tanya dari mana aplikasi mengambil dependensi/kode saat runtime.',
                    ],
                    'verdict' => [
                        'Temuan berbasis review/wawancara.',
                    ],
                ],
            ],
        ],
        'a09' => [
            'kode'  => 'A09',
            'nama'  => 'Security Logging and Alerting Failures',
            'desk'  => 'Memastikan aktivitas keamanan dicatat, dipantau, dan memicu alert sehingga insiden terdeteksi. Tak bisa diamati dari luar; ini ranah SOC/monitoring.',
            'butir' => [
                1 => [
                    'judul'   => 'Event keamanan dicatat',
                    'metode'  => 'Tanya developer / tinjau konfigurasi',
                    'tujuan'  => 'Login gagal, akses ditolak, dan perubahan hak tercatat.',
                    'langkah' => [
                        'Tanya/tinjau apakah event keamanan utama dicatat dengan konteks memadai (waktu, aktor, aksi).',
                    ],
                    'verdict' => [
                        'Temuan berbasis wawancara/tinjauan log.',
                    ],
                ],
                2 => [
                    'judul'   => 'Log bebas data sensitif',
                    'metode'  => 'Review kode',
                    'tujuan'  => 'Password dan token tak masuk ke log.',
                    'langkah' => [
                        'Review kode logging untuk kebocoran data sensitif.',
                    ],
                    'verdict' => [
                        'Temuan berbasis review kode.',
                    ],
                ],
                3 => [
                    'judul'   => 'Log terlindungi',
                    'metode'  => 'Tanya developer',
                    'tujuan'  => 'Log tak bisa dihapus/diubah oleh penyerang atau pengguna biasa.',
                    'langkah' => [
                        'Tanya di mana log disimpan dan bagaimana integritasnya dijaga.',
                    ],
                    'verdict' => [
                        'Temuan berbasis wawancara arsitektur.',
                    ],
                ],
                4 => [
                    'judul'   => 'Alerting aktif',
                    'metode'  => 'Tinjau monitoring',
                    'tujuan'  => 'Aktivitas mencurigakan memicu alert.',
                    'langkah' => [
                        'Tinjau apakah ada aturan alert untuk pola mencurigakan.',
                    ],
                    'verdict' => [
                        'Temuan berbasis tinjauan konfigurasi monitoring.',
                    ],
                ],
                5 => [
                    'judul'   => 'Integrasi SOC / SIEM',
                    'metode'  => 'Verifikasi di platform SOC',
                    'tujuan'  => 'Log diteruskan dan dikorelasikan terpusat.',
                    'langkah' => [
                        'Verifikasi di platform SOC/Wazuh apakah aplikasi mengirim log dan terpantau.',
                    ],
                    'verdict' => [
                        'Temuan berbasis verifikasi SOC; kategori ini paling tepat ditutup lewat monitoring, bukan pengujian aplikasi.',
                    ],
                ],
            ],
        ],
        'a10' => [
            'kode'  => 'A10',
            'nama'  => 'Mishandling of Exceptional Conditions',
            'desk'  => 'Memastikan error, exception, dan kondisi tak terduga ditangani aman dan konsisten; sistem harus gagal dengan menolak (fail securely), bukan membuka akses. Kategori baru 2025.',
            'butir' => [
                1 => [
                    'judul'   => 'Error tak membocorkan info internal',
                    'metode'  => 'Uji aktif',
                    'tujuan'  => 'Respons error tak memuat detail internal (overlap dengan A02).',
                    'langkah' => [
                        'Picu berbagai kondisi error dan periksa respons.',
                    ],
                    'verdict' => [
                        'Error generik: AMAN.',
                        'Detail internal bocor: KERENTANAN (lihat juga A02 error verbose).',
                    ],
                ],
                2 => [
                    'judul'   => 'Fail securely',
                    'metode'  => 'Review kode',
                    'tujuan'  => 'Saat error, akses ditolak, bukan diizinkan.',
                    'langkah' => [
                        'Review jalur penanganan error pada cek keamanan: apakah default saat gagal adalah menolak?',
                    ],
                    'verdict' => [
                        'Temuan berbasis review kode.',
                    ],
                ],
                3 => [
                    'judul'   => 'Penanganan exception konsisten',
                    'metode'  => 'Review / tanya developer',
                    'tujuan'  => 'Tak ada state setengah jadi saat operasi gagal.',
                    'langkah' => [
                        'Review/tanya bagaimana transaksi/operasi ditangani saat terjadi exception di tengah.',
                    ],
                    'verdict' => [
                        'Temuan berbasis review/wawancara.',
                    ],
                ],
                4 => [
                    'judul'   => 'Timeout & resource exhaustion',
                    'metode'  => 'Uji terbatas + konfirmasi',
                    'tujuan'  => 'Kondisi beban/timeout ditangani wajar.',
                    'langkah' => [
                        'Uji terbatas input besar/lambat; amati perilaku. Hormati batas dan hindari mengganggu layanan.',
                    ],
                    'verdict' => [
                        'Ditangani wajar: AMAN.',
                        'Crash/hang/perilaku tak terduga: perlu perbaikan.',
                    ],
                ],
                5 => [
                    'judul'   => 'Kontrol tak bisa di-bypass via error',
                    'metode'  => 'Review + uji',
                    'tujuan'  => 'Memaksa error tak melewati cek keamanan.',
                    'langkah' => [
                        'Uji apakah memicu error pada satu langkah membuat langkah berikutnya melewati otorisasi.',
                        'Dukung dengan review logika bila memungkinkan.',
                    ],
                    'verdict' => [
                        'Kontrol tetap berlaku saat error: AMAN.',
                        'Error membuka bypass: KERENTANAN.',
                    ],
                ],
            ],
        ],
    ];

    /** Kunci tab kategori: a01 … a10. */
    public static function kunci(): array
    {
        return array_keys(self::KATEGORI);
    }

    public static function kategori(string $k): ?array
    {
        return self::KATEGORI[$k] ?? null;
    }

    /** Jenis metode untuk penanda warna: aktif | hati (berisiko/intrusif) | tinjau (review/wawancara). */
    public static function jenisMetode(string $metode): string
    {
        $m = strtolower($metode);
        if (str_contains($m, 'hati') || str_contains($m, 'intrusive') || str_contains($m, 'ubah data')) return 'hati';
        if (str_starts_with($m, 'uji')) return 'aktif';
        return 'tinjau';
    }
}
