const usuario = JSON.parse(localStorage.getItem("usuario"));

if (!usuario) {
    window.location.href = "login.html";
}

document.getElementById("bienvenida").textContent = `Bienvenido, ${usuario.alias}`;
document.getElementById("rolUsuario").textContent = `${usuario.roles.join(", ")}`;

if (usuario.roles && usuario.roles.includes("profesor")) {
    document.getElementById("cardProfesor").style.display = "block";
}

if (usuario.roles && usuario.roles.includes("admin")) {
    document.getElementById("cardAdmin").style.display = "block";
}

document.getElementById("btnLogout").addEventListener("click", function () {
    localStorage.removeItem("usuario");
    window.location.href = "login.html";
});