// JavaScript de la vista configuracion/hotel.php (antes estaba inline; la CSP no lo permite).

document.addEventListener('DOMContentLoaded', function() {
    const inputLogo = document.getElementById('inputLogoHotel');
    const previewLogo = document.getElementById('previewLogoHotel');

    if (inputLogo && previewLogo) {
        inputLogo.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    previewLogo.src = evt.target.result;
                    previewLogo.hidden = false;
                    document.getElementById('previewLogoVacio')?.setAttribute('hidden', '');
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
