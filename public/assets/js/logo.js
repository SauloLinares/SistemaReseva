// Logo del hotel en un marco circular (.logo-marco: menú lateral y login). Si el logo es
// redondo con margen transparente (sus esquinas están vacías), se marca con .logo-redondo y el
// CSS lo acerca para que el dibujo no quede diminuto. Un logo cuadrado o con texto ancho se
// muestra completo sobre una placa clara.
(function () {
    'use strict';

    function revisar(img) {
        try {
            const lado = 32;
            const lienzo = document.createElement('canvas');
            lienzo.width = lienzo.height = lado;
            const ctx = lienzo.getContext('2d', { willReadFrequently: true });
            ctx.drawImage(img, 0, 0, lado, lado);
            const transparente = (x, y) => ctx.getImageData(x, y, 1, 1).data[3] < 16;
            const esquinas = [[1, 1], [lado - 2, 1], [1, lado - 2], [lado - 2, lado - 2]];
            if (esquinas.every(([x, y]) => transparente(x, y))) {
                img.closest('.logo-marco').classList.add('logo-redondo');
            }
        } catch (e) { /* imagen de otro origen o sin canvas: se muestra completa */ }
    }

    document.querySelectorAll('.logo-marco img').forEach(function (img) {
        if (img.complete && img.naturalWidth) revisar(img);
        else img.addEventListener('load', () => revisar(img), { once: true });
    });
})();
