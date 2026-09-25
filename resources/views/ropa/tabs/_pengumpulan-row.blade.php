<div class="rep-row" data-row>
    <div class="rep-line pair">
        <span class="rep-no"></span>
        <input type="text" name="pengumpulan[{{ $i }}][sumber]" value="{{ $row['sumber'] ?? '' }}"
               aria-label="Sumber pengumpulan" placeholder="Contoh: Formulir daring di portal layanan">
        <input type="text" name="pengumpulan[{{ $i }}][lokasi]" value="{{ $row['lokasi'] ?? '' }}"
               aria-label="Lokasi penyimpanan" placeholder="Contoh: Server basis data di pusat data instansi">
        <button type="button" class="icon" data-remove aria-label="Hapus baris">✕</button>
    </div>
    @error("pengumpulan.$i.sumber") <small class="error">{{ $message }}</small> @enderror
    @error("pengumpulan.$i.lokasi") <small class="error">{{ $message }}</small> @enderror
</div>
