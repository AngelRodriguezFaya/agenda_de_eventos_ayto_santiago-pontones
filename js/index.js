const OCULTO_CLASS = 'oculto'; // Clase que indica que un elemento se debe ocultar

// Esta función recibe un "elemento" de HTML y le quita la clase oculto
function mostrarElemento(elemento) {
    elemento.classList.remove(OCULTO_CLASS);
}

// Esta función recibe un "elemento" de HTML y le pone la clase oculto
function ocultarElemento(elemento) {
    elemento.classList.add(OCULTO_CLASS);
}

function limpiarEltosFormulario() {
    document.getElementById('nombre').value = '';
    document.getElementById('email').value = '';
    document.getElementById('texto-comentario').value = '';
}

function crearComentario(nombre, email, texto) {
    const comentario = document.createElement('div');
    comentario.classList.add('comentario');

    const nombreElem = document.createElement('h4');
    nombreElem.textContent = nombre;
    comentario.appendChild(nombreElem);

    const emailElem = document.createElement('p');
    emailElem.textContent = `Email: ${email}`;
    comentario.appendChild(emailElem);

    const textoElem = document.createElement('p');
    textoElem.textContent = texto;
    comentario.appendChild(textoElem);

    return comentario;
}

// Nos aseguramos de que el DOM esté completamente cargado antes de ejecutar nuestro código
document.addEventListener("DOMContentLoaded", function() {

    // En primer lugar, capturamos los elementos del DOM
    const desplegarPanel = document.getElementById('desplegar_panel_comentarios');
    const panelComentarios = document.getElementById('panel_comentarios');
    const btnCerrarPanel = document.getElementById('btn-cerrar-comentarios');
    const btnAniadirComentario = document.getElementById('btn-aniadir-comentario');
    const formComentario = document.getElementById('form-comentario');
    const btnEnviarComentario = document.getElementById('btn-enviar-comentario');
    const btnCancelarComentario = document.getElementById('btn-cancelar-comentario');
    const listaComentarios = document.getElementById('lista-comentarios');

    // evento para abrir el panel de comentarios
    desplegarPanel.addEventListener('mouseenter', function() { 
        mostrarElemento(panelComentarios);
    });

    // evento para cerrar el panel de comentarios
    btnCerrarPanel.addEventListener('click', function() {
        ocultarElemento(panelComentarios);
    });

    // evento para mostrar el formulario de añadir comentario
    btnAniadirComentario.addEventListener('click', function() {
        formComentario.classList.remove(OCULTO_CLASS); // Mostramos el formulario
    });

    // evento para enviar el comentario
    btnEnviarComentario.addEventListener('click', function() {
        const nombre = document.getElementById('nombre').value.trim(); // trim() para eliminar espacios en blanco al inicio y al final
        const email = document.getElementById('email').value.trim();
        const texto = document.getElementById('texto-comentario').value.trim();
        const comentario = crearComentario(nombre, email, texto);
        listaComentarios.prepend(comentario); // COn prepend() añadimos el nuevo comentario al principio de la lista
        ocultarElemento(formComentario); // Ocultamos el formulario después de enviar el comentario
        // Limpiamos los campos del formulario
        limpiarEltosFormulario();
    });
    
    // evento para cancelar el comentario
    btnCancelarComentario.addEventListener('click', function() {
        ocultarElemento(formComentario); // Ocultamos el formulario
        // Limpiamos los campos del formulario
        limpiarEltosFormulario();
    });
});