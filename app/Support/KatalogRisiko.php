<?php

namespace App\Support;

/**
 * Katalog referensi Identifikasi Risiko (Modul C, Tab III): kategori risiko keamanan SPBE,
 * serta daftar ancaman → kerentanan per klasifikasi aset. Dipakai sebagai SARAN isian;
 * pengguna tetap boleh menulis ancaman/kerentanan sendiri.
 */
class KatalogRisiko
{
    public const KATEGORI = [
        'kontrol_akses'     => 'Penyalahgunaan Kontrol Akses',
        'pencurian_pdp'     => 'Pencurian Data Pribadi',
        'web_defacement'    => 'Insiden Web Defacement',
        'cloud'             => 'Keamanan Cloud Service',
        'infrastruktur'     => 'Keamanan Infrastruktur',
        'aplikasi'          => 'Ketidaksesuaian Pengelolaan Aplikasi',
        'data_terbatas'     => 'Kesalahan Pengelolaan Data dan Informasi Terbatas',
        'sdm'               => 'Kesalahan Pengelolaan SDM',
        'aset'              => 'Kesalahan Pengelolaan Aset',
        'pihak_ketiga'      => 'Kesalahan Pengelolaan oleh Pihak Ketiga',
        'keberlangsungan'   => 'Terganggunya Keberlangsungan Layanan',
        'malware'           => 'Insiden Serangan Malware',
    ];

    /** [klasifikasi aset => [ancaman => [kerentanan, ...]]] */
    public const ANCAMAN = [
        'perangkat_keras' => [
            'Server secara logikal dapat diakses oleh pihak yang tidak berwenang' => [
                'Tidak adanya prosedur yang mengatur pengaksesan server secara logikal sesuai dengan wewenang dan privilege personil',
                'Password tidak pernah diganti secara berkala',
                'Tidak menerapkan sistem keamanan pada server',
                'Belum ada penerapan segmentasi jaringan',
                'VPN belum terintegrasi dengan active directory',
            ],
            'Server secara fisikal dapat diakses oleh pihak yang tidak berwenang' => [
                'Tidak adanya prosedur yang mengatur pengaksesan server secara fisikal sesuai dengan wewenang dan privilege personil',
                'Tidak menerapkan sistem keamanan fisik pada server',
                'Pengawasan akses fisik oleh pihak berwenang kurang optimal',
                'Akses keluar masuk ruang server tidak terkendali dan terdokumentasikan dengan baik',
                'Rak server tidak dikunci',
                'Tidak adanya mekanisme terpusat untuk monitoring penggunaan user dalam mengakses server',
                'Tidak optimalnya manajemen log akses server',
            ],
            'Terjadi peretasan pada server' => [
                'Server dapat disusupi backdoor',
                'Kurangnya security awareness pengguna server',
                'Manajemen password yang buruk',
                'Tidak ada anti malware atau antivirus',
                'OS server tidak up-to-date',
                'Aplikasi yang terdapat pada server tidak up-to-date',
                'Pelaksanaan Vulnerability Assessment tidak dilakukan secara berkala',
                'Firewall tidak diaktifkan',
                'Masih terdapat port terbuka yang tidak digunakan',
            ],
            'Kerusakan pada server' => [
                'Kurangnya mekanisme pemeliharaan terhadap server',
                'Listrik tidak stabil / sering mengalami pemadaman listrik',
            ],
            'Kapasitas server melebihi batas' => [
                'Kurangnya monitoring terhadap kapasitas penggunaan server serta pembaharuan kebutuhan kapastitas server',
            ],
            'Kegagalan fungsi server' => [
                'Kurangnya monitoring, pengujian dan pemeliharaan server',
                'Server kekurangan daya',
                'Perubahan konfigurasi sistem pada server tidak terkelola dengan baik',
                'Tidak adanya proses change management',
                'Kesalahan operasional (pencabutan dan penyambungan kabel) akibat penataan kabel yang tidak rapih',
                'Lingkungan ruang server tidak dimonitor secara berkala (debu, suhu, dan kelembaban)',
            ],
            'PC/ Laptop digunakan oleh pihak yang tidak berwenang' => [
                'Kurangnya security awareness pengguna PC/laptop',
                'Tidak ada pengaturan pembatasan penggunaan PC / laptop',
                'Tidak ada penerapan sistem keamanan pada PC / laptop',
                'Tidak berjalannya prosedur pengamanan yang sudah ada',
                'Pengelolaan pengguna dan password PC & laptop belum dilakukan secara terpadu',
            ],
            'PC / Laptop terkena malicious code' => [
                'Tidak adanya antivirus yang digunakan',
                'Aplikasi dan/atau OS pada PC/laptop tidak up-to-date',
            ],
            'Kerusakan PC/Laptop' => [
                'Kelalaian pengguna PC/laptop',
                'Kurangnya maintenance terhadap PC/laptop',
                'Pengguna dapat melakukan modifikasi konfigurasi dan aplikasi tanpa otorisasi',
            ],
            'Kehilangan PC/Laptop' => [
                'Kelalaian pengguna PC/laptop',
                'Pengamanan fisik PC/Laptop belum diterapkan oleh pengguna',
                'Proses pengembalian aset/informasi bagi personil yang mengundurkan diri atau dimutasikan tidak terkelola dengan baik',
            ],
            'PC/Laptop diretas' => [
                'Kurangnya security awareness',
                'Tidak menerapkan manajemen password yang kuat pada PC/laptop',
                'Tidak ada anti malware atau antivirus',
                'Firewall tidak diaktifkan',
                'Tidak dilakukan update dan/atau patch keamanan',
                'Masih terdapat port terbuka yang tidak digunakan',
                'Menggunakan jaringan publik yang tidak aman',
            ],
            'Kehilangan removable media/storage media' => [
                'Kurangnya security awareness',
                'Removable media tidak terinventarisasi dengan baik',
            ],
        ],
        'perangkat_lunak' => [
            'OS terkena serangan siber' => [
                'Belum ada atau belum diupdate antivirus',
                'Kurangnya kesadaran keamanan informasi personil',
                'OS tidak up-to-date',
            ],
            'OS error / mengalami kerusakan' => [
                'Adanya missconfigurasi saat instalasi dan konfigurasi OS',
                'Terjadi kesalahan pada saat OS digunakan',
            ],
            'OS disisipi malicious code' => [
                'Belum adanya/belum updatenya antivirus',
                'OS tidak up-to-date',
                'Penggunan patch pada OS',
            ],
            'Penyalahgunaan aplikasi oleh pihak yang tidak berwenang' => [
                'Tidak ada dan/atau tidak dilaksanakan kebijakan akses kontrol pengelola IT pada proses otentikasi',
                'Aplikasi yang dibuat tidak sesuai dengan desain perancangan perangkat lunak atau tidak menerapkan metode DevSecOps',
                'Username dan password tersimpan secara otomatis',
                'Masih ditemukan bug pada aplikasi',
                'Manajemen password yang buruk',
                'Username dan password tidak terintegrasi ke Active Directory',
                'Tidak ada pembatasan pada mekanisme login',
                'Pelaksanaan Vulnerability Assessment tidak dilakukan secara berkala',
                'Kurangnya mekanisme pemantauan hak akses',
            ],
            'Terjadi peretasan pada aplikasi' => [
                'Tidak melakukan pengujian keamanan secara berkala',
                'Lemahnya mekanisme kriptografi aplikasi',
                'Masih ditemukan bug pada aplikasi',
                'Data sensitif/password tertulis pada sistem log',
                'Manajemen password yang buruk',
                'Tidak ada anti malware atau antivirus pada device pengguna',
                'Aplikasi tidak update',
                'Kurangnya mekanisme otentikasi',
            ],
            'Modifikasi aplikasi tidak melalui otorisasi' => [
                'Proses perubahan aplikasi tidak terotorisasi',
                'Tidak ada mekanisme otentikasi ataupun tidak diterapkannya two factor authentication',
                'Aplikasi mudah dilakukan reverse engineering',
            ],
            'Kegagalan fungsi pada aplikasi' => [
                'Kurangnya maintenance terhadap aplikasi secara berkala',
                'Kurangnya kontrol perubahan yang efektif',
                'Kesalahan pada penggunaan aplikasi',
                'Implementasi patch yang tidak kompatibel',
                'Adanya miss konfigurasi pada aplikasi',
                'Kurangnya pengujian fungsi aplikasi secara berkala',
                'Tidak melakukan update secara berkala',
            ],
            'Data pada aplikasi tidak tersedia' => [
                'Kurang optimalnya sistem back up pada aplikasi',
            ],
            'Aplikasi tidak sesuai dengan kebutuhan' => [
                'UAT tidak melibatkan end user',
                'Tidak adanya monitoring pengembangan aplikasi oleh Pihak ke-3',
                'Lingkungan pengujian aplikasi tidak sesuai dengan kondisi di lingkungan production',
            ],
            'Adanya backdoor pada aplikasi' => [
                'Tidak ada atau tidak cukup waktu pengujian pada aplikasi',
                'Aplikasi mudah dilakukan reverse engineering',
                'Aplikasi mudah untuk di build ulang',
            ],
            'Adanya backdoor pada OS' => [
                'OS tidak up-to-date',
                'Penggunaan OS yang tidak resmi',
            ],
            'Database penuh / melebihi kapasitas' => [
                'Kurangnya monitoring terhadap kapasitas penggunaan database serta pembaharuan kebutuhan kapastitas database',
            ],
            'Database terkena serangan siber' => [
                'Versi database tidak up-to-date',
                'Kurangnya security awareness',
                'Manajemen password yang buruk pada database',
                'Tidak ada anti malware atau antivirus',
                'Tidak adanya prosedur yang mengatur pengaksesan database sesuai dengan wewenang dan privilege personil',
            ],
        ],
        'data' => [
            'Kebocoran data/informasi diakses oleh pihak yang tidak berwenang' => [
                'Kurangnya prosedur mengenai penetapan hak akses terhadap informasi',
                'Belum adanya kebijakan yang mengatur tentang pengelolaan data/informasi (mulai dari penyimpanan data hingga pemusnahan data)',
                'Belum diterapkannya algoritma kriptografi terhadap informasi/data',
                'Kurangnya Awareness mengenai klasifikasi informasi (informasi rahasia, informasi internal, informasi publik) dan penanganannya ke personil internal maupun eksternal',
            ],
            'Informasi hilang atau corrupt' => [
                'Kurang optimalnya prosedur back up data',
                'Kelalaian personil dalam mengelola informasi / data',
                'Kurangnya awareness mengenai penanganan dan klasifikasi informasi (Informasi rahasia, Informasi internal, informasi publik)',
                'Dokumentasi tidak terpelihara secara memadai (tidak terkendali perubahannya serta tidak pernah ditinjau kesesuaiannya)',
            ],
            'Penyalahgunaan informasi' => [
                'Belum adanya prosedur terkait pembatasan hak akses terhadap informasi',
                'Belum diterapkannya algoritma kriptografi terhadap informasi-informasi sensitif',
            ],
            'Modifikasi informasi oleh pihak yang tidak berwenang' => [
                'Belum adanya prosedur terkait pembatasan hak akses terhadap informasi',
                'Belum diterapkannya sistem algoritma kriptografi terhadap informasi/data',
            ],
        ],
        'sdm' => [
            'Personil melakukan pembocoran informasi' => [
                'Kurangnya security awareness dari personil',
                'Prosedur rekruitmen dan penempatan pegawai tidak melingkupi aspek keamanan informasi',
                'Social Engineering',
                'Tidak adanya evaluasi terhadap risiko keamanan informasi pada pegawai',
            ],
            'Penyalahgunaan wewenang' => [
                'Kurangnya rasa tanggungjawab dan pemahaman tugas personil terkait keamanan informasi sesuai tugas dan perannya masing-masing',
                'Belum adanya kebijakan yang mengatur pembatasan hak akses personil sesuai dengan tugas dan wewenangnya',
            ],
            'Pihak ketiga membocorkan informasi' => [
                'Kurang jelasnya kebijakan dan peraturan yang mengatur pihak ketiga (NDA)',
                'Kurangnya ketentuan yang memadai (mengenai kerahasiaan) dalam kontrak dengan pihak ketiga',
                'Social Engineering',
                'Kurang tepatnya cara screening dalam pemilihan pihak ketiga',
            ],
            'Tidak terdapat personil pengganti dengan kompetensi yang setara' => [
                'Kurangnya sumber daya manusia yang memiliki kompetensi yang setara (di bidang keamanan siber)',
                'Personil absen/cuti untuk jangka waktu yang cukup panjang',
                'Epidemi penyakit',
                'Personil dimutasi / dirotasi / didemosi/ dipromosi / resign',
            ],
            'Personil tidak melakukan tugas secara optimal' => [
                'Kurangnya kontrol terhadap pelaksanaan tugas dan kewajiban personil',
                'Kurangnya kemampuan dan keterampilan yang dimiliki personil untuk suatu pekerjaan',
                'Deskripsi peran dan tanggung jawab masing-masing personil kurang jelas',
                'Beban dan wewenang kerja yang terlalu besar',
                'Kurangnya disiplin personil',
                'Belum adanya peraturan terkait reward and punishment untuk pegawai',
            ],
        ],
        'sarana_penunjang' => [
            'Kehilangan / Kerusakan pada support appliance' => [
                'Kurangnya pemeliharaan printer',
                'Kurangnya security awareness',
                'Akses ruangan tidak dibatasi (pencuri, atau pihak yang tidak berwenang)',
                'Listrik tidak stabil / sering mengalami pemadaman listrik',
            ],
        ],
    ];

    /** Katalog untuk satu klasifikasi; bila klasifikasi belum diketahui, gabungan semua klasifikasi. */
    public static function untuk(?string $klasifikasi): array
    {
        if ($klasifikasi && isset(self::ANCAMAN[$klasifikasi])) {
            return self::ANCAMAN[$klasifikasi];
        }

        $semua = [];
        foreach (self::ANCAMAN as $daftar) {
            foreach ($daftar as $ancaman => $kerentanan) {
                $semua[$ancaman] = array_values(array_unique(array_merge($semua[$ancaman] ?? [], $kerentanan)));
            }
        }

        return $semua;
    }
}
