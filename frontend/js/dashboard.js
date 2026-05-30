const usuario = JSON.parse(localStorage.getItem("usuario"));

if (!usuario) {
    window.location.href = "login.html";
}

const menuProfesor = document.getElementById("menuProfesor");
const menuAdmin = document.getElementById("menuAdmin");

const cardNuevaReserva = document.getElementById("cardNuevaReserva");
const cardGestionClases = document.getElementById("cardGestionClases");
const cardAdmin = document.getElementById("cardAdmin");

document.getElementById("bienvenida").textContent = `Bienvenido, ${usuario.alias}`;

if (menuProfesor && usuario.roles.includes("profesor")) {
    menuProfesor.style.display = "inline-block";
}

if (menuAdmin && usuario.roles.includes("admin")) {
    menuAdmin.style.display = "inline-block";
}

if (
    cardNuevaReserva &&
    cardGestionClases &&
    usuario.roles.includes("profesor") &&
    !usuario.roles.includes("admin")
) {
    cardNuevaReserva.style.display = "none";
    cardGestionClases.style.display = "block";
}

if (cardAdmin && usuario.roles.includes("admin")) {
    cardAdmin.style.display = "block";
}

document.getElementById("btnLogout").addEventListener("click", function () {
    localStorage.removeItem("usuario");
    window.location.href = "login.html";
});