document.getElementById("formLogin").addEventListener("submit", async function (e) {
    e.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();
    const mensaje = document.getElementById("mensaje");

    mensaje.textContent = "";
    mensaje.className = "mensaje";

    try {
        const respuesta = await fetch("../../backend/routes/login.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                email: email,
                password: password
            })
        });

        const datos = await respuesta.json();

        if (datos.success) {
            localStorage.setItem("usuario", JSON.stringify(datos.usuario));

            mensaje.textContent = "Login correcto. Redirigiendo...";
            mensaje.classList.add("ok");

            setTimeout(() => {
                window.location.href = "dashboard.html";
            }, 1000);
        } else {
            mensaje.textContent = datos.error;
            mensaje.classList.add("error");
        }

    } catch (error) {
        mensaje.textContent = "Error de conexión con el servidor";
        mensaje.classList.add("error");
    }
});