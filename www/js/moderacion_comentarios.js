// moderacion_comentarios.js - Lógica específica para editar y eliminar comentarios
// Es común al evento (dentro del panel de comentarios) y al moderador (gestor de comentarios)

document.addEventListener('DOMContentLoaded', function() {
    
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
});