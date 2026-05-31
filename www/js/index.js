// index.js
// Ángel Rodríguez Faya

// ==========================================================================================================
//                          CONSTANTES Y DATOS INICIALES
// ==========================================================================================================

// Obtenemos la URL base 
const BASE_URL = document.body.dataset.baseUrl || '/';

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

// Comprueba si un email es válido haciendo uso de una expresión regular.
function esEmailValido(email) {
    /* \w en JavaScript equivale a [A-Za-z0-9_]*/
    const exprRegEmail = /^\w+([.-_+]?\w+)*@\w+([.-]?\w+)*(\.\w{2,10})+$/;
    return exprRegEmail.test(email);
}

/** Comprueba si una contraseña es fuerte: Mínimo 8 caracteres, al menos 1 letra y 1 número
 *  Hace uso de una expresión regular que está ajustada para que no sea tan estricta que 
 *  requiera mayúsculas obligatorias si no lo deseas, pero cumple el requisito estándar de "contraseña fuerte"
 */
function esPasswordFuerte(password) {
    const regexPassword = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/;
    return regexPassword.test(password);
}

// ==========================================================================================================
//                              MANEJO DEL DOM 
// ==========================================================================================================

// Nos aseguramos de que el DOM esté completamente cargado antes de ejecutar nuestro código
document.addEventListener("DOMContentLoaded", function() {

    // ====================================================================
    //                  1. CÓDIGO GLOBAL.
    // ====================================================================

    // --------------------------------------------------------------------
    //      GESTIÓN DEL MODAL DE LOGIN (Funciona en todas las páginas)
    // --------------------------------------------------------------------
    const btnAbrirLogin = document.getElementById('btn-abrir-login');
    const modalLogin = document.getElementById('modal-login');
    const btnCerrarLogin = document.getElementById('btn-cerrar-login');

    // ABRIR EL MODAL DE LOGIN
    // Sólo añadimos el evento si el botón existe (si el usuario no ha iniciado sesión)
    if(btnAbrirLogin && modalLogin){
        btnAbrirLogin.addEventListener('click', function() {
            // mostrarElemento(modalLogin);
            modalLogin.classList.remove('oculto'); 
        });
    }

    // CERRAR EL MODAL DE LOGIN 
    // Igual, solo añadimos el evento si el botón existe
    if(btnCerrarLogin && modalLogin){
        btnCerrarLogin.addEventListener('click', function() {
            // ocultarElemento(modalLogin);
            modalLogin.classList.add('oculto');
            // Limpiamos los campos del formulario de login
            document.getElementById('login-nick').value = '';
            document.getElementById('login-password').value = '';
            document.getElementById('login-error-msg').style.display = 'none'; // Ocultamos el mensaje de error si estaba visible
        });
    }


    // --------------------------------------------------------------------
    //          PROCESAMIENTO DEL LOGIN (INICIO DE SESIÓN) CON AJAX
    // --------------------------------------------------------------------

    const formLogin = document.getElementById('form-login');
    const mensajeErrorLogin = document.getElementById('login-error-msg');

    if(formLogin) {
        formLogin.addEventListener('submit', async function(event) {
            event.preventDefault(); // Evitamos que el formulario recargue la página al hacer submit

            const nickValor = document.getElementById('login-nick').value.trim();
            const passwordValor = document.getElementById('login-password').value.trim();

            try {
                // Enviamos los datos al servidor y "esperamos" (await) la respuesta HTTP
                const respuesta = await fetch(BASE_URL + 'api_login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nick: nickValor, password: passwordValor })
                });

                // Si el servidor responde con un código de éxito (ej. 200 OK)
                if (respuesta.ok) {
                    // Recargamos la página para que Twig detecte la nueva $_SESSION y pinte el header con el usuario logueado
                    window.location.reload();
                } else {
                    // Si el servidor responde con un error (ej. 401), mostramos el mensaje de error
                    if (mensajeErrorLogin) {
                        mostrarElemento(mensajeErrorLogin);
                        mensajeErrorLogin.style.display = 'block'; // Nos aseguramos de que sea visible
                    }
                }
            } catch (error) {
                // Si hay un problema de red o el archivo PHP no existe, atrapamos el error aquí
                console.error('Erro en la petición de login:', error);
            }
        });
    }


    // --------------------------------------------------------------------
    //          PROCESAMIENTO DEL LOGOUT (CIERRE DE SESIÓN)
    // --------------------------------------------------------------------
    const btnLogout = document.getElementById('btn-logout');

    if(btnLogout) {
        btnLogout.addEventListener('click', async function(evento) {
            evento.preventDefault(); // Evitamos que el enlace recargue la página

            // Llamamos al archivo de PHP de logout
            try{
                const respuesta = await fetch(BASE_URL + 'api_logout.php');

                if (respuesta.ok) {
                    window.location.reload(); // Recargamos la página para que TWIG detecte que ya no hay $_SESSION
                }
            }catch(error) {
                console.error('Error en la petición de logout:', error);
            }
        });
    }


    // ====================================================================
    //                  2. CÓDIGO ESPECÍFICO.
    // ====================================================================

    // --------------------------------------------------------------------
    //     GESTIÓN DE LOS COMENTARIOS (Sólo en evento.html)
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

    // --------------------------------------------------------------------
    //                      BORRADO DE COMENTARIOS
    // --------------------------------------------------------------------

    const botonesBorrar = document.querySelectorAll('.btn-borrar-comentario');

    botonesBorrar.forEach( boton => {
        boton.addEventListener('click', async function() {

            // Dialogo de confirmación antes de borrar
            if(confirm("¿Estás seguro de que quieres eliminar este comentario? Esta acción no se puede deshacer.")) {
                // Si es que si, ejecutamos la función de borrado

                // Buscamos la tarjeta del comentario
                const tarjetaComentario = this.closest('.comentario');
                const comentarioId = tarjetaComentario.dataset.id;

                try {
                    // Hacemos la petición al servidor con Fetch
                    const respuesta = await fetch(BASE_URL + 'borrar_comentario.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        // body es el cuerpo del mensaje/peticion. stringify convierte el 
                        // objeto de JavaScript a una cadena JSON para enviarlo al servidor.
                        body: JSON.stringify({ id: comentarioId })
                    });

                    // Esperamos la respuesta del servidor 
                    const resultado = await respuesta.json();

                    // Si el servidor nos confirma que se ha borrado bien en la base de datos 
                    // entonces borramos la tarjeta del comentario del DOM
                    if (respuesta.ok && resultado.exito) {
                        tarjetaComentario.style.transition = "opacity 0.5s ease";
                        tarjetaComentario.style.opacity = "0";
                        setTimeout(() => tarjetaComentario.remove(), 500);
                    } else {
                        alert("Error: " + (resultado.error || "No se pudo borrar"));
                    }
                } catch (error) {
                    console.error('Error en la petición AJAX:', error);
                    alert("Hubo un problema de conexión con el servidor.");
                }
            }
        });
    });

    // --------------------------------------------------------------------
    //                      EDICIÓN DE COMENTARIOS
    // --------------------------------------------------------------------
    const botonesEditar = document.querySelectorAll('.btn-editar-comentario');

    botonesEditar.forEach(boton => {
        boton.addEventListener('click', async function() {
            const tarjetaComentario = this.closest('.comentario');
            const comentarioId = tarjetaComentario.dataset.id;
            const parrafoTexto = tarjetaComentario.querySelector('.texto-del-comentario');

            // Si el botón dice "Editar", transformamos el texto en un textarea
            if (this.textContent === 'Editar') {
                const textoActual = parrafoTexto.innerText;
                
                // Creamos el campo de texto editable
                const textarea = document.createElement('textarea');
                textarea.className = 'edit-textarea';
                textarea.style.width = '100%';
                textarea.style.minHeight = '80px';
                textarea.style.marginTop = '10px';
                textarea.style.backgroundColor = 'rgba(248, 245, 230)';
                textarea.style.color = 'black';
                textarea.value = textoActual;

                // Reemplazamos el párrafo por el textarea
                parrafoTexto.replaceWith(textarea);

                // Cambiamos el aspecto del botón
                this.textContent = 'Guardar';
                this.style.backgroundColor = '#28a745'; // Color verde para indicar guardado
            } 
            // Si el botón dice "Guardar", enviamos los datos por FETCH
            else if (this.textContent === 'Guardar') {
                const textarea = tarjetaComentario.querySelector('.edit-textarea');
                const nuevoTexto = textarea.value;

                try {
                    const respuesta = await fetch(BASE_URL + 'editar_comentario.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: comentarioId, texto: nuevoTexto })
                    });

                    const resultado = await respuesta.json();

                    if (respuesta.ok && resultado.exito) {
                        // Creamos un nuevo párrafo con el texto finalizado (incluye la firma)
                        const nuevoParrafo = document.createElement('p');
                        nuevoParrafo.className = 'texto-del-comentario';
                        nuevoParrafo.innerText = resultado.texto_actualizado; // Usamos innerText para respetar los saltos de línea

                        // Reemplazamos el textarea por el párrafo final
                        textarea.replaceWith(nuevoParrafo);

                        // Devolvemos el botón a su estado original
                        this.textContent = 'Editar';
                        this.style.backgroundColor = ''; // Restablece el color CSS original
                    } else {
                        alert("Error: " + (resultado.error || "No se pudo editar"));
                    }
                } catch (error) {
                    console.error('Error en la petición AJAX:', error);
                    alert("Hubo un problema de conexión con el servidor.");
                }
            }
        });
    });

    // ==========================================================================================================
    //                          VALIDACIÓN DE FORMULARIO DE REGISTRO
    // ==========================================================================================================
    const formRegistro = document.querySelector('form[action*="guardar_registro.php"]');
        
    if (formRegistro) {
        formRegistro.addEventListener('submit', function(evento) {
            let email = document.getElementById('email').value;
            let password = document.getElementById('password').value;
            let password_confirm = document.getElementById('password_confirm').value;

            if (!esEmailValido(email)) {
                alert("❌ Por favor, introduce un correo electrónico válido.");
                evento.preventDefault(); 
                return;
            }

            if (!esPasswordFuerte(password)) {
                alert("❌ La contraseña debe tener al menos 8 caracteres, incluir una letra y un número.");
                evento.preventDefault();
                return;
            }

            // Comprobar que las contraseñas coinciden
            if (password !== password_confirm) {
                alert("❌ Las contraseñas no coinciden.");
                evento.preventDefault();
                return;
            }
        });
    }

    // ==========================================================================================================
    //                 CAMBIO DE VISIBILIDAD DE LOS EVENTOS (PUBLICADO/NO PUBLICADO)
    // ==========================================================================================================

    // const checkboxesVisibilidad = document.querySelectorAll('.toggle-publicado');

    // checkboxesVisibilidad.forEach(checkbox => {
    //     checkbox.addEventListener('change', async function() {
    //         // Obtenemos el ID del evento guardado en el atributo data-id del HTML
    //         const idEvento = this.getAttribute('data-id');
    //         // Según el estado del checkbox, decidimos si el nuevo estado es 1 (publicado) o 0 (no publicado)
    //         const nuevoEstado = this.checked ? 1 : 0; 

    //         // Buscamos el span del mensaje de Guardado para este evento especifico
    //         const mensajeGuardado = document.getElementById(`estado-visibilidad-${idEvento}`);

    //         try {
    //             // Hacemos la petición AJAX
    //             const respuesta = await fetch(BASE_URL + 'api_visibilidad.php', {
    //                 method: 'POST',
    //                 headers: { 'Content-Type': 'application/json' },
    //                 body: JSON.stringify({ id_evento: idEvento, estado: nuevoEstado })
    //             });

    //             const resultado = await respuesta.json();

    //             if (respuesta.ok && resultado.status === "success") {
    //                 // Si el cambio se ha guardado correctamente en la base de datos, mostramos un mensaje de "Guardado"
    //                 mostrarElemento(mensajeGuardado);

    //                 // Y después de 2 segundos, ocultamos el mensaje de nuevo
    //                 setTimeout(() => {
    //                     ocultarElemento(mensajeGuardado);
    //                 }, 2000);
    //             } else {
    //                 alert("Error al cambiar la visibilidad: " + (resultado.message || "No se pudo actualizar"));
    //                 this.checked = !this.checked; // Revertimos el cambio del checkbox para reflejar el estado real
    //             }
    //         } catch (error) {
    //             console.error('Error en la petición al cambiar visibilidad:', error);
    //             alert("Hubo un problema de conexión con el servidor.");
    //             this.checked = !this.checked; // Revertimos el cambio del checkbox para reflejar el estado real
    //         }
    //     });
    // });

    // ==========================================================================================================
    //                 CAMBIO DE VISIBILIDAD DE LOS EVENTOS (PUBLICADO/NO PUBLICADO)
    // ==========================================================================================================

    

});

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