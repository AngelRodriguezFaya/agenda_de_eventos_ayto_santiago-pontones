// index.js
// Ángel Rodríguez Faya

// ==========================================================================================================
//                          CONSTANTES Y DATOS INICIALES
// ==========================================================================================================

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


// Array de comentarios iniciales harcodeados
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


// Array de 10 localidades del municipio de Santiago-Pontones.
const localidades = [ 
    "santiago de la espada",
    "la matea",
    "pontones",
    "cerezo",
    "cañuelos",
    "teatinos",
    "marchena",
    "miller",
    "ruices",
    "atascaderos",
];


// ==========================================================================================================
//                              FUNCIONES AUXILIARES
// ==========================================================================================================

// Esta función recibe un "elemento" de HTML y le quita la clase oculto
function mostrarElemento(elemento) {
    elemento.classList.remove(OCULTO_CLASS);
}


// Esta función recibe un "elemento" de HTML y le pone la clase oculto
function ocultarElemento(elemento) {
    elemento.classList.add(OCULTO_CLASS);
}


// Limpia los campos del formulario y resetea los labels a su estado original
function limpiarEltosFormulario(lNombre, lEmail, lTexto) {
    document.getElementById('nombre').value = '';
    document.getElementById('email').value = '';
    document.getElementById('texto-comentario').value = '';

    // Reseteamos las etiquetas (labels) a su estado original
    lNombre.style.color = ''; // Al dejarlo vacío, vuelve al color por defecto del CSS
    lNombre.textContent = 'Nombre:';

    lEmail.style.color = '';
    lEmail.textContent = 'E-mail:';

    lTexto.style.color = '';
    lTexto.textContent = 'Comentario:';
}


// Crea un comentario con los parámetros pasados, y lo devuelve
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


// Esta función carga los comentarios iniciales que están harcodeados (ya que no tenemos BD)
// Le pedimos que reciba el 'contenedor' para que sepa dónde tiene que pegar los comentarios
function cargarComentariosIniciales(contenedor) {
    // Usamos el bucle forEach para recorrer cada comentario del array
    comentariosIniciales.forEach(function(comentario) {

        // Creamos un nuevo comentario        
        const nuevoElemento = crearComentario(comentario.nombre, comentario.email, comentario.texto);
        
        contenedor.appendChild(nuevoElemento); 
    });
}


// Comprueba si un email es válido haciendo uso de una expresión regular.
function esEmailValido(email) {
    /* \w en JavaScript equivale a [A-Za-z0-9_]*/
    const exprRegEmail = /^\w+([.-_+]?\w+)*@\w+([.-]?\w+)*(\.\w{2,10})+$/;
    return exprRegEmail.test(email);
}


// ==========================================================================================================
//                              MANEJO DEL DOM 
// ==========================================================================================================

// Nos aseguramos de que el DOM esté completamente cargado antes de ejecutar nuestro código
document.addEventListener("DOMContentLoaded", function() {

    // -------- En primer lugar, capturamos los elementos del DOM -----------------------
    // Elementos relacionados con la apertura/cierre del panel de comentarios
    const desplegarPanel = document.getElementById('desplegar_panel_comentarios');
    const btnCerrarPanel = document.getElementById('btn-cerrar-comentarios');
    const panelComentarios = document.getElementById('panel_comentarios');
    
    // Elementos relacionados con añadir un nuevo comentario
    const btnAniadirComentario = document.getElementById('btn-aniadir-comentario');
    const formComentario = document.getElementById('form-comentario');
    const btnCancelarComentario = document.getElementById('btn-cancelar-comentario');
    const listaComentarios = document.getElementById('lista-comentarios');

    // Elementos relacionados con el mensaje de error (modal personalizado)
    const mensajeError = document.getElementById('mensaje-error');
    const textoError = document.getElementById('texto-error');
    const btnAceptarError = document.getElementById('btn-aceptar-msg-error');

    // Etiquetas (labels) del formulario.
    const labelNombre = document.querySelector('label[for="f-nombre"]');
    const labelEmail = document.querySelector('label[for="f-email"]');
    const labelTexto = document.querySelector('label[for="f-texto-comentario"]');

    // Campos (o cajones de texto) del formulario.
    const campoNombre = document.getElementById('nombre')
    const campoEmail = document.getElementById('email');
    const campoTexto = document.getElementById('texto-comentario')


    // ------------ Cargamos los comentarios iniciales al cargar la página -----------------
    cargarComentariosIniciales(listaComentarios);
    

    // --------- Añadimos los eventos a los elementos del DOM ------------------------------

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


    // evento para añadir el comentario
    formComentario.addEventListener('submit', function(event) {
        event.preventDefault(); // Evitamos que el formulario recargue la página al hacer submit

        // Paso 1: Capturamos los valores de los campos del formulario
        const nombre = campoNombre.value.trim(); // trim() para eliminar espacios en blanco al inicio y al final
        const email = campoEmail.value.trim();
        const texto = campoTexto.value.trim();

        // RESETEAMOS LAS ETIQUETAS (por si el usuario está reintentando enviar)
        // Pero NO SE RESETEA EL CONTENIDO de los campos
        labelNombre.style.color = ''; labelNombre.textContent = 'Nombre:';
        labelEmail.style.color = '';  labelEmail.textContent = 'E-mail:';
        labelTexto.style.color = '';  labelTexto.textContent = 'Comentario:';

        // Paso 2: Comprobamos que no haya ningún campo vacío
        let hayCamposVacios = false; // flag

        if (nombre === '') {
            labelNombre.style.color = 'red';
            labelNombre.textContent = 'Nombre: *no puede estar vacío';
            hayCamposVacios = true; // Levantamos el flag de error
        }

        if (email === '') {
            labelEmail.style.color = 'red';
            labelEmail.textContent = 'E-mail: *no puede estar vacío';
            hayCamposVacios = true; 
        }

        if (texto === '') {
            labelTexto.style.color = 'red';
            labelTexto.textContent = 'Comentario: *no puede estar vacío';
            hayCamposVacios = true; 
        }

        // Si el flag está activo, mostramos el modal (el mensaje de error) y abortamos
        if (hayCamposVacios) {
            let mensaje = "Por favor, completa todos los campos del formulario para poder añadir tu comentario.";
            textoError.textContent = mensaje; 
            mostrarElemento(mensajeError); 
            
            return; // Salimos de la función para que no se cree el comentario
        }

        // Paso 3: Comprobamos que el email tiene un formato válido
        if(!esEmailValido(email)) {
            labelEmail.style.color = 'red';
            labelEmail.textContent = 'E-mail: *formato no válido';

            let mensaje = "El formato del email no es correcto. Por favor, introduce una dirección de correo electrónico válida.";
            textoError.textContent = mensaje; 
            mostrarElemento(mensajeError);

            return; // Salimos de la función para que no se cree el comentario
        }

        // Paso 4: Si todo es correcto, creamos el nuevo comentario y lo añadimos a la lista
        const comentario = crearComentario(nombre, email, texto);

            // Mostramos un cuadro de confirmación antes de añadir el comentario a la lista
        const confirmacion = confirm("¿Estás seguro de que quieres añadir este comentario?"); // Mostramos un cuadro de confirmación
        
        if (confirmacion) { // Si es true, agregamos el comentario a la lista, si es false, no hacemos nada
            listaComentarios.prepend(comentario); // COn prepend() añadimos el nuevo comentario al principio de la lista
            alert("¡Comentario enviado! Gracias por tu participación."); // Mostramos un mensaje de alerta al enviar el comentario
        }

        // Paso 5: Ocultamos el formulario y limpiamos los campos
        
        ocultarElemento(formComentario); // Ocultamos el formulario después de enviar el comentario
        
        limpiarEltosFormulario(labelNombre, labelEmail, labelTexto); // Limpiamos los campos del formulario
    });
    

    // evento para cancelar la agregación del comentario
    btnCancelarComentario.addEventListener('click', function() {
        ocultarElemento(formComentario); // Ocultamos el formulario
        // Limpiamos los campos del formulario
        limpiarEltosFormulario(labelNombre, labelEmail, labelTexto);
    });


    // evento para ocultar el dialogo modal personalizado (para errores)
    btnAceptarError.addEventListener('click', function() {
        ocultarElemento(mensajeError);
    }); 

    // evento para capturar lo que se escribe en el texto del comentario
    // y poder localizar palabras relacionadas con las localidades para mostrarlas resaltadas
    campoTexto.addEventListener('input', function() {
        // Paso 1: Leemos y guardamos todo lo que el usuario ha escrito hasta ahora
        let textoActual = campoTexto.value;

        // Paso 2: Recorremos el array de localidades
        localidades.forEach(function(localidad) {
            // Expresión regular para buscar la localidad de forma global e insensible a mayúsculas/minúsculas
            let rastreador = new RegExp(localidad, "gi");

            // Reemplazamos en el texto
            // la sintaxis es: texto.replace(rastreador, nuevoValor)
            // no modificamos el texto original, sino que nos devuelve una nueva cadena con los cambios.
            textoActual = textoActual.replace(rastreador, localidad.toUpperCase());
            campoTexto.style.border = '2px solid green'; 
        });

        // Paso 3: Devolvemos el texto ya corregido a la caja del formulario
        campoTexto.value = textoActual;
    });
});