{{-- Satu butir uji: pedoman (dari runbook, sekali) + daftar entri hasil (bisa ditambah/dihapus, min 1). --}}
@php
    $id    = $kode . '-' . $no;
    $jenis = \App\Support\RunbookOwasp::jenisMetode($butir['metode']);
    $warnaJenis = ['aktif' => '#1e40af', 'hati' => '#b45309', 'tinjau' => '#6b7280'][$jenis] ?? '#6b7280';
    $entri = $daftarEntri; // array entri tersimpan, minimal satu (disiapkan controller)
@endphp
<div class="bt" data-bt data-bt-kode="{{ $id }}" data-bt-no="{{ $no }}">
    <div class="bt-head">
        <span class="bt-kode">{{ $kode }}.{{ $no }}</span>
        <span class="bt-judul">{{ $butir['judul'] }}</span>
        <span class="bt-metode" style="border-color:{{ $warnaJenis }}; color:{{ $warnaJenis }}">{{ $butir['metode'] }}</span>
    </div>

    <details class="bt-pedoman">
        <summary>Pedoman uji</summary>
        <p class="bt-tujuan"><strong>Tujuan.</strong> {{ $butir['tujuan'] }}</p>
        <div class="bt-pg">
            <div>
                <span class="bt-lbl">Langkah</span>
                <ol>
                    @foreach ($butir['langkah'] as $l) <li>{{ $l }}</li> @endforeach
                </ol>
            </div>
            <div>
                <span class="bt-lbl">Cara menilai (verdict)</span>
                <ul>
                    @foreach ($butir['verdict'] as $v) <li>{{ $v }}</li> @endforeach
                </ul>
            </div>
        </div>
    </details>

    <div class="bt-isi">
        <div class="en-list" data-en-list>
            @foreach ($entri as $j => $row)
                @include('pengujian.tabs._entri', ['no' => $no, 'j' => $j, 'row' => $row])
            @endforeach
        </div>

        <template data-en-template>
            @include('pengujian.tabs._entri', ['no' => $no, 'j' => '__j__', 'row' => []])
        </template>

        <button type="button" class="secondary en-tambah" data-en-tambah>+ Tambah entri</button>
    </div>
</div>
