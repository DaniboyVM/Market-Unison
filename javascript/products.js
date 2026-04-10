// ============================================================
// ARCHIVO: javascript/producto.js
// este archivo sirve para el funcionamiento de product.html
// ============================================================

document.addEventListener("DOMContentLoaded", () => {

    // 1. Leer el ID del producto de la URL
    const parametrosURL = new URLSearchParams(window.location.search);
    const idProducto = parametrosURL.get("id");

    if (idProducto) {
        console.log("Cargando detalles del producto ID:", idProducto);

        // Llamar a la función principal
        cargarDetallesDelProducto(idProducto);

    } else {
        mostrarMensajeError("Error: No se seleccionó ningún producto en la URL.");
    }
});

// ------------------------------------------------------------
// Función principal: Pide datos a PHP y llena la página
// ------------------------------------------------------------
async function cargarDetallesDelProducto(id) {
    // 1. Mostrar estado de carga (opcional, para mejor experiencia)
    const tituloElemento = document.getElementById("prod-titulo");
    tituloElemento.textContent = "Cargando producto...";

    try {
        // 2. Hacer la petición a PHP
        const respuesta = await fetch(`../back-end/getProduct.php?id=${encodeURIComponent(id)}`);
        if (!respuesta.ok) {
            throw new Error("Error del servidor: " + respuesta.status);
        }

        const datos = await respuesta.json();

        // 3. Verificar si PHP nos mandó un error (ej: producto no encontrado)
        if (datos.error) {
            mostrarMensajeError(`⚠️ ${datos.error}`);
            return;
        }

        console.log("Datos recibidos:", datos);

        // 4. Llenar la información del PRODUCTO en el HTML
        document.getElementById("prod-categoria").textContent = datos.categoria || "Categoría";
        document.getElementById("prod-titulo").textContent = datos.titulo || "Producto sin título";
        document.getElementById("prod-precio").textContent = formataPrecio(datos.precio);
        document.getElementById("prod-descripcion").textContent = datos.descripcion || "Este producto no tiene una descripción detallada.";

        // 5. Llenar la información del VENDEDOR en el HTML
        document.getElementById("vend-nombre").textContent = datos.nombre_vendedor || "Vendedor Desconocido";
        document.getElementById("vend-contacto").textContent = datos.contacto_vendedor || "Sin información de contacto";

        // 6. Configurar la acción del botón chat
        const btnContactar = document.getElementById("btn-contactar");
        btnContactar.onclick = () => {
            // Mandamos al usuario a chat.html con los IDs necesarios en la URL
            const urlChat = `chat.html?vendedor=${encodeURIComponent(datos.id_vendedor)}&producto=${encodeURIComponent(id)}`;
            window.location.href = urlChat;
        };

        // 7. --- MAGIA PARA LAS FOTOS ---
        // Suponiendo que 'datos.imagenes' es una cadena de URLs separadas por comas.
        // Si tienes una sola imagen en la columna 'imagen', usa datos.imagen.
        let urlsImagenes = [];

        if (datos.imagenes) {
            urlsImagenes = datos.imagenes.split(",");
        } else if (datos.imagen) {
            urlsImagenes = [datos.imagen];
        }
        mostrarImagenes(urlsImagenes);

    } catch (error) {
        console.error("Error al cargar producto:", error);
        mostrarMensajeError(`⚠️ Ocurrió un error al conectar con el servidor: ${error.message}`);
    }
}

// ------------------------------------------------------------
// Funciones auxiliares
// ------------------------------------------------------------

// Formatea un número a precio mexicano: 1200 -> "$1,200.00"
function formataPrecio(precio) {
    if (!precio || isNaN(precio)) return "$0.00";
    return "$" + parseFloat(precio).toLocaleString("es-MX", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Crea el contenedor deslizable con todas las fotos
function mostrarImagenes(imagenes) {
    const contenedorFotos = document.getElementById("prod-imagen");
    contenedorFotos.innerHTML = ""; // Limpiar el "Cargando imagen..."

    // Filtrar para quitar URLs vacías si las hay
    const imagenesValidas = imagenes.filter(url => url && url.trim() !== "");

    if (imagenesValidas.length === 0) {
        contenedorFotos.textContent = "📦 Sin imágenes";
        return;
    }

    // Configurar el contenedor para que sea deslizable horizontalmente
    contenedorFotos.style.display = "flex";
    contenedorFotos.style.overflowX = "auto";
    contenedorFotos.style.scrollSnapType = "x mandatory"; // Truco pro para que se "pegue" a la foto
    contenedorFotos.style.gap = "10px";
    contenedorFotos.style.borderRadius = "12px";

    imagenesValidas.forEach((url, index) => {
        const img = document.createElement("img");
        img.src = url.trim(); // Quitar espacios
        img.alt = `Imagen ${index + 1} del producto`;

        // Estilos para que cada imagen ocupe todo el ancho del contenedor
        img.style.width = "100%";
        img.style.height = "100%";
        img.style.objectFit = "cover"; // Se ajusta sin deformarse
        img.style.scrollSnapAlign = "start"; // Pega la orilla de la foto a la orilla del contenedor
        img.style.flexShrink = "0"; // Evita que se aplasten entre ellas

        contenedorFotos.appendChild(img);
    });
}

// Muestra un mensaje de error en la página
function mostrarMensajeError(mensaje) {
    const main = document.querySelector(".contenedor-producto");
    main.innerHTML = `
        <div style="text-align: center; color: red; padding: 40px; border: 2px dashed red; border-radius: 12px; grid-column: 1 / -1;">
            <h2>Error al cargar el producto</h2>
            <p>${mensaje}</p>
            <br>
            <button onclick="window.location.href='main.html'" style="padding: 10px 20px; cursor: pointer;">Volver al MarketPlace</button>
        </div>
    `;
}