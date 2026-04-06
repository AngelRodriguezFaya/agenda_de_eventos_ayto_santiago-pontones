const OCULTO_CLASS = 'oculto'; // Clase que indica que un elemento se debe ocultar

// Opciones para formatear la fecha y hora en español
// Ejemplo: "15 de septiembre de 2025, 14:30"
const opcionesFecha = { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric', 
    hour: '2-digit', 
    minute: '2-digit' 
};

// Comentarios iniciales harcodeados
const comentariosIniciales = [
    {
        nombre: "María Gómez",
        email: "mariagm@gmail.com",
        texto: "¡Qué ganas de participar este año! El recorrido de la edición anterior fue espectacular. Mi parte favorita fue sin duda la subida del cerro de la Matea."
    },
    {
        nombre: "Carlos López",
        email: "carloslopez@outlook.com",
        texto: "La organización de este evento es impecable. Me encanta que hayan modalidades distintas para los que no somos corredores profesionales. ¡Nos vemos en la línea de salida!"
    },
    {
        nombre: "Juan Pérez",
        email: "juanitomtb@yahoo.com",
        texto: "He participado en varias ediciones y cada año me sorprende la belleza del recorrido. Me encanta ir a Santiago de la Espada para disfrutar de la naturaleza y de la bici. ¡Recomiendo a todos que se animen a participar!"
    }
];

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

    // Nombre del autor del comentario
    const nombreElem = document.createElement('h4');
    nombreElem.textContent = nombre;
    comentario.appendChild(nombreElem);

    // Email de contacto del autor del comentario
    const emailElem = document.createElement('p');
    emailElem.classList.add('email-comentario');
    emailElem.textContent = email; //`Email: ${email}`;
    comentario.appendChild(emailElem);

    // Descripción del comentario
    const textoElem = document.createElement('p');
    textoElem.textContent = texto;
    comentario.appendChild(textoElem);

    // Fecha y hora del comentario
    const fechaElem = document.createElement('p');
    const fechaActual = new Date();
    fechaElem.classList.add('fecha-comentario');
    fechaElem.textContent = fechaActual.toLocaleDateString("es-ES", opcionesFecha);
    comentario.appendChild(fechaElem);

    return comentario;
}

// Le pedimos que reciba el 'contenedor' para que sepa dónde tiene que pegar los comentarios
function cargarComentariosIniciales(contenedor) {
    // Usamos el bucle forEach para recorrer cada comentario del array
    comentariosIniciales.forEach(function(comentario) {

        // Creamos un nuevo comentario        
        const nuevoElemento = crearComentario(comentario.nombre, comentario.email, comentario.texto);
        
        contenedor.appendChild(nuevoElemento); 
    });
}

// Nos aseguramos de que el DOM esté completamente cargado antes de ejecutar nuestro código
document.addEventListener("DOMContentLoaded", function() {

    // En primer lugar, capturamos los elementos del DOM
    const desplegarPanel = document.getElementById('desplegar_panel_comentarios');
    const btnCerrarPanel = document.getElementById('btn-cerrar-comentarios');

    const panelComentarios = document.getElementById('panel_comentarios');
    
    const btnAniadirComentario = document.getElementById('btn-aniadir-comentario');
    const formComentario = document.getElementById('form-comentario');
    // const btnEnviarComentario = document.getElementById('btn-enviar-comentario');
    const btnCancelarComentario = document.getElementById('btn-cancelar-comentario');
    
    const listaComentarios = document.getElementById('lista-comentarios');

    // Cargamos los comentarios iniciales al cargar la página
    cargarComentariosIniciales(listaComentarios);
    

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
    // btnEnviarComentario.addEventListener('click', function() {
    //     const nombre = document.getElementById('nombre').value.trim(); // trim() para eliminar espacios en blanco al inicio y al final
    //     const email = document.getElementById('email').value.trim();
    //     const texto = document.getElementById('texto-comentario').value.trim();
    //     const comentario = crearComentario(nombre, email, texto);
    //     listaComentarios.prepend(comentario); // COn prepend() añadimos el nuevo comentario al principio de la lista
    //     ocultarElemento(formComentario); // Ocultamos el formulario después de enviar el comentario
    //     // Limpiamos los campos del formulario
    //     limpiarEltosFormulario();
    // });

    formComentario.addEventListener('submit', function(event) {
        event.preventDefault(); // Evitamos que el formulario recargue la página al hacer submit

        const nombre = document.getElementById('nombre').value.trim(); // trim() para eliminar espacios en blanco al inicio y al final
        const email = document.getElementById('email').value.trim();
        const texto = document.getElementById('texto-comentario').value.trim();
        const comentario = crearComentario(nombre, email, texto);

        // Mostramos un cuadro de confirmación antes de añadir el comentario a la lista
        const confirmacion = confirm("¿Estás seguro de que quieres añadir este comentario?"); // Mostramos un cuadro de confirmación
        if (confirmacion) { // Si es true, agregamos el comentario a la lista, si es false, no hacemos nada
            listaComentarios.prepend(comentario); // COn prepend() añadimos el nuevo comentario al principio de la lista
        }
        
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