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
//                   FUNCIONES AUXILIARES GLOBALES
// ==========================================================================================================

// Esta función recibe un "elemento" de HTML y le quita la clase oculto
function mostrarElemento(elemento) {
    elemento.classList.remove(OCULTO_CLASS);
}

// Esta función recibe un "elemento" de HTML y le pone la clase oculto
function ocultarElemento(elemento) {
    elemento.classList.add(OCULTO_CLASS);
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
});

