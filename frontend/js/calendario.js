const usuario = JSON.parse(localStorage.getItem("usuario"));

if (!usuario) {
    window.location.href = "login.html";
}

document.getElementById("btnLogout").addEventListener("click", function () {
    localStorage.removeItem("usuario");
    window.location.href = "login.html";
});

const centroSelect = document.getElementById("centro");
const deporteSelect = document.getElementById("deporte");
const pistaSelect = document.getElementById("pista");
const fechaInput = document.getElementById("fecha");
const tramosDiv = document.getElementById("tramos");

const menuProfesor = document.getElementById("menuProfesor");
const menuAdmin = document.getElementById("menuAdmin");

const modalAcompanantes = document.getElementById("modalAcompanantes");
const idReservaAcompanante = document.getElementById("idReservaAcompanante");
const aliasAcompananteReserva = document.getElementById("aliasAcompananteReserva");
const listaAcompanantesReserva = document.getElementById("listaAcompanantesReserva");

const modalLiberarClase = document.getElementById("modalLiberarClase");
const tipoClaseLiberar = document.getElementById("tipoClaseLiberar");
const idReservaFijaLiberar = document.getElementById("idReservaFijaLiberar");
const idReservaExtraLiberar = document.getElementById("idReservaExtraLiberar");
const idProfesorLiberar = document.getElementById("idProfesorLiberar");
const fechaClaseLiberar = document.getElementById("fechaClaseLiberar");
const textoClaseLiberar = document.getElementById("textoClaseLiberar");
const motivoClaseLiberar = document.getElementById("motivoClaseLiberar");

if (menuProfesor && usuario.roles.includes("profesor")) {
    menuProfesor.style.display = "inline-block";
}

if (menuAdmin && usuario.roles.includes("admin")) {
    menuAdmin.style.display = "inline-block";
}

async function cargarCentros() {
    const respuesta = await fetch("../../backend/routes/centros.php");
    const datos = await respuesta.json();

    centroSelect.innerHTML = "";
    datos.centros.forEach(centro => {
        centroSelect.innerHTML += `
            <option value="${centro.id_centro}">${centro.nombre}</option>
        `;
    });
}

async function cargarDeportes() {
    const respuesta = await fetch("../../backend/routes/deportes.php");
    const datos = await respuesta.json();

    deporteSelect.innerHTML = "";
    datos.deportes.forEach(deporte => {
        deporteSelect.innerHTML += `
            <option value="${deporte.id_deporte}">${deporte.nombre}</option>
        `;
    });
}

async function cargarPistas() {
    const idCentro = centroSelect.value;
    const idDeporte = deporteSelect.value;

    const respuesta = await fetch(`../../backend/routes/pistas.php?id_centro=${idCentro}&id_deporte=${idDeporte}`);
    const datos = await respuesta.json();

    pistaSelect.innerHTML = "";

    if (datos.pistas.length === 0) {
        pistaSelect.innerHTML = `<option value="">No hay pistas disponibles</option>`;
        return;
    }

    datos.pistas.forEach(pista => {
        pistaSelect.innerHTML += `
            <option value="${pista.id_pista}">${pista.nombre_pista}</option>
        `;
    });
}

async function consultarDisponibilidad() {
    const idPista = pistaSelect.value;
    const fecha = fechaInput.value;

    if (!idPista || !fecha) {
        alert("Selecciona pista y fecha");
        return;
    }

    const respuesta = await fetch(`../../backend/routes/disponibilidad.php?id_pista=${idPista}&fecha=${fecha}`);
    const datos = await respuesta.json();

    tramosDiv.innerHTML = "";

    if (datos.cerrado) {
        tramosDiv.innerHTML = "<p>El centro está cerrado ese día.</p>";
        return;
    }

datos.tramos.forEach(tramo => {

    const reservado = tramo.estado === "reservado";

    const fechaHoraTramo = new Date(`${fecha}T${tramo.hora_inicio}`);
    const ahora = new Date();
    const pasado = fechaHoraTramo < ahora;

    const esProfesor = usuario.roles.includes("profesor") && !usuario.roles.includes("admin");

    let textoEstado = "Libre";
    let claseEstado = "tramo-libre";
    let botonDesactivado = "";
    let textoBoton = "Reservar";
    let accionBoton = `reservar('${tramo.hora_inicio}', '${tramo.hora_fin}')`;

    if (reservado) {
        claseEstado = "tramo-reservado";
        botonDesactivado = "disabled";
        textoBoton = "No disponible";

        if (
            tramo.tipo_ocupacion === "reserva_fija" ||
            tramo.tipo_ocupacion === "reserva_extra_clase"
        ) {
            textoEstado = `Reservada clase<br><small>${tramo.ocupado_por || ""}</small>`;
        } else {
            textoEstado = `Reservada<br><small>${tramo.ocupado_por || ""}</small>`;
        }

        if (
            tramo.tipo_ocupacion === "reserva_normal" &&
            tramo.id_usuario_reservador == usuario.id_usuario &&
            !pasado
        ) {
            botonDesactivado = "";
            textoBoton = "Cancelar reserva";
            accionBoton = `cancelarReserva(${tramo.id_reserva})`;
        }

        const puedeLiberarClase =
        (
            usuario.roles.includes("admin") ||
            tramo.id_profesor == usuario.id_usuario
        ) &&
        (
            tramo.tipo_ocupacion === "reserva_fija" ||
            tramo.tipo_ocupacion === "reserva_extra_clase"
        ) &&
        !pasado;

    if (puedeLiberarClase) {
        botonDesactivado = "";
        textoBoton = "Liberar clase";
        accionBoton = `abrirLiberarClase(
            '${tramo.tipo_ocupacion}',
            ${tramo.id_reserva_fija || "null"},
            ${tramo.id_reserva_extra || "null"},
            ${tramo.id_profesor},
            '${fecha}',
            '${tramo.hora_inicio}',
            '${tramo.hora_fin}'
        )`;
    }

    }

    if (pasado) {
        textoEstado = "No disponible";
        claseEstado = "tramo-reservado";
        botonDesactivado = "disabled";
        textoBoton = "No disponible";
    }

    if (esProfesor && !reservado && !pasado) {
        textoEstado = "Solo gestión de clases";
        claseEstado = "tramo-reservado";
        botonDesactivado = "disabled";
        textoBoton = "Solo clases";
    }

    tramosDiv.innerHTML += `
        <div class="tramo">
            <span>${tramo.hora_inicio} - ${tramo.hora_fin}</span>

            <span class="${claseEstado}">
                ${textoEstado}
            </span>

            <button 
                class="btn-reservar"
                ${botonDesactivado}
                onclick="${accionBoton}">
                ${textoBoton}
            </button>
        </div>
    `;
});
}

async function reservar(horaInicio, horaFin) {

    const fechaSeleccionada = fechaInput.value;

    if (!fechaSeleccionada) {
        alert("Selecciona una fecha");
        return;
    }

    const fechaHoraReserva = new Date(`${fechaSeleccionada}T${horaInicio}`);
    const ahora = new Date();

    if (fechaHoraReserva < ahora) {
        alert("No se pueden realizar reservas en fechas u horas pasadas");
        return;
    }

    const datosReserva = {
        id_usuario: usuario.id_usuario,
        id_pista: pistaSelect.value,
        id_deporte: deporteSelect.value,
        fecha: fechaSeleccionada,
        hora_inicio: horaInicio,
        hora_fin: horaFin
    };

    const respuesta = await fetch("../../backend/routes/crear_reserva.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(datosReserva)
    });

    const datos = await respuesta.json();

    if (datos.success) {
        idReservaAcompanante.value = datos.id_reserva;
        aliasAcompananteReserva.value = "";
        listaAcompanantesReserva.innerHTML = "";
        modalAcompanantes.style.display = "flex";

        consultarDisponibilidad();
    } else {
        alert(datos.error);
    }
}

async function cancelarReserva(idReserva) {

    if (!confirm("¿Seguro que quieres cancelar esta reserva?")) {
        return;
    }

    const respuesta = await fetch("../../backend/routes/cancelar_reserva.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            id_reserva: idReserva,
            id_usuario: usuario.id_usuario
        })
    });

    const datos = await respuesta.json();

    if (datos.success) {
        alert("Reserva cancelada correctamente");
        consultarDisponibilidad();
    } else {
        alert(datos.error);
    }
}

function abrirLiberarClase(tipo, idReservaFija, idReservaExtra, idProfesor, fecha, horaInicio, horaFin) {
    tipoClaseLiberar.value = tipo;
    idReservaFijaLiberar.value = idReservaFija || "";
    idReservaExtraLiberar.value = idReservaExtra || "";
    idProfesorLiberar.value = idProfesor;
    fechaClaseLiberar.value = fecha;
    motivoClaseLiberar.value = "";

    textoClaseLiberar.textContent = `${fecha} - ${horaInicio.substring(0,5)} - ${horaFin.substring(0,5)}`;

    modalLiberarClase.style.display = "flex";
}

document.getElementById("btnCancelarLiberarClase").addEventListener("click", function () {
    modalLiberarClase.style.display = "none";
});

document.getElementById("btnConfirmarLiberarClase").addEventListener("click", liberarClaseDesdeDisponibilidad);

async function liberarClaseDesdeDisponibilidad() {
    let respuesta;

    if (tipoClaseLiberar.value === "reserva_extra_clase") {
        respuesta = await fetch("../../backend/routes/cancelar_reserva_extra_clase.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                id_reserva_extra: idReservaExtraLiberar.value
            })
        });
    } else {
        respuesta = await fetch("../../backend/routes/liberar_reserva_fija.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                id_reserva_fija: idReservaFijaLiberar.value,
                id_profesor: idProfesorLiberar.value,
                fecha: fechaClaseLiberar.value,
                motivo: motivoClaseLiberar.value
            })
        });
    }

    const datos = await respuesta.json();

    if (datos.success) {
        alert("Clase liberada correctamente");
        modalLiberarClase.style.display = "none";
        consultarDisponibilidad();
    } else {
        alert(datos.error);
    }
}

centroSelect.addEventListener("change", cargarPistas);
deporteSelect.addEventListener("change", cargarPistas);
document.getElementById("btnConsultar").addEventListener("click", consultarDisponibilidad);

async function iniciar() {
    await cargarCentros();
    await cargarDeportes();
    await cargarPistas();
}

document.getElementById("btnCerrarAcompanantes").addEventListener("click", function () {
    modalAcompanantes.style.display = "none";
    consultarDisponibilidad();
});

document.getElementById("btnAgregarAcompananteReserva").addEventListener("click", agregarAcompananteDesdeReserva);

async function agregarAcompananteDesdeReserva() {
    const alias = aliasAcompananteReserva.value.trim();

    if (!alias) {
        alert("Introduce el alias del acompañante");
        return;
    }

    const respuesta = await fetch("../../backend/routes/agregar_acompanante.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            id_reserva: idReservaAcompanante.value,
            id_usuario_reservador: usuario.id_usuario,
            alias_acompanante: alias
        })
    });

    const datos = await respuesta.json();

    if (datos.success) {
        listaAcompanantesReserva.innerHTML += `
            <p class="mensaje ok">Añadido: ${datos.acompanante.alias}</p>
        `;

        aliasAcompananteReserva.value = "";
    } else {
        alert(datos.error);
    }
}

function cambiarDia(cantidad) {
    if (!fechaInput.value) {
        const hoy = new Date();
        fechaInput.value = hoy.toISOString().split("T")[0];
    }

    const fecha = new Date(fechaInput.value);
    fecha.setDate(fecha.getDate() + cantidad);

    fechaInput.value = fecha.toISOString().split("T")[0];

    consultarDisponibilidad();
}

document.getElementById("btnDiaAnterior").addEventListener("click", function () {
    cambiarDia(-1);
});

document.getElementById("btnDiaSiguiente").addEventListener("click", function () {
    cambiarDia(1);
});

iniciar();