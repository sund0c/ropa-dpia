<?php

namespace App\Http\Controllers;

use App\Support\LabelTte;
use App\Support\PdfFooter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Modul D — Laporan Penanganan Insiden.
 * Mengacu ISO/IEC 27035, NIST SP 800-61 Rev. 3, dan Peraturan BSSN No. 1 Tahun 2024.
 * Data hanya di session ('insiden'), satu insiden per sesi.
 */
class InsidenController extends Controller
{
    /** Daftar tab. Tab berikutnya cukup ditambahkan di sini + di rules(). */
    public const TABS = [
        'informasi'   => 'I. Informasi Insiden',
        'klasifikasi' => 'II. Klasifikasi & Dampak',
        'kronologi'   => 'III. Kronologi',
        'analisis'    => 'IV. Analisis',
        'penanganan'  => 'V. Penanganan',
        'pelajaran'   => 'VI. Pelajaran & Tindak Lanjut',
    ];

    /** Tab khusus Laporan Akhir (evaluasi dilakukan setelah insiden ditutup). */
    private const TAB_AKHIR = ['pelajaran'];

    // ---------------------------------------------------------------------
    //  Tab VI — Pelajaran & Tindak Lanjut
    // ---------------------------------------------------------------------

    public const ASPEK = [
        'deteksi'    => 'Deteksi',
        'tanggapan'  => 'Tanggapan',
        'pemulihan'  => 'Pemulihan',
        'komunikasi' => 'Komunikasi',
        'pencegahan' => 'Pencegahan',
    ];

    public const STATUS_TL = [
        'belum'    => 'Belum mulai',
        'berjalan' => 'Berjalan',
        'selesai'  => 'Selesai',
    ];

    /** Saran kontrol ISO/IEC 27001:2022 Annex A yang sering muncul pada tindak lanjut insiden. */
    public const KONTROL = [
        'A.5.9 Inventory of information and other associated assets',
        'A.5.15 Access control',
        'A.5.17 Authentication information',
        'A.5.24 Incident management planning and preparation',
        'A.5.25 Assessment and decision on information security events',
        'A.5.26 Response to information security incidents',
        'A.5.27 Learning from information security incidents',
        'A.5.28 Collection of evidence',
        'A.6.3 Information security awareness, education and training',
        'A.8.2 Privileged access rights',
        'A.8.5 Secure authentication',
        'A.8.7 Protection against malware',
        'A.8.8 Management of technical vulnerabilities',
        'A.8.9 Configuration management',
        'A.8.13 Information backup',
        'A.8.15 Logging',
        'A.8.16 Monitoring activities',
        'A.8.20 Networks security',
        'A.8.28 Secure coding',
    ];

    /** Tab yang tidak dipakai Laporan Awal (penyebab & dampak belum dianalisis). */
    private const TAB_LANJUTAN = ['analisis', 'penanganan'];

    // ---------------------------------------------------------------------
    //  Tab V — Penanganan
    // ---------------------------------------------------------------------

    public const MAKS_BUKTI = 30;

    // ---------------------------------------------------------------------
    //  Tab IV — Analisis
    // ---------------------------------------------------------------------

    /** Taktik MITRE ATT&CK (Enterprise), dalam urutan rantai serangan. */
    public const TAKTIK = [
        'pengintaian'   => 'Pengintaian (Reconnaissance)',
        'persiapan'     => 'Persiapan Sumber Daya (Resource Development)',
        'akses_awal'    => 'Akses Awal (Initial Access)',
        'eksekusi'      => 'Eksekusi (Execution)',
        'persistensi'   => 'Persistensi (Persistence)',
        'eskalasi'      => 'Eskalasi Hak Akses (Privilege Escalation)',
        'penghindaran'  => 'Penghindaran Deteksi (Defense Evasion)',
        'kredensial'    => 'Akses Kredensial (Credential Access)',
        'penemuan'      => 'Penemuan (Discovery)',
        'lateral'       => 'Pergerakan Lateral (Lateral Movement)',
        'pengumpulan'   => 'Pengumpulan (Collection)',
        'kendali'       => 'Komando & Kendali (Command and Control)',
        'eksfiltrasi'   => 'Eksfiltrasi (Exfiltration)',
        'dampak'        => 'Dampak (Impact)',
    ];

    /** Saran teknik ATT&CK yang umum pada insiden situs web pemerintah. */
    public const TEKNIK = [
        'T1595 Active Scanning',
        'T1589.001 Gather Victim Identity Information: Credentials',
        'T1190 Exploit Public-Facing Application',
        'T1078 Valid Accounts',
        'T1110.001 Brute Force: Password Guessing',
        'T1110.004 Brute Force: Credential Stuffing',
        'T1566 Phishing',
        'T1059 Command and Scripting Interpreter',
        'T1505.003 Server Software Component: Web Shell',
        'T1136 Create Account',
        'T1068 Exploitation for Privilege Escalation',
        'T1070 Indicator Removal',
        'T1082 System Information Discovery',
        'T1083 File and Directory Discovery',
        'T1005 Data from Local System',
        'T1041 Exfiltration Over C2 Channel',
        'T1486 Data Encrypted for Impact',
        'T1491.002 Defacement: External Defacement',
        'T1498 Network Denial of Service',
        'T1565.001 Data Manipulation: Stored Data Manipulation',
    ];

    public const JENIS_IOC = [
        'ip'        => 'Alamat IP',
        'domain'    => 'Domain',
        'url'       => 'URL / pola URL',
        'hash'      => 'Hash berkas',
        'berkas'    => 'Nama / lokasi berkas',
        'email'     => 'Alamat email',
        'useragent' => 'User-Agent',
        'akun'      => 'Akun pengguna',
        'lainnya'   => 'Lainnya',
    ];

    public const MAKS_BARIS = 30;
    public const JUMLAH_WHY = 5;

    /** Baris yang seluruh kolomnya kosong dianggap tidak diisi. */
    private static function buangBarisKosong($rows): array
    {
        return array_values(array_filter((array) $rows, fn ($r) => is_array($r)
            && collect($r)->contains(fn ($v) => is_string($v) ? trim($v) !== '' : filled($v))));
    }

    // ---------------------------------------------------------------------
    //  Tab III — Kronologi
    // ---------------------------------------------------------------------

    /** Fase penanganan insiden (ISO/IEC 27035 & NIST SP 800-61). */
    public const FASE = [
        'serangan'      => 'Serangan',
        'deteksi'       => 'Deteksi',
        'pelaporan'     => 'Pelaporan',
        'analisis'      => 'Analisis',
        'penanggulangan'=> 'Penanggulangan',
        'pemberantasan' => 'Pemberantasan',
        'pemulihan'     => 'Pemulihan',
        'pascainsiden'  => 'Pascainsiden',
        'penutupan'     => 'Penutupan',
    ];

    public const MAKS_KRONOLOGI = 80;

    public const JENIS_LAPORAN = [
        'awal'        => 'Laporan Awal',
        'perkembangan' => 'Laporan Perkembangan',
        'akhir'       => 'Laporan Akhir (Pascainsiden)',
    ];

    public const STATUS = [
        'ditangani' => 'Dalam Penanganan',
        'pulih'     => 'Layanan Pulih (Pemantauan)',
        'ditutup'   => 'Ditutup',
    ];

    public const SUMBER_DETEKSI = [
        'masyarakat' => 'Laporan masyarakat / pengguna layanan',
        'pegawai'    => 'Laporan pegawai / operator sistem',
        'pemantauan' => 'Pemantauan internal (SOC / SIEM / monitoring)',
        'csirt'      => 'Notifikasi BSSN / CSIRT lain',
        'pihak3'     => 'Pihak ketiga / vendor',
        'lainnya'    => 'Lainnya',
    ];

    // ---------------------------------------------------------------------
    //  Tab II — Klasifikasi & Dampak
    // ---------------------------------------------------------------------

    public const KATEGORI = [
        'defacement'     => 'Perubahan tampilan situs (defacement)',
        'konten_ilegal'  => 'Penyisipan konten ilegal (judi online / SEO spam)',
        'akses_tidak_sah'=> 'Akses tidak sah / pengambilalihan akun',
        'malware'        => 'Malware / ransomware',
        'kebocoran'      => 'Kebocoran / pencurian data',
        'dos'            => 'Gangguan layanan (DoS / DDoS)',
        'phishing'       => 'Phishing / rekayasa sosial',
        'eksploitasi'    => 'Eksploitasi kerentanan aplikasi / sistem',
        'lainnya'        => 'Lainnya',
    ];

    public const VEKTOR = [
        'kredensial'   => 'Kredensial lemah, bawaan, atau bocor',
        'kerentanan'   => 'Kerentanan perangkat lunak (belum ditambal)',
        'konfigurasi'  => 'Kesalahan konfigurasi',
        'phishing'     => 'Phishing / rekayasa sosial',
        'malware'      => 'Malware / unduhan berbahaya',
        'orang_dalam'  => 'Orang dalam / penyalahgunaan hak akses',
        'pihak3'       => 'Pihak ketiga / rantai pasok',
        'belum'        => 'Belum diketahui (masih diselidiki)',
        'lainnya'      => 'Lainnya',
    ];

    public const TINGKAT_DAMPAK = [
        0 => 'Tidak terdampak',
        1 => 'Rendah',
        2 => 'Sedang',
        3 => 'Tinggi',
    ];

    /** Dimensi dampak beserta panduan tingkat Rendah / Sedang / Tinggi. */
    public const DIMENSI = [
        'kerahasiaan' => [
            'nama'    => 'Kerahasiaan (C)',
            'panduan' => 'Rendah: hanya informasi publik yang terlihat · Sedang: informasi internal/terbatas terakses · Tinggi: data pribadi atau informasi rahasia bocor',
        ],
        'integritas' => [
            'nama'    => 'Integritas (I)',
            'panduan' => 'Rendah: perubahan kecil tidak terlihat publik · Sedang: data/konten diubah terbatas · Tinggi: konten resmi dipalsukan atau data penting rusak',
        ],
        'ketersediaan' => [
            'nama'    => 'Ketersediaan (A)',
            'panduan' => 'Rendah: gangguan < 1 jam · Sedang: layanan terganggu beberapa jam · Tinggi: layanan lumpuh ≥ 1 hari kerja atau layanan vital terhenti',
        ],
        'reputasi' => [
            'nama'    => 'Reputasi',
            'panduan' => 'Rendah: hanya diketahui internal · Sedang: diketahui sebagian pengguna · Tinggi: tersebar di media sosial/media massa',
        ],
        'cakupan' => [
            'nama'    => 'Cakupan',
            'panduan' => 'Rendah: 1 sistem/aset · Sedang: beberapa sistem dalam satu instansi · Tinggi: lintas instansi atau meluas',
        ],
    ];

    public const DATA_PRIBADI = [
        'tidak'  => 'Tidak ada bukti data pribadi terdampak',
        'diduga' => 'Diduga terdampak (masih diselidiki)',
        'ya'     => 'Terkonfirmasi terdampak (kegagalan pelindungan data pribadi)',
    ];

    public const NOTIFIKASI_PDP = [
        'tidak_perlu' => 'Tidak diperlukan',
        'menunggu'    => 'Menunggu hasil investigasi',
        'disiapkan'   => 'Sedang disiapkan (batas 3 × 24 jam)',
        'sudah'       => 'Sudah disampaikan',
    ];

    public const KEPARAHAN = [
        1 => ['nama' => 'Rendah', 'kriteria' => 'Gangguan kecil, tidak terlihat publik, tidak ada data terdampak'],
        2 => ['nama' => 'Sedang', 'kriteria' => 'Layanan publik terganggu atau reputasi terdampak, tanpa kebocoran data'],
        3 => ['nama' => 'Tinggi', 'kriteria' => 'Data pribadi/terbatas bocor atau diakses, atau beberapa sistem terdampak'],
        4 => ['nama' => 'Kritis', 'kriteria' => 'Layanan vital lumpuh, kebocoran data masif, atau dampak lintas instansi'],
    ];

    /**
     * Saran tingkat keparahan dari penilaian dampak + status data pribadi.
     * Contoh deface: C1 I3 A2 Reputasi3 Cakupan1, tanpa data pribadi → 2 (Sedang).
     */
    public static function saranKeparahan(array $dampak, ?string $dataPribadi): int
    {
        $t = fn (string $k) => (int) ($dampak[$k]['tingkat'] ?? 0);
        [$c, $i, $a, $r, $k] = [$t('kerahasiaan'), $t('integritas'), $t('ketersediaan'), $t('reputasi'), $t('cakupan')];

        if (($c >= 3 && $dataPribadi === 'ya') || ($a >= 3 && $k >= 3)) return 4;
        if ($c >= 2 || $dataPribadi === 'ya' || $k >= 2) return 3;
        if ($i >= 2 || $a >= 2 || $r >= 2) return 2;

        return 1;
    }

    /** Urutan waktu insiden: setiap waktu tidak boleh mendahului waktu sebelumnya. */
    public const WAKTU = [
        'waktu_kejadian' => 'Waktu Kejadian',
        'waktu_deteksi'  => 'Waktu Terdeteksi',
        'waktu_lapor'    => 'Waktu Dilaporkan ke Tim',
        'waktu_pulih'    => 'Waktu Layanan Pulih',
        'waktu_tutup'    => 'Waktu Insiden Ditutup',
    ];

    public const FORMAT_WAKTU = 'Y-m-d\TH:i';

    /** Lampiran laporan. Dipilih (dicentang) saat cetak; yang tidak dicentang dicetak dicoret. */
    public const LAMPIRAN = [
        'a' => 'Daftar Bukti (Chain of Custody)',
        'b' => 'Bukti Dukung',
        'c' => 'Surat Perintah Tugas',
        'd' => 'Non-Disclosure Agreement (Pernyataan Menjaga Kerahasiaan)',
    ];

    // =====================================================================
    //  Status tab
    // =====================================================================

    /**
     * Tab yang wajib menurut jenis laporan:
     * Awal → I–III · Perkembangan → I–V (isi analisis bersifat sementara) · Akhir → I–VI.
     */
    public static function tabsAktif(array $insiden): array
    {
        $jenis = $insiden['informasi']['jenis_laporan'] ?? null;

        return match ($jenis) {
            'awal'         => array_diff_key(self::TABS, array_flip([...self::TAB_LANJUTAN, ...self::TAB_AKHIR])),
            'perkembangan' => array_diff_key(self::TABS, array_flip(self::TAB_AKHIR)),
            default        => self::TABS,
        };
    }

    public static function laporanAkhir(array $insiden): bool
    {
        return ($insiden['informasi']['jenis_laporan'] ?? null) === 'akhir';
    }

    /** Label tab yang belum pernah disimpan. */
    public static function tabBelumDisimpan(array $insiden): array
    {
        $belum = array_diff_key(self::tabsAktif($insiden), $insiden);

        // Jenis laporan diubah menjadi Akhir setelah Tab IV disimpan tanpa akar masalah → Tab IV perlu dilengkapi
        if (self::laporanAkhir($insiden) && isset($insiden['analisis'])
            && (blank($insiden['analisis']['akar_masalah'] ?? null) || empty($insiden['analisis']['whys']))) {
            $belum['analisis'] = self::TABS['analisis'] . ' (akar masalah wajib untuk Laporan Akhir)';
        }

        if (self::laporanAkhir($insiden) && isset($insiden['penanganan'])
            && (blank($insiden['penanganan']['pemberantasan'] ?? null) || blank($insiden['penanganan']['pemulihan'] ?? null))) {
            $belum['penanganan'] = self::TABS['penanganan'] . ' (pemberantasan & pemulihan wajib untuk Laporan Akhir)';
        }

        return array_values($belum);
    }

    public static function lengkap(array $insiden): bool
    {
        return self::tabBelumDisimpan($insiden) === [];
    }

    // =====================================================================
    //  Bantuan waktu (dipakai form & PDF)
    // =====================================================================

    public static function waktu(?string $nilai): ?Carbon
    {
        if (blank($nilai)) return null;

        try {
            return Carbon::createFromFormat('!' . self::FORMAT_WAKTU, $nilai, config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }

    /** Contoh: 14 Oktober 2026, 07.42 WITA */
    public static function tampilWaktu(?string $nilai): string
    {
        $w = self::waktu($nilai);

        return $w ? $w->locale('id')->translatedFormat('d F Y, H.i') . ' WITA' : '-';
    }

    /** Selisih dua waktu dalam teks, mis. "3 jam 38 menit". */
    public static function durasi(?string $dari, ?string $sampai): ?string
    {
        $a = self::waktu($dari);
        $b = self::waktu($sampai);
        if (! $a || ! $b || $b->lt($a)) return null;

        $menit = (int) $a->diffInMinutes($b);
        $hari  = intdiv($menit, 1440);
        $jam   = intdiv($menit % 1440, 60);
        $sisa  = $menit % 60;

        $bagian = array_filter([
            $hari ? "$hari hari" : null,
            $jam ? "$jam jam" : null,
            $sisa ? "$sisa menit" : null,
        ]);

        return $bagian ? implode(' ', $bagian) : '0 menit';
    }

    /** Ukuran waktu baku penanganan insiden, dihitung dari Tab I. */
    public static function ukuranWaktu(array $inf): array
    {
        return array_filter([
            'Waktu tinggal penyerang sebelum terdeteksi (dwell time)' => self::durasi($inf['waktu_kejadian'] ?? null, $inf['waktu_deteksi'] ?? null),
            'Waktu deteksi hingga dilaporkan ke tim'                    => self::durasi($inf['waktu_deteksi'] ?? null, $inf['waktu_lapor'] ?? null),
            'Waktu pemulihan (terdeteksi hingga layanan pulih)'         => self::durasi($inf['waktu_deteksi'] ?? null, $inf['waktu_pulih'] ?? null),
        ]);
    }

    // =====================================================================
    //  Form & simpan
    // =====================================================================

    public function form(Request $request)
    {
        $insiden = session('insiden', []);
        $tabs    = self::tabsAktif($insiden);
        $tab     = $request->query('tab', array_key_first($tabs));
        abort_unless(array_key_exists($tab, $tabs), 404);

        return view('insiden.form', [
            'tabs'    => $tabs,
            'tab'     => $tab,
            'insiden' => $insiden,
        ]);
    }

    public function save(Request $request, string $tab)
    {
        abort_unless(array_key_exists($tab, self::tabsAktif(session('insiden', []))), 404);

        $data = $this->dataTab($tab, $request);

        $insiden               = session('insiden', []);
        $insiden[$tab]         = $data;
        $insiden['nomor']      = $insiden['informasi']['nomor_laporan'] ?? null;
        $insiden['diperbarui'] = now()->toIso8601String();
        session(['insiden' => $insiden]);

        // Lanjut otomatis ke tab berikutnya; tab terakhir tetap di tempat
        $urutan = array_keys(self::tabsAktif($insiden));
        $next   = $urutan[array_search($tab, $urutan, true) + 1] ?? $tab;

        $pesan = self::TABS[$tab] . ' tersimpan.'
            . ($next !== $tab ? ' Silakan lanjutkan ' . self::TABS[$next] . '.' : '');

        return redirect()->route('insiden.form', ['tab' => $next])->with('saved', $pesan);
    }

    /**
     * Bersihkan + validasi isian satu tab. Dipakai oleh tombol Simpan dan oleh Impor JSON,
     * sehingga keduanya memakai aturan yang sama. Melempar ValidationException bila gagal.
     */
    public function dataTab(string $tab, Request $request): array
    {
        abort_unless(array_key_exists($tab, self::TABS), 404);

        if ($tab === 'informasi') {
            foreach (['nomor_laporan', 'nomor_tiket', 'kode_aset'] as $f) {
                if ($request->filled($f)) {
                    $request->merge([$f => strtoupper(preg_replace('/\s+/', '', (string) $request->input($f)))]);
                }
            }
        }

        if ($tab === 'pelajaran') {
            $request->merge(['tindak_lanjut' => self::buangBarisKosong($request->input('tindak_lanjut'))]);
        }

        if ($tab === 'penanganan') {
            $bukti = self::buangBarisKosong($request->input('bukti'));
            foreach ($bukti as &$b) {
                if (filled($b['hash'] ?? null)) $b['hash'] = strtolower(preg_replace('/\s+/', '', (string) $b['hash']));
            }
            unset($b);
            $request->merge(['bukti' => $bukti]);
        }

        if ($tab === 'analisis') {
            $request->merge([
                'rantai' => self::buangBarisKosong($request->input('rantai')),
                'ioc'    => self::buangBarisKosong($request->input('ioc')),
                'whys'   => self::buangBarisKosong($request->input('whys')),
            ]);
        }

        $v = Validator::make($request->all(), $this->rules($tab), $this->messages(), $this->attributes());

        $v->after(function ($v) use ($request, $tab) {
            if ($tab === 'pelajaran') {
                $terisi = collect((array) $request->input('evaluasi', []))
                    ->contains(fn ($e) => is_array($e) && (filled($e['baik'] ?? null) || filled($e['perbaikan'] ?? null)));
                if (! $terisi) $v->errors()->add('evaluasi', 'Isi evaluasi minimal pada satu aspek.');
                return;
            }
            if ($tab === 'penanganan') {
                foreach ((array) $request->input('bukti', []) as $i => $row) {
                    $w = self::waktu(is_array($row) ? ($row['waktu'] ?? null) : null);
                    if ($w && $w->gt(now()->addMinutes(5))) {
                        $v->errors()->add("bukti.$i.waktu", 'Waktu tidak boleh melewati waktu sekarang.');
                    }
                }
                return;
            }
            if ($tab === 'kronologi') {
                foreach ((array) $request->input('kronologi', []) as $i => $row) {
                    $w = self::waktu(is_array($row) ? ($row['waktu'] ?? null) : null);
                    if ($w && $w->gt(now()->addMinutes(5))) {
                        $v->errors()->add("kronologi.$i.waktu", 'Waktu tidak boleh melewati waktu sekarang.');
                    }
                }
                return;
            }
            if ($tab === 'klasifikasi') {
                $this->periksaKlasifikasi($v, $request);
                return;
            }
            if ($tab !== 'informasi') return;

            // Laporan Akhir ⇔ status Ditutup. Laporan Awal/Perkembangan: insiden masih ditangani atau baru pulih.
            $jenis  = $request->input('jenis_laporan');
            $status = $request->input('status');
            if ($jenis === 'akhir' && $status !== 'ditutup') {
                $v->errors()->add('status', 'Laporan Akhir hanya dapat dibuat untuk insiden berstatus Ditutup.');
            }
            if (in_array($jenis, ['awal', 'perkembangan'], true) && $status === 'ditutup') {
                $v->errors()->add('status', 'Insiden berstatus Ditutup dilaporkan dengan Laporan Akhir.');
            }

            // Urutan waktu: kejadian ≤ terdeteksi ≤ dilaporkan ≤ pulih ≤ ditutup
            $sebelum = null;
            foreach (self::WAKTU as $f => $nama) {
                $w = self::waktu($request->input($f));
                if (! $w) continue;
                if ($sebelum && $w->lt($sebelum[0])) {
                    $v->errors()->add($f, "{$nama} tidak boleh lebih awal dari {$sebelum[1]}.");
                }
                $sebelum = [$w, $nama];
            }

            // Waktu insiden tidak boleh di masa depan
            foreach (self::WAKTU as $f => $nama) {
                $w = self::waktu($request->input($f));
                if ($w && $w->gt(now()->addMinutes(5))) {
                    $v->errors()->add($f, "{$nama} tidak boleh melewati waktu sekarang.");
                }
            }
        });

        $data = $v->validate();

        if ($tab === 'informasi') {
            // Waktu tutup hanya relevan untuk status Ditutup
            if (($data['status'] ?? null) !== 'ditutup') $data['waktu_tutup'] = null;
            if (($data['sumber_deteksi'] ?? null) !== 'lainnya') $data['sumber_lainnya'] = null;
        }

        if ($tab === 'pelajaran') {
            $evaluasi = [];
            foreach (array_keys(self::ASPEK) as $a) {
                $evaluasi[$a] = [
                    'baik'      => $data['evaluasi'][$a]['baik'] ?? null,
                    'perbaikan' => $data['evaluasi'][$a]['perbaikan'] ?? null,
                ];
            }
            $data['evaluasi'] = $evaluasi;
            $data['tindak_lanjut'] = array_values(array_map(fn ($r) => [
                'tindakan' => $r['tindakan'],
                'kontrol'  => $r['kontrol'] ?? null,
                'pj'       => $r['pj'],
                'target'   => $r['target'],
                'status'   => $r['status'],
            ], $data['tindak_lanjut']));
        }

        if ($tab === 'penanganan') {
            $data['bukti'] = array_values(array_map(fn ($r) => [
                'nama'     => $r['nama'],
                'waktu'    => $r['waktu'],
                'pengambil'=> $r['pengambil'],
                'lokasi'   => $r['lokasi'],
                'hash'     => $r['hash'] ?? null,
            ], $data['bukti'] ?? []));
        }

        if ($tab === 'analisis') {
            $ambil = fn (array $rows, array $kolom) => array_values(array_map(
                fn ($r) => collect($kolom)->mapWithKeys(fn ($k) => [$k => $r[$k] ?? null])->all(), $rows));
            $data['rantai'] = $ambil($data['rantai'] ?? [], ['taktik', 'teknik', 'temuan']);
            $data['ioc']    = $ambil($data['ioc'] ?? [], ['jenis', 'nilai', 'keterangan']);
            $data['whys']   = $ambil($data['whys'] ?? [], ['tanya', 'jawab']);
        }

        if ($tab === 'kronologi') {
            // Urut menurut waktu; kejadian pada waktu yang sama tetap dalam urutan isian
            $baris = array_values(array_map(fn ($r) => [
                'waktu'    => $r['waktu'],
                'fase'     => $r['fase'],
                'kejadian' => $r['kejadian'],
                'bukti'    => $r['bukti'] ?? null,
            ], $data['kronologi']));
            $urut = array_keys($baris);
            usort($urut, fn ($a, $b) => [$baris[$a]['waktu'], $a] <=> [$baris[$b]['waktu'], $b]);
            $data['kronologi'] = array_map(fn ($i) => $baris[$i], $urut);
        }

        if ($tab === 'klasifikasi') {
            if ($data['kategori'] !== 'lainnya') $data['kategori_lainnya'] = null;
            if ($data['vektor'] !== 'lainnya')   $data['vektor_lainnya'] = null;

            // Urutan dimensi selalu baku, tingkat disimpan sebagai angka
            $dampak = [];
            foreach (array_keys(self::DIMENSI) as $dim) {
                $dampak[$dim] = [
                    'tingkat'    => (int) $data['dampak'][$dim]['tingkat'],
                    'keterangan' => $data['dampak'][$dim]['keterangan'],
                ];
            }
            $data['dampak'] = $dampak;

            if ($data['data_pribadi'] === 'tidak') {
                $data['jenis_data'] = $data['jumlah_subjek'] = null;
                $data['notifikasi'] = 'tidak_perlu';
            }
            if ($data['notifikasi'] !== 'sudah') $data['tanggal_notifikasi'] = null;

            $data['keparahan'] = (int) $data['keparahan'];
            $data['saran']     = self::saranKeparahan($dampak, $data['data_pribadi']);
            if ($data['keparahan'] === $data['saran']) $data['alasan_keparahan'] = null;
        }

        return $data;
    }

    /** Aturan lintas field Tab II: notifikasi PDP & alasan bila keparahan berbeda dari saran. */
    private function periksaKlasifikasi($v, Request $request): void
    {
        if ($v->errors()->hasAny(['data_pribadi', 'notifikasi', 'keparahan']) || $v->errors()->has('dampak.*')) return;

        $dp    = $request->input('data_pribadi');
        $notif = $request->input('notifikasi');

        // UU 27/2022 Pasal 46: kegagalan pelindungan data pribadi wajib diberitahukan paling lambat 3 × 24 jam
        if ($dp === 'ya' && ! in_array($notif, ['disiapkan', 'sudah'], true)) {
            $v->errors()->add('notifikasi', 'Data pribadi terkonfirmasi terdampak: pemberitahuan kepada subjek data dan lembaga wajib disampaikan paling lambat 3 × 24 jam (UU 27/2022 Pasal 46).');
        }
        if ($dp === 'diduga' && $notif === 'tidak_perlu') {
            $v->errors()->add('notifikasi', 'Selama dugaan belum terjawab, pilih "Menunggu hasil investigasi" atau siapkan pemberitahuan.');
        }

        $saran = self::saranKeparahan((array) $request->input('dampak', []), $dp);
        if ((int) $request->input('keparahan') !== $saran && blank($request->input('alasan_keparahan'))) {
            $v->errors()->add('alasan_keparahan', 'Tingkat keparahan berbeda dari saran (' . self::KEPARAHAN[$saran]['nama'] . '). Jelaskan alasannya.');
        }
    }

    // =====================================================================
    //  Export PDF
    // =====================================================================

    public function pdf(Request $request)
    {
        $insiden = session('insiden', []);

        if (! self::lengkap($insiden)) {
            return redirect()->route('insiden.form')
                ->withErrors(['insiden' => 'Lengkapi dan simpan seluruh tab terlebih dahulu sebelum export PDF.']);
        }

        // Lokasi, tanggal & pilihan TTE: hanya untuk cetakan ini, tidak disimpan
        $v = Validator::make($request->only('lokasi', 'tanggal', 'tte', 'lampiran'), [
            'lokasi'  => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'tte'     => ['nullable', 'array'],
            'tte.*'   => [Rule::in(['ketua', 'koordinator'])],
            'lampiran'   => ['nullable', 'array'],
            'lampiran.*' => [Rule::in(array_keys(self::LAMPIRAN))],
        ]);

        $kembali = fn (string $pesan) => redirect()
            ->route('insiden.form', ['tab' => array_key_last(self::tabsAktif($insiden))])
            ->withErrors(['insiden' => $pesan]);

        if ($v->fails()) {
            return $kembali('Lokasi dan tanggal laporan wajib diisi dengan benar sebelum mencetak PDF.');
        }

        $input = $v->validated();
        $tte   = array_values(array_unique($input['tte'] ?? []));

        [$label, $galat] = LabelTte::ambil($request, $tte);
        if ($galat) return $kembali($galat);

        $penetapan = [
            'lokasi'  => $input['lokasi'],
            'tanggal' => Carbon::createFromFormat('!Y-m-d', $input['tanggal'])->locale('id')->translatedFormat('d F Y'),
            'tte'      => $tte,
            'lampiran' => array_values(array_intersect(array_keys(self::LAMPIRAN), $input['lampiran'] ?? [])),
            'label'   => $label,
        ];

        $pdf = Pdf::loadView('insiden.pdf', [
            'insiden'   => $insiden,
            'penetapan' => $penetapan,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled'     => false,
                'isPhpEnabled'        => false,
                'isJavascriptEnabled' => false,
                'defaultFont'         => 'Helvetica',
            ]);

        PdfFooter::tambah($pdf, 'TERBATAS — Laporan Penanganan Insiden ' . $insiden['nomor']);

        return $pdf->download('Insiden-' . self::namaBerkas($insiden['nomor']) . '.pdf')
            ->header('Cache-Control', 'no-store, private');
    }

    /** Nomor laporan → aman untuk nama file (LPI/2026/10/001 → LPI-2026-10-001). */
    public static function namaBerkas(?string $nomor): string
    {
        $aman = trim(preg_replace('/[^A-Za-z0-9.-]+/', '-', (string) $nomor), '-');

        return $aman !== '' ? $aman : 'laporan';
    }

    // =====================================================================
    //  Validasi
    // =====================================================================

    private function rules(string $tab): array
    {
        $waktu = ['date_format:' . self::FORMAT_WAKTU];

        return match ($tab) {
            'informasi' => [
                // A. Identitas laporan
                'nomor_laporan'    => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9][A-Z0-9\/._-]*$/'],
                'nomor_tiket'      => ['nullable', 'string', 'max:50', 'regex:/^[A-Z0-9][A-Z0-9\/._-]*$/'],
                'judul'            => ['required', 'string', 'max:255'],
                'jenis_laporan'    => ['required', Rule::in(array_keys(self::JENIS_LAPORAN))],
                'status'           => ['required', Rule::in(array_keys(self::STATUS))],

                // B. Aset terdampak & pemilik
                'nama_aset'        => ['required', 'string', 'max:255'],
                'kode_aset'        => ['nullable', 'regex:/^(DI|PL|PK|SP|PS)-\d{6}$/', 'not_regex:/-0{6}$/'],
                'lokasi_aset'      => ['required', 'string', 'max:1000'],
                'pemilik_aset'     => ['required', 'string', 'max:255'],
                'pimpinan_nama'    => ['nullable', 'string', 'max:255'],
                'pimpinan_jabatan' => ['nullable', 'string', 'max:255'],

                // C. Waktu insiden
                'waktu_kejadian'   => ['required', ...$waktu],
                'waktu_deteksi'    => ['required', ...$waktu],
                'waktu_lapor'      => ['required', ...$waktu],
                'waktu_pulih'      => ['nullable', 'required_if:status,pulih,ditutup', ...$waktu],
                'waktu_tutup'      => ['nullable', 'required_if:status,ditutup', ...$waktu],

                // D. Deteksi & pelaporan
                'sumber_deteksi'   => ['required', Rule::in(array_keys(self::SUMBER_DETEKSI))],
                'sumber_lainnya'   => ['nullable', 'required_if:sumber_deteksi,lainnya', 'string', 'max:255'],
                'pelapor'          => ['required', 'string', 'max:255'],
                'pihak_diberitahu' => ['nullable', 'string', 'max:1000'],

                // E. Tim penanganan
                'tim_nama'         => ['required', 'string', 'max:255'],
                'ketua_nama'       => ['required', 'string', 'max:255'],
                'ketua_jabatan'    => ['required', 'string', 'max:255'],
                'ketua_instansi'   => ['required', 'string', 'max:255'],
                'koordinator_nama'    => ['required', 'string', 'max:255'],
                'koordinator_jabatan' => ['required', 'string', 'max:255'],

                // F. Ringkasan
                'ringkasan'        => ['required', 'string', 'max:3000'],
            ],

            'pelajaran' => array_merge([
                'evaluasi'                => ['required', 'array'],
                'tindak_lanjut'           => ['required', 'array', 'min:1', 'max:' . self::MAKS_BARIS],
                'tindak_lanjut.*.tindakan'=> ['required', 'string', 'max:1000'],
                'tindak_lanjut.*.kontrol' => ['nullable', 'string', 'max:255'],
                'tindak_lanjut.*.pj'      => ['required', 'string', 'max:255'],
                'tindak_lanjut.*.target'  => ['required', 'date_format:Y-m-d'],
                'tindak_lanjut.*.status'  => ['required', Rule::in(array_keys(self::STATUS_TL))],
                'rekomendasi'             => ['required', 'string', 'max:2000'],
                'tembusan'                => ['nullable', 'string', 'max:1000'],
            ], ...array_map(fn ($a) => [
                "evaluasi.$a.baik"      => ['nullable', 'string', 'max:1000'],
                "evaluasi.$a.perbaikan" => ['nullable', 'string', 'max:1000'],
            ], array_keys(self::ASPEK))),

            // Laporan Akhir: seluruh fase wajib; Perkembangan: cukup penanggulangan
            'penanganan' => [
                'penanggulangan'   => ['required', 'string', 'max:3000'],
                'catatan_bukti'    => ['nullable', 'string', 'max:1000'],
                'bukti'            => ['nullable', 'array', 'max:' . self::MAKS_BUKTI],
                'bukti.*.nama'     => ['required', 'string', 'max:255'],
                'bukti.*.waktu'    => ['required', 'date_format:' . self::FORMAT_WAKTU],
                'bukti.*.pengambil'=> ['required', 'string', 'max:255'],
                'bukti.*.lokasi'   => ['required', 'string', 'max:255'],
                'bukti.*.hash'     => ['nullable', 'regex:/^[a-f0-9]{64}$/'],
                'pemberantasan'    => [$this->akhirDiminta() ? 'required' : 'nullable', 'string', 'max:3000'],
                'pemulihan'        => [$this->akhirDiminta() ? 'required' : 'nullable', 'string', 'max:3000'],
            ],

            'analisis' => [
                'ringkasan_teknis'  => ['required', 'string', 'max:3000'],

                'rantai'            => ['nullable', 'array', 'max:' . self::MAKS_BARIS],
                'rantai.*.taktik'   => ['required', Rule::in(array_keys(self::TAKTIK))],
                'rantai.*.teknik'   => ['nullable', 'string', 'max:255'],
                'rantai.*.temuan'   => ['required', 'string', 'max:1000'],

                'ioc'               => ['nullable', 'array', 'max:' . self::MAKS_BARIS],
                'ioc.*.jenis'       => ['required', Rule::in(array_keys(self::JENIS_IOC))],
                'ioc.*.nilai'       => ['required', 'string', 'max:500'],
                'ioc.*.keterangan'  => ['nullable', 'string', 'max:500'],
                'ioc_dibagikan'     => ['nullable', 'string', 'max:1000'],

                // Laporan Akhir wajib menyimpulkan akar masalah; Perkembangan boleh sementara/kosong
                'whys'              => [$this->akhirDiminta() ? 'required' : 'nullable', 'array', 'max:' . self::JUMLAH_WHY],
                'whys.*.tanya'      => ['required', 'string', 'max:500'],
                'whys.*.jawab'      => ['required', 'string', 'max:1000'],
                'akar_masalah'      => [$this->akhirDiminta() ? 'required' : 'nullable', 'string', 'max:2000'],
                'kontrol_terkait'   => ['nullable', 'string', 'max:500'],
            ],

            'kronologi' => [
                'kronologi'            => ['required', 'array', 'min:1', 'max:' . self::MAKS_KRONOLOGI],
                'kronologi.*'          => ['array'],
                'kronologi.*.waktu'    => ['required', 'date_format:' . self::FORMAT_WAKTU],
                'kronologi.*.fase'     => ['required', Rule::in(array_keys(self::FASE))],
                'kronologi.*.kejadian' => ['required', 'string', 'max:1000'],
                'kronologi.*.bukti'    => ['nullable', 'string', 'max:255'],
            ],

            'klasifikasi' => array_merge([
                'kategori'           => ['required', Rule::in(array_keys(self::KATEGORI))],
                'kategori_lainnya'   => ['nullable', 'required_if:kategori,lainnya', 'string', 'max:255'],
                'vektor'             => ['required', Rule::in(array_keys(self::VEKTOR))],
                'vektor_lainnya'     => ['nullable', 'required_if:vektor,lainnya', 'string', 'max:255'],
                'dampak'             => ['required', 'array'],

                'data_pribadi'       => ['required', Rule::in(array_keys(self::DATA_PRIBADI))],
                'jenis_data'         => ['nullable', 'required_if:data_pribadi,diduga,ya', 'string', 'max:1000'],
                'jumlah_subjek'      => ['nullable', 'string', 'max:255'],
                'notifikasi'         => ['nullable', 'required_if:data_pribadi,diduga,ya', Rule::in(array_keys(self::NOTIFIKASI_PDP))],
                'tanggal_notifikasi' => ['nullable', 'required_if:notifikasi,sudah', 'date_format:Y-m-d'],

                'keparahan'          => ['required', Rule::in(array_keys(self::KEPARAHAN))],
                'alasan_keparahan'   => ['nullable', 'string', 'max:1000'],
            ], ...array_map(fn ($dim) => [
                "dampak.$dim.tingkat"    => ['required', Rule::in(array_keys(self::TINGKAT_DAMPAK))],
                "dampak.$dim.keterangan" => ['required', 'string', 'max:500'],
            ], array_keys(self::DIMENSI))),
        };
    }

    /** Jenis laporan di sesi adalah Laporan Akhir (dipakai aturan wajib Tab IV ke atas). */
    private function akhirDiminta(): bool
    {
        return self::laporanAkhir(session('insiden', []));
    }

    private function messages(): array
    {
        return [
            'evaluasi.required'               => 'Isi evaluasi minimal pada satu aspek.',
            'tindak_lanjut.required'          => 'Isi minimal satu rencana tindak lanjut.',
            'tindak_lanjut.min'               => 'Isi minimal satu rencana tindak lanjut.',
            'tindak_lanjut.max'               => 'Rencana tindak lanjut maksimal ' . self::MAKS_BARIS . ' baris.',
            'tindak_lanjut.*.tindakan.required' => 'Tindakan wajib diisi.',
            'tindak_lanjut.*.pj.required'     => 'Penanggung jawab wajib diisi.',
            'tindak_lanjut.*.target.required' => 'Target wajib diisi.',
            'tindak_lanjut.*.target.date_format' => 'Format tanggal tidak valid.',
            'tindak_lanjut.*.status.required' => 'Status wajib dipilih.',
            'tindak_lanjut.*.status.in'       => 'Pilihan status tidak valid.',
            'tindak_lanjut.*.tindakan.max'    => 'Maksimal 1000 karakter.',
            'tindak_lanjut.*.kontrol.max'     => 'Maksimal 255 karakter.',
            'tindak_lanjut.*.pj.max'          => 'Maksimal 255 karakter.',
            'evaluasi.*.baik.max'             => 'Maksimal 1000 karakter.',
            'evaluasi.*.perbaikan.max'        => 'Maksimal 1000 karakter.',
            'bukti.*.nama.required'      => 'Nama bukti wajib diisi.',
            'bukti.*.waktu.required'     => 'Waktu pengambilan wajib diisi.',
            'bukti.*.waktu.date_format'  => 'Format waktu tidak valid.',
            'bukti.*.pengambil.required' => 'Pengambil bukti wajib diisi.',
            'bukti.*.lokasi.required'    => 'Lokasi penyimpanan wajib diisi.',
            'bukti.*.hash.regex'         => 'Hash SHA-256 harus 64 karakter heksadesimal (0–9, a–f).',
            'bukti.*.nama.max'           => 'Maksimal 255 karakter.',
            'bukti.*.pengambil.max'      => 'Maksimal 255 karakter.',
            'bukti.*.lokasi.max'         => 'Maksimal 255 karakter.',
            'bukti.max'                  => 'Daftar bukti maksimal ' . self::MAKS_BUKTI . ' baris.',
            'whys.required'          => 'Laporan Akhir: isi minimal satu pasang pertanyaan–jawaban 5 Whys.',
            'rantai.*.taktik.required' => 'Tahap (taktik) wajib dipilih.',
            'rantai.*.taktik.in'     => 'Pilihan tahap tidak valid.',
            'rantai.*.temuan.required' => 'Temuan wajib diisi.',
            'rantai.*.teknik.max'    => 'Teknik maksimal 255 karakter.',
            'rantai.*.temuan.max'    => 'Temuan maksimal 1000 karakter.',
            'ioc.*.jenis.required'   => 'Jenis IoC wajib dipilih.',
            'ioc.*.jenis.in'         => 'Pilihan jenis IoC tidak valid.',
            'ioc.*.nilai.required'   => 'Nilai IoC wajib diisi.',
            'ioc.*.nilai.max'        => 'Nilai IoC maksimal 500 karakter.',
            'ioc.*.keterangan.max'   => 'Keterangan maksimal 500 karakter.',
            'whys.*.tanya.required'  => 'Pertanyaan wajib diisi bila jawabannya diisi.',
            'whys.*.jawab.required'  => 'Jawaban wajib diisi bila pertanyaannya diisi.',
            'rantai.max'             => 'Rantai serangan maksimal ' . self::MAKS_BARIS . ' baris.',
            'ioc.max'                => 'IoC maksimal ' . self::MAKS_BARIS . ' baris.',
            'required'                => ':attribute wajib diisi.',
            'required_if'             => ':attribute wajib diisi.',
            'max'                     => ':attribute maksimal :max karakter.',
            'in'                      => 'Pilihan :attribute tidak valid.',
            'date_format'             => 'Format :attribute tidak valid.',
            'nomor_laporan.regex'     => 'Nomor Laporan hanya boleh berisi huruf, angka, dan tanda / . _ -',
            'nomor_tiket.regex'       => 'Nomor Tiket hanya boleh berisi huruf, angka, dan tanda / . _ -',
            'kode_aset.regex'         => 'Format Kode Aset: kode klasifikasi + 6 digit, contoh PL-000001.',
            'kode_aset.not_regex'     => 'Nomor pada Kode Aset minimal 000001.',
            'waktu_pulih.required_if' => 'Waktu Layanan Pulih wajib diisi bila status Layanan Pulih atau Ditutup.',
            'waktu_tutup.required_if' => 'Waktu Insiden Ditutup wajib diisi bila status Ditutup.',
            'jenis_data.required_if'  => 'Jenis Data Pribadi wajib diisi bila data pribadi diduga atau terkonfirmasi terdampak.',
            'notifikasi.required_if'  => 'Status Pemberitahuan wajib dipilih bila data pribadi diduga atau terkonfirmasi terdampak.',
            'kronologi.required'   => 'Isi minimal satu kejadian pada kronologi.',
            'kronologi.min'        => 'Isi minimal satu kejadian pada kronologi.',
            'kronologi.max'        => 'Kronologi maksimal ' . self::MAKS_KRONOLOGI . ' baris.',
            'kronologi.*.waktu.required'    => 'Waktu wajib diisi.',
            'kronologi.*.waktu.date_format' => 'Format waktu tidak valid.',
            'kronologi.*.fase.required'     => 'Fase wajib dipilih.',
            'kronologi.*.fase.in'           => 'Pilihan fase tidak valid.',
            'kronologi.*.kejadian.required' => 'Kejadian wajib diisi.',
            'kronologi.*.kejadian.max'      => 'Kejadian maksimal 1000 karakter.',
            'kronologi.*.bukti.max'         => 'Sumber bukti maksimal 255 karakter.',
            'tanggal_notifikasi.required_if' => 'Tanggal Pemberitahuan wajib diisi bila pemberitahuan sudah disampaikan.',
        ];
    }

    private function attributes(): array
    {
        return [
            'nomor_laporan'    => 'Nomor Laporan',
            'nomor_tiket'      => 'Nomor Tiket Insiden',
            'judul'            => 'Judul Insiden',
            'jenis_laporan'    => 'Jenis Laporan',
            'status'           => 'Status Insiden',
            'nama_aset'        => 'Nama Aset Terdampak',
            'kode_aset'        => 'Kode Aset',
            'lokasi_aset'      => 'Lokasi & Spesifikasi Aset',
            'pemilik_aset'     => 'Nama Instansi Pemilik Aset',
            'pimpinan_nama'    => 'Nama Pimpinan Instansi Pemilik Aset',
            'pimpinan_jabatan' => 'Jabatan Pimpinan',
            'sumber_deteksi'   => 'Sumber Deteksi',
            'sumber_lainnya'   => 'Sumber Deteksi Lainnya',
            'pelapor'          => 'Pelapor',
            'pihak_diberitahu' => 'Pihak yang Diberi Tahu',
            'tim_nama'         => 'Nama Tim Penanganan Insiden',
            'ketua_nama'       => 'Nama Ketua Tim',
            'ketua_jabatan'    => 'Jabatan Ketua Tim',
            'ketua_instansi'   => 'Instansi Tim',
            'koordinator_nama'    => 'Nama Koordinator Tim',
            'koordinator_jabatan' => 'Jabatan Koordinator Tim',
            'ringkasan'        => 'Ringkasan Insiden',

            'kategori'           => 'Kategori Insiden',
            'kategori_lainnya'   => 'Kategori Lainnya',
            'vektor'             => 'Vektor Serangan',
            'vektor_lainnya'     => 'Vektor Serangan Lainnya',
            'data_pribadi'       => 'Status Data Pribadi',
            'jenis_data'         => 'Jenis Data Pribadi',
            'jumlah_subjek'      => 'Perkiraan Jumlah Subjek Data',
            'notifikasi'         => 'Status Pemberitahuan',
            'tanggal_notifikasi' => 'Tanggal Pemberitahuan',
            'keparahan'          => 'Tingkat Keparahan',
            'ringkasan_teknis'   => 'Ringkasan Analisis Teknis',
            'penanggulangan'     => 'Penanggulangan',
            'rekomendasi'        => 'Rekomendasi Utama',
            'tembusan'           => 'Tembusan',
            'catatan_bukti'      => 'Catatan Pengamanan Bukti',
            'pemberantasan'      => 'Pemberantasan',
            'pemulihan'          => 'Pemulihan',
            'ioc_dibagikan'      => 'Tindak Lanjut IoC',
            'akar_masalah'       => 'Akar Masalah',
            'kontrol_terkait'    => 'Kontrol ISO 27001 Terkait',
            'alasan_keparahan'   => 'Alasan Penetapan Keparahan',
        ] + self::WAKTU + array_merge(...array_map(fn ($dim, $d) => [
            "dampak.$dim.tingkat"    => 'Tingkat dampak ' . $d['nama'],
            "dampak.$dim.keterangan" => 'Keterangan dampak ' . $d['nama'],
        ], array_keys(self::DIMENSI), self::DIMENSI));
    }
}
