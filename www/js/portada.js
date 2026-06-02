// portada.js - Lógica para la búsqueda en la portada

document.addEventListener('DOMContentLoaded', function() {
    // const BASE_URL = document.body.dataset.baseUrl || '/';
    const input = document.getElementById('input-busqueda-portada');
    const desplegable = document.getElementById('desplegable-busqueda');

    // Temporizador para no lanzar una petición por cada tecla pulsada
    let temporizador = null;

    input.addEventListener('input', function() {
        // Si ha habido un cambio en el input
        
        const termino = this.value.trim();
        
        // Limpiamos el temporizador anterior para no hacer peticiones innecesarias
        clearTimeout(temporizador);

        // Si el campo está vacío o tiene menos de 2 caracteres, ocultamos el desplegable
        if (termino.length < 2) {
            desplegable.innerHTML = '';
            desplegable.classList.add('oculto');
            return;
        }

        // Esperamos 300ms desde que el usuario deja de escribir para lanzar la petición
        // Esto se llama "debounce" y evita saturar el servidor
        temporizador = setTimeout(async function() {
            try {
                const respuesta = await fetch(BASE_URL + 'api_busqueda.php?q=' + encodeURIComponent(termino));
                const eventos = await respuesta.json();

                // Limpiamos el desplegable antes de rellenarlo
                desplegable.innerHTML = '';

                if (eventos.length === 0) {
                    // Si no hay resultados, mostramos un mensaje
                    const li = document.createElement('li');
                    li.textContent = 'No se encontraron eventos';
                    li.classList.add('sin-resultados');
                    desplegable.appendChild(li);
                } else {
                    // Por cada evento recibido, creamos un <li> con un enlace
                    eventos.forEach(function(evento) {
                        const li = document.createElement('li');
                        const enlace = document.createElement('a');
                        enlace.href = BASE_URL + '?ev=' + evento.id; // Enlace al detalle del evento
                        enlace.textContent = evento.titulo;
                        li.appendChild(enlace);
                        desplegable.appendChild(li);
                    });
                }

                // Mostramos el desplegable
                desplegable.classList.remove('oculto');

            } catch (error) {
                console.error('Error en la búsqueda:', error);
            }
        }, 300);
    });

    // Cerrar el desplegable si el usuario hace click fuera
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !desplegable.contains(e.target)) {
            desplegable.classList.add('oculto');
        }
    });
});