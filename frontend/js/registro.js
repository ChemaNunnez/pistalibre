document.getElementById("formRegistro").addEventListener("submit", async function (e) {
    e.preventDefault();

    const mensaje = document.getElementById("mensaje");

    const datosRegistro = {
        nombre: document.getElementById("nombre").value.trim(),
        apellidos: document.getElementById("apellidos").value.trim(),
        alias: document.getElementById("alias").value.trim(),
        email: document.getElementById("email").value.trim(),
        password: document.getElementById("password").value.trim()
    };

    mensaje.textContent = "";
    mensaje.className = "mensaje";

    try {
        const respuesta = await fetch("../../backend/routes/registro.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(datosRegistro)
        });

        const datos = await respuesta.json();

        if (datos.success) {
            mensaje.textContent = "Registro correcto. Redirigiendo al login...";
            mensaje.classList.add("ok");

            setTimeout(() => {
                window.location.href = "login.html";
            }, 1200);
        } else {
            mensaje.textContent = datos.error;
            mensaje.classList.add("error");
        }

    } catch (error) {
        mensaje.textContent = "Error de conexión con el servidor";
        mensaje.classList.add("error");
    }
});