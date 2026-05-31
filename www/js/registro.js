// registro.js - Validación del formulario de registro

document.addEventListener('DOMContentLoaded', function() {
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
});