// ========================================================
// ARCHIVO: javascript/post.js
// ========================================================
document.addEventListener("DOMContentLoaded", () => {

    // 1. LÓGICA PARA LA VISTA PREVIA DE LA IMAGEN
    const inputImagen = document.getElementById("imagen");
    const preview = document.getElementById("preview");

    inputImagen.addEventListener("change", function() {
        const archivo = this.files[0]; // Agarramos el archivo que subió
        if (archivo) {
            const lector = new FileReader(); // El lector lee la imagen de su compu

            lector.onload = function(e) {
                preview.src = e.target.result; // Le metemos la imagen a la etiqueta <img>
                preview.style.display = "block"; // La hacemos visible
            }

            lector.readAsDataURL(archivo);
        } else {
            preview.style.display = "none";
            preview.src = "";
        }
    });

    // 2. LÓGICA PARA ENVIAR EL FORMULARIO
    const formPublicar = document.getElementById("formPublicar");
    const mensajeRespuesta = document.getElementById("mensaje-respuesta");

    formPublicar.addEventListener("submit", async (evento) => {
        evento.preventDefault(); // Evitamos que la página se recargue

        // Validamos la sesión antes de mandar nada
        const btn = formPublicar.querySelector('button');
        btn.textContent = "Subiendo...";
        btn.disabled = true;

        try {
            // FormData es mágico, agarra todos los inputs y la foto en un solo paquete
            const datosFormulario = new FormData(formPublicar);

            // Mandamos el paquete al servidor (Este archivo PHP lo haremos en el siguiente paso)
            const respuesta = await fetch("../back-end/post.php", {
                method: "POST",
                body: datosFormulario
            });

            const resultado = await respuesta.json();

            if (resultado.exito) {
                mensajeRespuesta.style.color = "green";
                mensajeRespuesta.textContent = "¡Producto publicado con éxito! Redirigiendo...";
                // Lo mandamos de regreso al main para que vea su producto
                setTimeout(() => { window.location.href = "main.html"; }, 1500);
            } else {
                mensajeRespuesta.style.color = "red";
                mensajeRespuesta.textContent = resultado.error || "Hubo un error al publicar.";
                btn.textContent = "Publicar Producto";
                btn.disabled = false;
            }

        } catch (error) {
            console.error("Error:", error);
            mensajeRespuesta.style.color = "red";
            mensajeRespuesta.textContent = "Error de red al intentar subir el producto.";
            btn.textContent = "Publicar Producto";
            btn.disabled = false;
        }
    });
});