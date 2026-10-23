@php
    use App\Http\Controllers\InsidenController as IC;

    $jenisLaporan = $insiden['informasi']['jenis_laporan'] ?? 'akhir';

    $baris = session()->hasOldInput()
        ? array_values(array_filter((array) old('kronologi', []), 'is_array'))
        : ($d['kronologi'] ?? []);
    if ($baris === []) $baris = [[]];

    // Contoh kasus deface WordPress: [waktu, fase, kejadian, sumber bukti]
    $tanggap = [
        ['2026-10-14T07:42', 'deteksi',        'Masyarakat melaporkan tampilan situs berubah melalui media sosial Dinas XYZ', 'Tangkapan layar laporan'],
        ['2026-10-14T07:55', 'pelaporan',      'Operator situs menghubungi tim; tiket INC-20261014-001 dibuat', 'Tiket insiden'],
        ['2026-10-14T08:10', 'penanggulangan', 'Situs dialihkan ke halaman pemeliharaan; akses /wp-admin diblokir dari internet', 'Catatan tim'],
        ['2026-10-14T08:25', 'penanggulangan', 'Snapshot VM dan salinan log diambil sebagai bukti (hash SHA-256 dicatat)', 'Formulir lacak bukti'],
        ['2026-10-14T08:40', 'penanggulangan', 'Seluruh sesi WordPress diputus; kata sandi 4 akun dan kredensial basis data diganti', 'Catatan tim'],
    ];
    $serangan = [
        ['2026-10-13T23:05', 'serangan', '1.412 percobaan login ke /wp-login.php dari 3 alamat IP (pola tebak kata sandi umum)', 'access.log Nginx'],
        ['2026-10-14T01:30', 'serangan', 'Enumerasi pengguna melalui /?author=1 dan /wp-json/wp/v2/users memperlihatkan nama pengguna admin', 'access.log Nginx'],
        ['2026-10-14T02:17', 'serangan', 'Login berhasil sebagai admin dari 203.0.113.45', 'access.log, log aktivitas WordPress'],
        ['2026-10-14T02:21', 'serangan', 'Berkas index.php dan header.php tema diganti halaman defacement melalui Theme File Editor', 'Log aktivitas WordPress, stempel waktu berkas'],
        ['2026-10-14T02:26', 'serangan', 'Penyerang mengunggah web shell wp-content/uploads/2026/10/x.php', 'Stempel waktu berkas, access.log'],
        ['2026-10-14T02:29', 'serangan', 'Web shell diakses 4 kali (perintah id, uname -a, ls); tidak ada eskalasi hak akses', 'access.log, error.log PHP'],
    ];
    $pulih = [
        ['2026-10-14T09:30', 'pemberantasan', 'Web shell dihapus; integritas inti WordPress diperiksa (wp core verify-checksums); tema dipasang ulang dari sumber resmi', 'Log perintah'],
        ['2026-10-14T10:45', 'pemulihan',     'Konten dipulihkan dari cadangan 13 Oktober 2026 pukul 00.00 dan diperiksa operator', 'Catatan tim'],
        ['2026-10-14T11:20', 'pemulihan',     'Situs kembali daring; pemantauan intensif diaktifkan', 'Catatan tim'],
    ];
    $penutup = [
        ['2026-10-16T09:00', 'pascainsiden', 'Rapat evaluasi (lessons learned) bersama Dinas XYZ', 'Notula rapat'],
        ['2026-10-21T15:00', 'penutupan',    'Laporan akhir disetujui; insiden dinyatakan ditutup', 'Laporan ini'],
    ];

    $jadikan = fn (array $rows) => array_map(fn ($r) => array_combine(['waktu', 'fase', 'kejadian', 'bukti'], $r), $rows);
    $contoh = [
        'awal'         => $jadikan($tanggap),
        'perkembangan' => $jadikan(array_merge($serangan, $tanggap, $pulih)),
        'akhir'        => $jadikan(array_merge($serangan, $tanggap, $pulih, $penutup)),
    ];
@endphp

<style>
    .contoh-bar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
                  margin-bottom: 1rem; padding: .6rem .9rem; background: #f0f9ff; border: 1px dashed #93c5fd; border-radius: 8px; font-size: .9rem; }
    .contoh-bar button { padding: .4rem .9rem; font-size: .88rem; background: #0369a1; }
    .kr-list { display: flex; flex-direction: column; gap: .6rem; }
    .kr { border: 1px solid #e5e7eb; border-left: 4px solid #93c5fd; border-radius: 8px; padding: .6rem .8rem 0; background: #fff; }
    .kr .field { margin-bottom: .6rem; }
    .kr .field input, .kr .field select, .kr .field textarea { width: 100%; box-sizing: border-box; }
    .kr-grid { display: grid; grid-template-columns: 1.1fr 1fr 1.4fr auto; gap: 0 .75rem; align-items: start; }
    button.kr-hapus { background: transparent; color: #b91c1c; padding: .3rem .5rem; margin-top: 1.4rem; font-size: 1rem; }
    button.kr-hapus:disabled { color: #9ca3af; }
    .kr-tambah { margin-top: .75rem; }
    @media (max-width: 700px) { .kr-grid { grid-template-columns: 1fr 1fr; } button.kr-hapus { margin-top: 0; } }
</style>

<div class="contoh-bar">
    <span>Contoh kronologi kasus <em>defacement</em> WordPress untuk
        <strong>{{ IC::JENIS_LAPORAN[$jenisLaporan] ?? 'Laporan Akhir' }}</strong> (mengikuti Jenis Laporan di Tab I).</span>
    <button type="button" data-isi-contoh>Isi dengan contoh</button>
</div>

<p class="hint" style="margin-top:0">
    Catat setiap kejadian penting, dari aktivitas penyerang (berdasarkan log) hingga penanganan dan penutupan.
    Waktu dalam WITA. Urutan baris tidak perlu diatur: saat disimpan, kronologi diurutkan otomatis menurut waktu.
</p>

@error('kronologi') <div class="alert err">{{ $message }}</div> @enderror

<div id="kronologi">
    <div class="kr-list" data-kr-list>
        @foreach ($baris as $i => $row)
            @include('insiden.tabs._kronologi-row', ['i' => $i, 'row' => $row])
        @endforeach
    </div>

    <template data-kr-template>
        @include('insiden.tabs._kronologi-row', ['i' => '__i__', 'row' => []])
    </template>

    <button type="button" class="secondary kr-tambah" data-kr-tambah>+ Tambah kejadian</button>
</div>

<script>
(function () {
    const root   = document.getElementById('kronologi');
    const list   = root.querySelector('[data-kr-list]');
    const tpl    = root.querySelector('[data-kr-template]');
    const contoh = @json($contoh);
    const jenis  = @json($jenisLaporan);
    const MAKS   = {{ IC::MAKS_KRONOLOGI }};
    let seq = Date.now();

    function tambah(isi) {
        list.insertAdjacentHTML('beforeend', tpl.innerHTML.replaceAll('__i__', String(seq++)));
        const kartu = list.lastElementChild;
        if (isi) {
            for (const [k, v] of Object.entries(isi)) {
                const el = kartu.querySelector(`[name$="[${k}]"]`);
                if (el) el.value = v ?? '';
            }
        }
        return kartu;
    }

    function segarkan() {
        const kartu = list.querySelectorAll('[data-kr]');
        kartu.forEach(k => k.querySelector('[data-kr-hapus]').disabled = kartu.length <= 1);
        root.querySelector('[data-kr-tambah]').disabled = kartu.length >= MAKS;
    }

    root.addEventListener('click', (e) => {
        if (e.target.closest('[data-kr-tambah]')) {
            // Baris baru mewarisi tanggal baris terakhir agar pengisian lebih cepat
            const akhir = [...list.querySelectorAll('[name$="[waktu]"]')].pop()?.value || '';
            const kartu = tambah(akhir ? { waktu: akhir } : null);
            segarkan();
            kartu.querySelector('[name$="[waktu]"]').focus();
            return;
        }
        const hapus = e.target.closest('[data-kr-hapus]');
        if (hapus && list.querySelectorAll('[data-kr]').length > 1) {
            hapus.closest('[data-kr]').remove();
            segarkan();
        }
    });

    document.querySelector('[data-isi-contoh]').addEventListener('click', () => {
        const ada = [...list.querySelectorAll('input, select, textarea')].some(x => x.value.trim() !== '');
        if (ada && !confirm('Kronologi yang sudah ada akan diganti dengan contoh. Lanjutkan?')) return;
        list.innerHTML = '';
        (contoh[jenis] || contoh.akhir).forEach(r => tambah(r));
        segarkan();
    });

    segarkan();
})();
</script>
