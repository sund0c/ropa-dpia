@once
<script>
// Centang TTE → tampilkan & wajibkan unggah label; hapus centang → sembunyikan & kosongkan
document.addEventListener('change', (e) => {
    const cek = e.target.closest('[data-tte-cek]');
    if (!cek) return;
    const unggah = cek.closest('[data-tte-item]').querySelector('.tte-unggah');
    const file   = unggah.querySelector('input[type=file]');
    unggah.hidden = !cek.checked;
    file.disabled = !cek.checked;
    file.required = cek.checked;
    if (!cek.checked) file.value = '';
});
</script>
@endonce
