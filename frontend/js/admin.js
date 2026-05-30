const usuario = JSON.parse(localStorage.getItem("usuario"));

if (!usuario) {
    window.location.href = "login.html";
}

if (!usuario.roles.includes("admin")) {
    alert("Acceso no autorizado");
    window.location.href = "dashboard.html";
}

document.getElementById("btnLogout").addEventListener("click", function () {
    localStorage.removeItem("usuario");
    window.location.href = "login.html";
});

/* ELEMENTOS USUARIOS */

const usuariosAdminDiv = document.getElementById("usuariosAdmin");
const buscadorUsuarios = document.getElementById("buscadorUsuarios");
const filtroRol = document.getElementById("filtroRol");
const btnToggleUsuarios = document.getElementById("btnToggleUsuarios");
const panelUsuarios = document.getElementById("panelUsuarios");

let usuariosOriginales = [];

/* ELEMENTOS RESERVAS FIJAS */

const btnToggleReservasFijas = document.getElementById("btnToggleReservasFijas");
const panelReservasFijas = document.getElementById("panelReservasFijas");
const buscadorReservasFijas = document.getElementById("buscadorReservasFijas");
const reservasFijasAdminDiv = document.getElementById("reservasFijasAdmin");

const modalDetalleReservaFija = document.getElementById("modalDetalleReservaFija");
const detalleReservaFijaDiv = document.getElementById("detalleReservaFija");

const modalLiberar = document.getElementById("modalLiberar");
const idReservaFijaLiberar = document.getElementById("idReservaFijaLiberar");
const idProfesorLiberar = document.getElementById("idProfesorLiberar");
const fechaLiberar = document.getElementById("fechaLiberar");
const motivoLiberar = document.getElementById("motivoLiberar");

const modalExtra = document.getElementById("modalExtra");
const idReservaFijaExtra = document.getElementById("idReservaFijaExtra");
const idProfesorExtra = document.getElementById("idProfesorExtra");
const pistaExtraSelect = document.getElementById("pistaExtra");
const fechaExtra = document.getElementById("fechaExtra");
const horaInicioExtra = document.getElementById("horaInicioExtra");
const horaFinExtra = document.getElementById("horaFinExtra");
const observacionesExtra = document.getElementById("observacionesExtra");

let reservasFijasOriginales = [];

/* MOSTRAR / OCULTAR SECCIONES */

btnToggleUsuarios.addEventListener("click", function () {
    panelUsuarios.style.display =
        panelUsuarios.style.display === "none" ? "block" : "none";
});

btnToggleReservasFijas.addEventListener("click", function () {
    panelReservasFijas.style.display =
        panelReservasFijas.style.display === "none" ? "block" : "none";
});

/* FILTROS */

buscadorUsuarios.addEventListener("input", aplicarFiltrosUsuarios);
filtroRol.addEventListener("change", aplicarFiltrosUsuarios);
buscadorReservasFijas.addEventListener("input", aplicarFiltroReservasFijas);

/* USUARIOS */

async function cargarUsuarios() {
    const respuesta = await fetch("../../backend/routes/admin_usuarios.php");
    const datos = await respuesta.json();

    if (!datos.success || datos.usuarios.length === 0) {
        usuariosAdminDiv.innerHTML = "<p>No hay usuarios registrados.</p>";
        return;
    }

    usuariosOriginales = datos.usuarios;
    aplicarFiltrosUsuarios();
}

function aplicarFiltrosUsuarios() {
    const texto = buscadorUsuarios.value.toLowerCase().trim();
    const rolSeleccionado = filtroRol.value;

    const usuariosFiltrados = usuariosOriginales.filter(usuarioItem => {
        const textoUsuario = `
            ${usuarioItem.nombre}
            ${usuarioItem.apellidos}
            ${usuarioItem.alias}
            ${usuarioItem.email}
        `.toLowerCase();

        const coincideTexto = textoUsuario.includes(texto);
        const roles = usuarioItem.roles || "";

        const coincideRol =
            rolSeleccionado === "todos" ||
            roles.includes(rolSeleccionado);

        return coincideTexto && coincideRol;
    });

    mostrarUsuarios(usuariosFiltrados);
}

function obtenerClaseRol(roles) {
    if (!roles) {
        return "rol-usuario";
    }

    if (roles.includes("admin")) {
        return "rol-admin";
    }

    if (roles.includes("profesor")) {
        return "rol-profesor";
    }

    return "rol-usuario";
}

function mostrarUsuarios(usuarios) {
    usuariosAdminDiv.innerHTML = "";

    if (usuarios.length === 0) {
        usuariosAdminDiv.innerHTML = "<p>No se encontraron usuarios.</p>";
        return;
    }

    usuarios.forEach(usuarioItem => {
        const estadoTexto = usuarioItem.activo == 1 ? "Activo" : "Inactivo";
        const estadoClase = usuarioItem.activo == 1 ? "estado-activa" : "estado-cancelada";
        const claseRol = obtenerClaseRol(usuarioItem.roles);

        usuariosAdminDiv.innerHTML += `
            <article class="reserva-card usuario-card ${claseRol}">
                <h3>${usuarioItem.nombre} ${usuarioItem.apellidos}</h3>

                <p><strong>Alias:</strong> ${usuarioItem.alias}</p>
                <p><strong>Email:</strong> ${usuarioItem.email}</p>
                <p><strong>Roles:</strong> ${usuarioItem.roles || "Sin rol"}</p>
                <p>
                    <strong>Estado:</strong>
                    <span class="${estadoClase}">${estadoTexto}</span>
                </p>

                <button onclick="cambiarEstadoUsuario(${usuarioItem.id_usuario}, ${usuarioItem.activo == 1 ? 0 : 1})">
                    ${usuarioItem.activo == 1 ? "Desactivar" : "Activar"}
                </button>
            </article>
        `;
    });
}

async function cambiarEstadoUsuario(idUsuario, nuevoEstado) {
    if (!confirm("¿Seguro que quieres cambiar el estado de este usuario?")) {
        return;
    }

    const respuesta = await fetch("../../backend/routes/cambiar_estado_usuario.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            id_usuario: idUsuario,
            activo: nuevoEstado
        })
    });

    const datos = await respuesta.json();

    if (datos.success) {
        alert("Estado actualizado correctamente");
        await cargarUsuarios();
    } else {
        alert(datos.error);
    }
}

/* RESERVAS FIJAS */

async function cargarReservasFijas() {
    const respuesta = await fetch("../../backend/routes/reservas_fijas.php");
    const datos = await respuesta.json();

    if (!datos.success || datos.reservas_fijas.length === 0) {
        reservasFijasAdminDiv.innerHTML = "<p>No existen reservas fijas.</p>";
        return;
    }

    reservasFijasOriginales = datos.reservas_fijas;
    aplicarFiltroReservasFijas();
}

function aplicarFiltroReservasFijas() {
    const texto = buscadorReservasFijas.value.toLowerCase().trim();

    const reservasFiltradas = reservasFijasOriginales.filter(reserva => {
        const textoReserva = `
            ${reserva.nombre}
            ${reserva.apellidos}
            ${reserva.alias}
            ${reserva.pista}
            ${reserva.deporte}
            ${reserva.dia_semana}
            ${reserva.observaciones}
        `.toLowerCase();

        return textoReserva.includes(texto);
    });

    mostrarReservasFijas(reservasFiltradas);
}

function mostrarReservasFijas(reservas) {
    reservasFijasAdminDiv.innerHTML = "";

    if (reservas.length === 0) {
        reservasFijasAdminDiv.innerHTML = "<p>No se encontraron reservas fijas.</p>";
        return;
    }

    reservas.forEach(reserva => {
        const estadoClase =
            reserva.activa == 1 ? "estado-activa-fija" : "estado-inactiva-fija";

        reservasFijasAdminDiv.innerHTML += `
            <article class="reserva-fija-card">
                <h4>${reserva.deporte}</h4>

                <p><strong>Profesor:</strong> ${reserva.nombre} ${reserva.apellidos} (${reserva.alias})</p>
                <p><strong>Pista:</strong> ${reserva.pista}</p>
                <p><strong>Día:</strong> ${reserva.dia_semana}</p>
                <p><strong>Horario:</strong> ${reserva.hora_inicio} - ${reserva.hora_fin}</p>
                <p><strong>Vigencia:</strong> ${reserva.fecha_inicio} → ${reserva.fecha_fin}</p>
                <p>
                    <strong>Estado:</strong>
                    <span class="estado-reserva-fija ${estadoClase}">
                        ${reserva.activa == 1 ? "Activa" : "Inactiva"}
                    </span>
                </p>
                <p><strong>Observaciones:</strong> ${reserva.observaciones || "-"}</p>

                ${reserva.activa == 1 ? `
                    <button onclick="verDetalleReservaFija(${reserva.id_reserva_fija})">
                        Ver detalle
                    </button>

                    <button onclick="abrirLiberacion(${reserva.id_reserva_fija}, ${reserva.id_profesor})">
                        Liberar clase
                    </button>

                    <button onclick="abrirReservaExtra(
                        ${reserva.id_reserva_fija},
                        ${reserva.id_profesor},
                        ${reserva.id_centro},
                        ${reserva.id_deporte},
                        ${reserva.id_pista}
                    )">
                        Crear recuperación
                    </button>

                    <button onclick="desactivarReservaFija(${reserva.id_reserva_fija})">
                        Desactivar reserva fija
                    </button>
                ` : ""}

            </article>
        `;
    });
}

document.getElementById("btnCerrarDetalleReservaFija").addEventListener("click", function () {
    modalDetalleReservaFija.style.display = "none";
});

async function verDetalleReservaFija(idReservaFija) {
    const respuesta = await fetch(
        `../../backend/routes/detalle_reserva_fija.php?id_reserva_fija=${idReservaFija}`
    );

    const datos = await respuesta.json();

    if (!datos.success) {
        alert(datos.error);
        return;
    }

    const reserva = datos.reserva_fija;

    let excepcionesHtml = "<p>No hay clases liberadas.</p>";

    if (datos.excepciones.length > 0) {
        excepcionesHtml = datos.excepciones.map(excepcion => `
            <div class="detalle-item">
                <p><strong>Fecha:</strong> ${excepcion.fecha}</p>
                <p><strong>Motivo:</strong> ${excepcion.motivo || "-"}</p>
            </div>
        `).join("");
    }

    let extrasHtml = "<p>No hay recuperaciones registradas.</p>";

    if (datos.reservas_extra.length > 0) {
        extrasHtml = datos.reservas_extra.map(extra => `
            <div class="detalle-item">
                <p><strong>Fecha:</strong> ${extra.fecha}</p>
                <p><strong>Horario:</strong> ${extra.hora_inicio} - ${extra.hora_fin}</p>
                <p><strong>Pista:</strong> ${extra.pista}</p>
                <p><strong>Observaciones:</strong> ${extra.observaciones || "-"}</p>
            </div>
        `).join("");
    }

    detalleReservaFijaDiv.innerHTML = `
        <div class="detalle-bloque">
            <p><strong>Profesor:</strong> ${reserva.nombre} ${reserva.apellidos} (${reserva.alias})</p>
            <p><strong>Deporte:</strong> ${reserva.deporte}</p>
            <p><strong>Pista:</strong> ${reserva.pista}</p>
            <p><strong>Día:</strong> ${reserva.dia_semana}</p>
            <p><strong>Horario:</strong> ${reserva.hora_inicio} - ${reserva.hora_fin}</p>
            <p><strong>Vigencia:</strong> ${reserva.fecha_inicio} → ${reserva.fecha_fin}</p>
        </div>

        <hr>

        <h4>Clases liberadas</h4>
        ${excepcionesHtml}

        <hr>

        <h4>Recuperaciones</h4>
        ${extrasHtml}
    `;

    modalDetalleReservaFija.style.display = "flex";
}

function abrirLiberacion(idReservaFija, idProfesor) {
    idReservaFijaLiberar.value = idReservaFija;
    idProfesorLiberar.value = idProfesor;
    fechaLiberar.value = "";
    motivoLiberar.value = "";

    modalLiberar.style.display = "flex";
}

document.getElementById("btnCancelarLiberar").addEventListener("click", function () {
    modalLiberar.style.display = "none";
});

document.getElementById("btnConfirmarLiberar").addEventListener("click", liberarClase);

async function liberarClase() {
    const respuesta = await fetch("../../backend/routes/liberar_reserva_fija.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            id_reserva_fija: idReservaFijaLiberar.value,
            id_profesor: idProfesorLiberar.value,
            fecha: fechaLiberar.value,
            motivo: motivoLiberar.value
        })
    });

    const resultado = await respuesta.json();

    if (resultado.success) {
        alert("Clase liberada correctamente");
        modalLiberar.style.display = "none";
        await cargarReservasFijas();
    } else {
        alert(resultado.error);
    }
}

async function abrirReservaExtra(idReservaFija, idProfesor, idCentro, idDeporte, idPistaOriginal) {
    idReservaFijaExtra.value = idReservaFija;
    idProfesorExtra.value = idProfesor;

    fechaExtra.value = "";
    horaInicioExtra.value = "";
    horaFinExtra.value = "";
    observacionesExtra.value = "";

    const respuesta = await fetch(
        `../../backend/routes/pistas.php?id_centro=${idCentro}&id_deporte=${idDeporte}`
    );

    const datos = await respuesta.json();

    pistaExtraSelect.innerHTML = "";

    datos.pistas.forEach(pista => {
        pistaExtraSelect.innerHTML += `
            <option value="${pista.id_pista}" ${pista.id_pista == idPistaOriginal ? "selected" : ""}>
                ${pista.nombre_pista}
            </option>
        `;
    });

    pistaExtraSelect.disabled = datos.pistas.length <= 1;

    modalExtra.style.display = "flex";
}

document.getElementById("btnCancelarExtra").addEventListener("click", function () {
    modalExtra.style.display = "none";
});

document.getElementById("btnConfirmarExtra").addEventListener("click", crearReservaExtra);

async function crearReservaExtra() {
    const respuesta = await fetch("../../backend/routes/reserva_extra_clase.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            id_reserva_fija: idReservaFijaExtra.value,
            id_profesor: idProfesorExtra.value,
            id_pista: pistaExtraSelect.value,
            fecha: fechaExtra.value,
            hora_inicio: horaInicioExtra.value,
            hora_fin: horaFinExtra.value,
            observaciones: observacionesExtra.value
        })
    });

    const resultado = await respuesta.json();

    if (resultado.success) {
        alert("Recuperación creada correctamente");
        modalExtra.style.display = "none";
        await cargarReservasFijas();
    } else {
        alert(resultado.error);
    }
}

async function desactivarReservaFija(idReservaFija) {

    if (!confirm("¿Seguro que deseas desactivar esta reserva fija?")) {
        return;
    }

    const respuesta = await fetch(
        "../../backend/routes/desactivar_reserva_fija.php",
        {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                id_reserva_fija: idReservaFija
            })
        }
    );

    const resultado = await respuesta.json();

    if (resultado.success) {

        alert("Reserva fija desactivada correctamente");

        await cargarReservasFijas();

    } else {

        alert(resultado.error);
    }
}

/* INICIO */


cargarUsuarios();
cargarReservasFijas();