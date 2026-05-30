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

        tramosDiv.innerHTML += `
            <div class="tramo">
                <span>${tramo.hora_inicio} - ${tramo.hora_fin}</span>
                <span class="${reservado ? "tramo-reservado" : "tramo-libre"}">
                    ${reservado ? tramo.tipo_ocupacion : "libre"}
                </span>
                <button 
                    class="btn-reservar"
                    ${reservado ? "disabled" : ""}
                    onclick="reservar('${tramo.hora_inicio}', '${tramo.hora_fin}')">
                    Reservar
                </button>
            </div>
        `;
    });
}

async function reservar(horaInicio, horaFin) {
    const datosReserva = {
        id_usuario: usuario.id_usuario,
        id_pista: pistaSelect.value,
        id_deporte: deporteSelect.value,
        fecha: fechaInput.value,
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
        alert("Reserva creada correctamente");
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

iniciar();