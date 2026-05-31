// evento.js - Lógica específica del detalle de evento (evento.php)

document.addEventListener("DOMContentLoaded", function() {

    // ===========================================================================================
    //                   FUNCIONES AUXILIARES
    // ===========================================================================================

    // Limpia los campos del formulario de los comentarios y resetea los labels a su estado original
    function limpiarEltosFormularioComen(lNombre, lEmail, lTexto) {
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

    // --------------------------------------------------------------------
    //                  GESTIÓN DE LOS COMENTARIOS
    // --------------------------------------------------------------------

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


    // Para las variables dinámicas guardadas en la BD

        // 1. Localizamos el elemento que tiene los datos
    const elementoEvento = document.getElementById('evento-detalle');

        // 2. Si el elemento no existe, salimos para no dar error
    if (!elementoEvento) return;

        // 3. Extraemos los datos de los atributos 'data-'
    const EVENTO_ID = elementoEvento.dataset.id;
    // Ya no re-declaramos BASE_URL para evitar conflictos de "Temporal Dead Zone", usamos la global
    
        // Como las localidades son un array (JSON), lo parsearmos
    const localidades = JSON.parse(elementoEvento.dataset.localidades);

        // Para depurar, mostramos por consola los datos que hemos extraído
    console.log("Cargado evento:", EVENTO_ID);
    console.log("Localidades para resaltar:", localidades);

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

        // Paso 4: Si todo es correcto, guardamos el comentario en la Base de Datos con AJAX (fetch)
        const confirmacion = confirm("¿Estás seguro de que quieres añadir este comentario?"); 
        if (!confirmacion) return; // Si no quiere, cancelamos

        // Creamos lo que le vamos a enviar al servidor de PHP en JSON
        const datos = {
            evento_id: EVENTO_ID,  // Viene de la constante inyectada en HTML por Twig
            nombre: nombre,
            email: email,
            texto: texto
        };

        // Hacemos la petición POST al archivo "api_comentarios.php"
        fetch(BASE_URL + 'api_comentarios.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datos)
        })
        .then(response => response.json()) // Se queda esperando a que PHP termine de guardar los datos en MySQL y decodifique la respuesta rápida del servidor.
        .then(data => {
            if (data.status === "success") {
                // i PHP nos confirma que se ha guardado bien en la base de datos sin errores, entonces llammamos a la función de crearComentario.
                const comentario = crearComentario(nombre, email, texto);
                listaComentarios.prepend(comentario); 
                
                alert("¡Comentario enviado y guardado! Gracias por tu participación.");

                // Si todo ha ido bien, ocultamos y limpiamos el form
                ocultarElemento(formComentario);
                limpiarEltosFormularioComen(labelNombre, labelEmail, labelTexto);
            } else {
                alert("Hubo un problema al guardar el comentario: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error enviando comentario:', error);
            alert("No se pudo conectar con el servidor.");
        });

    });
    

    // evento para cancelar la agregación del comentario
    btnCancelarComentario.addEventListener('click', function() {
        ocultarElemento(formComentario); // Ocultamos el formulario
        // Limpiamos los campos del formulario
        limpiarEltosFormularioComen(labelNombre, labelEmail, labelTexto);
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
        });

        // Paso 3: Devolvemos el texto ya corregido a la caja del formulario
        campoTexto.value = textoActual;
    });

});