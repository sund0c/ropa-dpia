<div class="rep-row" data-row>
    <div class="riwayat-line">
        <span class="rep-no"></span>
        <input type="text" name="riwayat[{{ $i }}][versi]" value="{{ $row['versi'] ?? '' }}" aria-label="Versi" placeholder="1.0">
        <input type="date" name="riwayat[{{ $i }}][tanggal]" value="{{ $row['tanggal'] ?? '' }}" aria-label="Tanggal">
        <input type="text" name="riwayat[{{ $i }}][deskripsi]" value="{{ $row['deskripsi'] ?? '' }}" aria-label="Deskripsi perubahan">
        <input type="text" name="riwayat[{{ $i }}][oleh]" value="{{ $row['oleh'] ?? '' }}" aria-label="Disusun/direvisi oleh">
        <button type="button" class="icon" data-remove aria-label="Hapus baris">✕</button>
    </div>
    @foreach (['versi', 'tanggal', 'deskripsi', 'oleh'] as $kolom)
        @error("riwayat.$i.$kolom") <small class="error">{{ $message }}</small> @enderror
    @endforeach
</div>
