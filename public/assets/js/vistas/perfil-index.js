// JavaScript de la vista perfil/index.php: vista previa de la foto antes de guardarla.
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('foto');
    const vista = document.getElementById('vistaFoto');
    if (!input || !vista) return;

    input.addEventListener('change', function () {
        const archivo = input.files[0];
        if (!archivo || !archivo.type.startsWith('image/')) return;
        const img = document.createElement('img');
        img.className = 'avatar-usuario avatar-xl';
        img.alt = 'Vista previa de la foto';
        img.src = URL.createObjectURL(archivo);
        vista.replaceChildren(img);
    });
});
