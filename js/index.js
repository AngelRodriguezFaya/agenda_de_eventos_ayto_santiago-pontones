
document.addEventListener("DOMContentLoaded", function() {

    // 1. CAPTURAR ELEMENTOS DEL DOM
    const desplegarPanel = document.getElementById('desplegar_panel_comentarios');
    const panelComentarios = document.getElementById('panel_comentarios');
    const btnCerrarPanel = document.getElementById('btn-cerrar-comentarios');

    // 2. EVENTO PARA ABRIR EL PANEL
    desplegarPanel.addEventListener('mouseenter', function() {
        // Le quitamos la clase 'oculto' al panel
        panelComentarios.classList.remove('oculto');
    });

    // 3. EVENTO PARA CERRAR EL PANEL
    btnCerrarPanel.addEventListener('click', function() {
        // Le volvemos a poner la clase 'oculto' al panel
        panelComentarios.classList.add('oculto');
    });

});