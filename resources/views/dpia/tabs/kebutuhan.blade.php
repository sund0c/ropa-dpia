@php
    $hasOld  = session()->hasOldInput();
    $jawaban = $hasOld ? (array) old('jawaban', []) : ($d['jawaban'] ?? []);
    $alasan  = $hasOld ? (array) old('alasan', [])  : ($d['alasan'] ?? []);

    // Catatan hanya berdasarkan jawaban yang sudah tersimpan
    $adaTidak = in_array('tidak', (array) ($d['jawaban'] ?? []), true);
@endphp

@if ($adaTidak)
    <div class="alert warn">
        Terdapat jawaban <strong>Tidak</strong>. Artinya kebutuhan atau proporsionalitas pemrosesan belum
        terpenuhi sepenuhnya. Pastikan hal ini ditindaklanjuti dalam rencana mitigasi, atau pertimbangkan
        kembali ruang lingkup pemrosesan (prinsip pembatasan tujuan dan minimisasi data).
    </div>
@endif

@foreach ($options['penilaian'] as $grup)
    <h3 class="sub">{{ $grup['judul'] }}</h3>

    <div class="tabel-wrap">
        <table class="tabel-form">
            <thead>
                <tr>
                    <th>Pertanyaan</th>
                    <th style="width:6.5rem">Ya/Tidak <span class="req">*</span></th>
                    <th style="width:45%">Keterangan <span class="req">*</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($grup['items'] as $key => $pertanyaan)
                    <tr>
                        <td>{{ $pertanyaan }}</td>
                        <td>
                            <div class="pilihan">
                                @foreach ($options['jawaban'] as $val => $label)
                                    <label class="check">
                                        <input type="radio" name="jawaban[{{ $key }}]" value="{{ $val }}"
                                               @checked(($jawaban[$key] ?? null) === $val)>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                            @error("jawaban.$key") <small class="error">{{ $message }}</small> @enderror
                        </td>
                        <td>
                            <textarea name="alasan[{{ $key }}]" rows="3">{{ $alasan[$key] ?? '' }}</textarea>
                            @error("alasan.$key") <small class="error">{{ $message }}</small> @enderror
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endforeach
