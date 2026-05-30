# PistaLibre

Aplicación web para la reserva de pistas deportivas.

## Descripción

PistaLibre permite consultar la disponibilidad de instalaciones deportivas, realizar reservas, gestionar acompañantes, añadir anotaciones al historial y administrar reservas fijas de profesores o entrenadores.

## Tecnologías utilizadas

- HTML5
- CSS3
- JavaScript
- PHP
- MySQL/MariaDB
- Apache
- Postman
- Git / GitHub

## Estructura del proyecto

```text
pistalibre/
├── backend/
│   ├── config/
│   ├── controllers/
│   ├── models/
│   └── routes/
├── frontend/
│   ├── assets/
│   ├── css/
│   ├── js/
│   └── pages/
└── README.md

```

## Configuración

Crear una base de datos llamada:

```sql
pistalibre
```

Crear el archivo:

```text
backend/config/conexion.php
```

tomando como referencia:

```text
backend/config/conexion.example.php
```

## Usuarios de prueba

```text
Admin:
Email: jmnunnezg04@educarex.es
Contraseña: chema1234

Profesor:
Email: profesor1@test.com
Contraseña: 123456

Usuario:
Email: Registro2@test.com
Contraseña: 123456
```

## Funcionalidades principales

- Registro e inicio de sesión.
- Consulta de centros, deportes y pistas.
- Consulta de disponibilidad.
- Creación y cancelación de reservas.
- Gestión de acompañantes.
- Anotaciones en reservas.
- Panel de profesor.
- Reservas fijas.
- Liberación de clases.
- Recuperaciones de clases.
- Panel de administración.
- Activación y desactivación de usuarios.
- Gestión de reservas fijas desde administración.

## Autor

Jose Maria Nuñez Gonzalez