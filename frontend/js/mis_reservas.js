const usuario = JSON.parse(localStorage.getItem("usuario"));

if (!usuario) {
    window.location.href = "login.html";
}

document.getElementById("btnLogout").addEventListener("click", function () {
    localStorage.removeItem("usuario");
    window.location.href = "login.html";
});

const reservasDiv = document.getElementById("reservas");

async function cargarReservas() {
    const respuesta = await fetch(`../../backend/routes/mis_reservas.php?id_usuario=${usuario.id_usuario}`);
    const datos = await respuesta.json();

    reservasDiv.innerHTML = "";

    if (!datos.success || datos.reservas.length === 0) {
        reservasDiv.innerHTML = "<p>No tienes reservas registradas.</p>";
        return;
    }

    datos.reservas.forEach(reserva => {
        const estadoClase = reserva.estado === "activa" ? "estado-activa" : "estado-cancelada";

        let acompanantesHtml = "Sin acompañantes";

        if (reserva.acompanantes) {
            const acompanantes = reserva.acompanantes.split(", ");

            acompanantesHtml = acompanantes.map(alias => {
                if (reserva.estado === "activa" && reserva.reservador === usuario.alias) {
                    return `
                        <span class="acompanante-item">
                            ${alias}
                            <button class="btn-quitar" onclick="quitarAcompanante(${reserva.id_reserva}, '${alias}')">
                                Quitar
                            </button>
                        </span>
                    `;
                }

                return `<span class="acompanante-item">${alias}</span>`;
            }).join("");
        }

        reservasDiv.innerHTML += `
            <article class="reserva-card">
                <h3>${reserva.deporte} - ${reserva.pista}</h3>
                <p><strong>Centro:</strong> ${reserva.centro}</p>
                <p><strong>Fecha:</strong> ${reserva.fecha}</p>
                <p><strong>Hora:</strong> ${reserva.hora_inicio} - ${reserva.hora_fin}</p>
                <p><strong>Estado:</strong> <span class="${estadoClase}">${reserva.estado}</span></p>
                <p><strong>Reservador:</strong> ${reserva.reservador}</p>

                <div class="acompanantes-lista">
                    <strong>Acompañantes:</strong>
                    <div>${acompanantesHtml}</div>
                </div>

                ${reserva.resultado || reserva.equipos || reserva.comentario_instalacion ? `
                    <div class="anotacion-box">
                        <p><strong>Equipos:</strong> ${reserva.equipos || "-"}</p>
                        <p><strong>Resultado:</strong> ${reserva.resultado || "-"}</p>
                        <p><strong>Comentario:</strong> ${reserva.comentario_instalacion || "-"}</p>
                        <p><strong>Valoración:</strong> ${reserva.valoracion || "-"}</p>
                    </div>
                ` : ""}

                ${!reserva.resultado && reserva.estado !== "cancelada" ? `
                    <div class="anotacion-form">
                        <h4>Añadir anotación</h4>

                        <label>Equipos</label>
                        <input type="text" id="equipos_${reserva.id_reserva}" placeholder="Ej: Registro2 vs Chema">

                        <label>Resultado</label>
                        <input type="text" id="resultado_${reserva.id_reserva}" placeholder="Ej: 6-4">

                        <label>Comentario instalación</label>
                        <textarea id="comentario_${reserva.id_reserva}" placeholder="Comentario sobre la pista o instalación"></textarea>

                        <label>Valoración</label>
                        <select id="valoracion_${reserva.id_reserva}">
                            <option value="">Sin valoración</option>
                            <option value="1">1 - Muy mala</option>
                            <option value="2">2 - Mala</option>
                            <option value="3">3 - Correcta</option>
                            <option value="4">4 - Buena</option>
                            <option value="5">5 - Muy buena</option>
                        </select>

                        <button onclick="crearAnotacion(${reserva.id_reserva})">
                            Guardar anotación
                        </button>
                    </div>
                ` : ""}

                ${reserva.estado === "activa" && reserva.reservador === usuario.alias ? `
                    <div class="acompanante-box">
                        <label>Añadir acompañante por alias</label>
                        <input type="text" id="alias_acompanante_${reserva.id_reserva}" placeholder="Alias del usuario">
                        <button onclick="agregarAcompanante(${reserva.id_reserva})">
                            Añadir acompañante
                        </button>
                    </div>

                    <button class="btn-cancelar" onclick="cancelarReserva(${reserva.id_reserva})">
                        Cancelar reserva
                    </button>
                ` : ""}
            </article>
        `;
    });
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
        cargarReservas();
    } else {
        alert(datos.error);
    }
}

async function agregarAcompanante(idReserva) {
    const input = document.getElementById(`alias_acompanante_${idReserva}`);
    const aliasAcompanante = input.value.trim();

    if (!aliasAcompanante) {
        alert("Introduce el alias del acompañante");
        return;
    }

    const respuesta = await fetch("../../backend/routes/agregar_acompanante.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            id_reserva: idReserva,
            id_usuario_reservador: usuario.id_usuario,
            alias_acompanante: aliasAcompanante
        })
    });

    const datos = await respuesta.json();

    if (datos.success) {
        alert("Acompañante añadido correctamente");
        cargarReservas();
    } else {
        alert(datos.error);
    }
}

async function quitarAcompanante(idReserva, aliasAcompanante) {
    if (!confirm(`¿Seguro que quieres quitar a ${aliasAcompanante} de la reserva?`)) {
        return;
    }

    const respuesta = await fetch("../../backend/routes/quitar_acompanante.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            id_reserva: idReserva,
            id_usuario_reservador: usuario.id_usuario,
            alias_acompanante: aliasAcompanante
        })
    });

    const datos = await respuesta.json();

    if (datos.success) {
        alert("Acompañante quitado correctamente");
        cargarReservas();
    } else {
        alert(datos.error);
    }
}

async function crearAnotacion(idReserva) {
    const equipos = document.getElementById(`equipos_${idReserva}`).value.trim();
    const resultado = document.getElementById(`resultado_${idReserva}`).value.trim();
    const comentario = document.getElementById(`comentario_${idReserva}`).value.trim();
    const valoracion = document.getElementById(`valoracion_${idReserva}`).value;

    const respuesta = await fetch("../../backend/routes/crear_anotacion.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            id_reserva: idReserva,
            id_usuario: usuario.id_usuario,
            equipos: equipos,
            resultado: resultado,
            comentario_instalacion: comentario,
            valoracion: valoracion ? parseInt(valoracion) : null
        })
    });

    const datos = await respuesta.json();

    if (datos.success) {
        alert("Anotación creada correctamente");
        cargarReservas();
    } else {
        alert(datos.error);
    }
}

cargarReservas();