// gestor.js - Lógica específica del panel de gestión de eventos (gestor.php)

document.addEventListener('DOMContentLoaded', function () {
    const BASE_URL = document.body.dataset.baseUrl || '/';

    // ================================================================
    //          BÚSQUEDA AJAX EN EL GESTOR DE EVENTOS
    // ================================================================

    const inputBusqueda  = document.getElementById('input-busqueda-gestor');
    const selectCriterio = document.getElementById('select-criterio-gestor');
    const btnBuscar      = document.getElementById('btn-buscar-gestor');
    const btnLimpiar     = document.getElementById('btn-limpiar-gestor');
    const zonaEventos    = document.querySelector('.zona-eventos-gestor');

    async function buscarEventos() {
        const termino  = inputBusqueda.value.trim();
        const criterio = selectCriterio.value;

        try {
            const respuesta = await fetch(
                BASE_URL + 'api_busqueda_gestor.php?termino=' + encodeURIComponent(termino) + '&criterio=' + criterio
            );
            const eventos = await respuesta.json();

            zonaEventos.innerHTML = '';

            if (eventos.length === 0) {
                zonaEventos.innerHTML = '<div class="alerta-aviso"><p>No hay eventos que coincidan con la búsqueda.</p></div>';
                return;
            }

            eventos.forEach(function(evento) {

                // PASO 1: Calculamos los hashtags antes de construir el innerHTML
                let htmlHashtags = '';
                if (evento.hashtags_list) {
                    const tags = evento.hashtags_list.split(',');
                    const tagsHtml = tags.map(tag =>
                        `<span class="hashtag-etiqueta-gestor">#${tag.trim()}</span>`
                    ).join('');
                    htmlHashtags = `<div class="contenedor-hashtags-gestor">${tagsHtml}</div>`;
                }

                // PASO 2: Construimos el innerHTML usando la variable ya calculada
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta-evento-gestor';
                tarjeta.innerHTML = `
                    <div class="columna-visibilidad-gestor">
                        <label class="switch-visibilidad">
                            <input type="checkbox"
                                class="check-visibilidad"
                                data-id="${evento.id}"
                                ${evento.publicado == 1 ? 'checked' : ''}>
                            <span class="slider-visibilidad">Evento Publicado</span>
                        </label>
                        <span id="estado-visibilidad-${evento.id}" class="oculto mensaje-evento-publicado">
                            ¡Guardado!
                        </span>
                    </div>

                    <div class="contenedor-cartel-gestor">
                        <img src="${BASE_URL}${evento.ruta_imagen || 'images/no-image.png'}" 
                            alt="Cartel de ${evento.titulo}" 
                            class="imagen-cartel-gestor">
                    </div>

                    <div class="info-evento-gestor">
                        <div class="cabecera-info-gestor">
                            <h3>${evento.titulo}</h3>
                            <div class="acciones-evento-gestor">
                                <a href="${BASE_URL}editar_evento.php?id=${evento.id}" class="btn-editar-evento">Editar</a>
                                <a href="${BASE_URL}borrar_evento.php?id=${evento.id}"
                                    class="btn-borrar-evento btn-peligro"
                                    onclick="return confirm('¿Estás seguro? Esta acción NO se puede deshacer.')">
                                    Eliminar
                                </a>
                            </div>
                        </div>
                        <div class="metadatos-gestor">
                            <span class="meta-tag-gestor categoria-tag">${evento.categoria || 'Sin categoría'}</span>
                            <span class="meta-tag-gestor localidad-tag">📍 ${evento.localidad || 'Sin localidad'}</span>
                            <span class="meta-tag-gestor fecha-tag">📅 ${evento.fecha_realizacion}</span>
                        </div>
                        <p class="descripcion-corta-gestor">${evento.descripcion || ''}</p>
                        ${htmlHashtags}
                    </div>
                `;

                zonaEventos.appendChild(tarjeta);

                // Re-enganchamos el listener del checkbox a la nueva tarjeta
                const checkbox = tarjeta.querySelector('.check-visibilidad');
                checkbox.addEventListener('change', cambiarVisibilidad);
            });

        } catch (error) {
            console.error('Error en la búsqueda AJAX del gestor:', error);
        }
    }

    btnBuscar.addEventListener('click', buscarEventos);

    inputBusqueda.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') buscarEventos();
    });

    btnLimpiar.addEventListener('click', function() {
        inputBusqueda.value = '';
        buscarEventos();
    });

    // ================================================================
    //      CAMBIO DE VISIBILIDAD (PUBLICADO/NO PUBLICADO) CON AJAX
    // ================================================================

    // Buscamos TODOS los checkboxes con la clase que pusimos en el HTML
    const checkboxesVisibilidad = document.querySelectorAll('.check-visibilidad');

    checkboxesVisibilidad.forEach(checkbox => {
        checkbox.addEventListener('change', async function() {
            // Obtenemos el ID del evento guardado en el atributo data-id del HTML
            const idEvento = this.getAttribute('data-id');
            // Según el estado del checkbox, decidimos si el nuevo estado es 1 (publicado) o 0 (no publicado)
            const nuevoEstado = this.checked ? 1 : 0; 

            // Buscamos el span del mensaje de Guardado para este evento especifico
            const mensajeGuardado = document.getElementById(`estado-visibilidad-${idEvento}`);

            try {
                const respuesta = await fetch(BASE_URL + 'api_visibilidad.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_evento: idEvento, estado: nuevoEstado })
                });

                const resultado = await respuesta.json();

                if (respuesta.ok && resultado.status === "success") {
                    if (mensajeGuardado) {
                        mostrarElemento(mensajeGuardado);
                        // Ocultar a los 2 segundos
                        setTimeout(() => { ocultarElemento(mensajeGuardado); }, 2000);
                    }
                } else {
                    alert("Error al cambiar la visibilidad: " + (resultado.message || "No se pudo actualizar"));
                    this.checked = !this.checked; // Revertir el checkbox si falló
                }
            } catch (error) {
                console.error('Error fatal en la petición:', error);
                alert("Hubo un problema de conexión con el servidor.");
                this.checked = !this.checked; // Revertir
            }
        });
    });

    // async function cambiarVisibilidad() {
    //     const idEvento    = this.dataset.id;
    //     const nuevoEstado = this.checked ? 1 : 0;
    //     const mensajeGuardado = document.getElementById('estado-visibilidad-' + idEvento);

    //     try {
    //         const respuesta = await fetch(BASE_URL + 'api_visibilidad.php', {
    //             method: 'POST',
    //             headers: { 'Content-Type': 'application/json' },
    //             body: JSON.stringify({ id_evento: idEvento, estado: nuevoEstado })
    //         });

    //         const resultado = await respuesta.json();

    //         if (respuesta.ok && resultado.status === 'success') {
    //             if (mensajeGuardado) {
    //                 mensajeGuardado.classList.remove('oculto');
    //                 setTimeout(() => mensajeGuardado.classList.add('oculto'), 2000);
    //             }
    //         } else {
    //             alert('Error: ' + (resultado.message || 'No se pudo actualizar'));
    //             this.checked = !this.checked;
    //         }
    //     } catch (error) {
    //         console.error('Error al cambiar visibilidad:', error);
    //         this.checked = !this.checked;
    //     }
    // }

    // document.querySelectorAll('.check-visibilidad').forEach(function(checkbox) {
    //     checkbox.addEventListener('change', cambiarVisibilidad);
    // });
});