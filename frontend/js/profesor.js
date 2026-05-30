const usuario = JSON.parse(localStorage.getItem("usuario"));

if (!usuario) {
    window.location.href = "login.html";
}

/* ELEMENTOS DEL DOM */
const profesorSelect = document.getElementById("profesor");
const profesorTexto = document.getElementById("profesorTexto");
const pistaSelect = document.getElementById("pista");
const deporteSelect = document.getElementById("deporte");
const resultadoDiv = document.getElementById("resultado");
const reservasFijasDiv = document.getElementById("reservasFijas");

const modalLiberar = document.getElementById("modalLiberar");
const fechaLiberar = document.getElementById("fechaLiberar");
const motivoLiberar = document.getElementById("motivoLiberar");
const idReservaFijaLiberar = document.getElementById("idReservaFijaLiberar");

const modalExtra = document.getElementById("modalExtra");
const idReservaFijaExtra = document.getElementById("idReservaFijaExtra");
const pistaExtraSelect = document.getElementById("pistaExtra");
const fechaExtra = document.getElementById("fechaExtra");
const horaInicioExtra = document.getElementById("horaInicioExtra");
const horaFinExtra = document.getElementById("horaFinExtra");
const observacionesExtra = document.getElementById("observacionesExtra");

const modalDetalleReservaFija = document.getElementById("modalDetalleReservaFija");
const detalleReservaFijaDiv = document.getElementById("detalleReservaFija");

/* COMPROBAR PERMISOS */
if (!usuario.roles.includes("profesor") && !usuario.roles.includes("admin")) {
    alert("Acceso no autorizado");
    window.location.href = "dashboard.html";
}

/* LOGOUT */
document.getElementById("btnLogout").addEventListener("click", function () {
    localStorage.removeItem("usuario");
    window.location.href = "login.html";
});

/* CARGAR PROFESORES */
async function cargarProfesores() {
    const respuesta = await fetch("../../backend/routes/profesores.php");
    const datos = await respuesta.json();

    profesorSelect.innerHTML = "";

    datos.profesores.forEach(profesor => {
        profesorSelect.innerHTML += `
            <option value="${profesor.id_usuario}">
                ${profesor.nombre} ${profesor.apellidos} (${profesor.alias})
            </option>
        `;
    });
}

/* CARGAR DEPORTES */
async function cargarDeportes() {
    const respuesta = await fetch("../../backend/routes/deportes.php");
    const datos = await respuesta.json();

    deporteSelect.innerHTML = "";

    datos.deportes.forEach(deporte => {
        deporteSelect.innerHTML += `
            <option value="${deporte.id_deporte}">
                ${deporte.nombre}
            </option>
        `;
    });
}

/* CARGAR PISTAS */
async function cargarPistas() {
    const respuesta = await fetch("../../backend/routes/pistas.php");
    const datos = await respuesta.json();

    pistaSelect.innerHTML = "";

    datos.pistas.forEach(pista => {
        pistaSelect.innerHTML += `
            <option value="${pista.id_pista}">
                ${pista.nombre_pista}
            </option>
        `;
    });
}

/* CREAR RESERVA FIJA */
async function crearReservaFija() {
    const idProfesor = usuario.roles.includes("admin")
        ? profesorSelect.value
        : usuario.id_usuario;

    const datos = {
        id_profesor: idProfesor,
        id_pista: pistaSelect.value,
        id_deporte: deporteSelect.value,
        dia_semana: document.getElementById("diaSemana").value,
        hora_inicio: document.getElementById("horaInicio").value,
        hora_fin: document.getElementById("horaFin").value,
        fecha_inicio: document.getElementById("fechaInicio").value,
        fecha_fin: document.getElementById("fechaFin").value,
        observaciones: document.getElementById("observaciones").value
    };

    const respuesta = await fetch("../../backend/routes/crear_reserva_fija.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(datos)
    });

    const resultado = await respuesta.json();

    resultadoDiv.innerHTML = `
        <pre>${JSON.stringify(resultado, null, 4)}</pre>
    `;

    if (resultado.success) {
        await cargarReservasFijas();
    }
}

document
    .getElementById("btnCrearReservaFija")
    .addEventListener("click", crearReservaFija);

/* CARGAR RESERVAS FIJAS */
async function cargarReservasFijas() {
    const respuesta = await fetch("../../backend/routes/reservas_fijas.php");
    const datos = await respuesta.json();

    reservasFijasDiv.innerHTML = "";

    if (!datos.success || datos.reservas_fijas.length === 0) {
        reservasFijasDiv.innerHTML = "<p>No existen reservas fijas.</p>";
        return;
    }

    datos.reservas_fijas.forEach(reserva => {
        const estadoClase = reserva.activa == 1
            ? "estado-activa-fija"
            : "estado-inactiva-fija";

        reservasFijasDiv.innerHTML += `
            <div class="reserva-fija-card">
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

                <p><strong>Observaciones:</strong> ${reserva.observaciones ?? "-"}</p>

                <button onclick="verDetalleReservaFija(${reserva.id_reserva_fija})">
                    Ver detalle
                </button>

                ${reserva.activa == 1 ? `
                    <button onclick="abrirLiberacion(${reserva.id_reserva_fija})">
                        Liberar clase
                    </button>

                    <button onclick="abrirReservaExtra(
                        ${reserva.id_reserva_fija},
                        ${reserva.id_centro},
                        ${reserva.id_deporte},
                        ${reserva.id_pista}
                    )">
                        Crear recuperación
                    </button>
                ` : ""}
            </div>
        `;
    });
}

/* LIBERAR RESERVA FIJA */
function abrirLiberacion(idReservaFija) {
    idReservaFijaLiberar.value = idReservaFija;
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
            id_profesor: usuario.id_usuario,
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

/* CREAR RESERVA EXTRA */
async function abrirReservaExtra(idReservaFija, idCentro, idDeporte, idPistaOriginal) {
    idReservaFijaExtra.value = idReservaFija;

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
            id_profesor: usuario.id_usuario,
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

/* INICIALIZACIÓN */
async function iniciar() {
    if (usuario.roles.includes("admin")) {
        profesorSelect.style.display = "block";
        profesorTexto.style.display = "none";
        await cargarProfesores();
    } else {
        profesorSelect.style.display = "none";
        profesorTexto.style.display = "block";
        profesorTexto.textContent = `${usuario.nombre} ${usuario.apellidos}`;
    }

    await cargarDeportes();
    await cargarPistas();
    await cargarReservasFijas();
}

document
    .getElementById("btnCerrarDetalleReservaFija")
    .addEventListener("click", function () {
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

iniciar();