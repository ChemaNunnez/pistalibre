# PistaLibre

Aplicación web para la gestión y reserva de instalaciones deportivas.

## Descripción

PistaLibre permite a los usuarios consultar la disponibilidad de pistas deportivas, realizar reservas, gestionar acompañantes, registrar anotaciones de partidos y consultar su historial de reservas.

Además, incorpora funcionalidades específicas para profesores y administradores, permitiendo gestionar clases periódicas, liberar sesiones, programar recuperaciones y administrar usuarios del sistema.

## Aplicación desplegada

La aplicación se encuentra disponible en:

https://pistalibre.infinityfreeapp.com

## Tecnologías utilizadas

- HTML5
- CSS3
- JavaScript (Vanilla JS)
- PHP 8
- MySQL / MariaDB
- Apache
- Postman
- Git
- GitHub

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
├── .gitignore
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

Configurar los datos de conexión correspondientes a la base de datos local.

## Usuarios de prueba

```text
Administrador
Email: administrador1@test.com
Contraseña: admin123

Profesor
Email: profesor1@test.com
Contraseña: 123456

Usuario
Email: Registro2@test.com
Contraseña: 123456
```

## Funcionalidades principales

### Usuarios

- Registro de nuevos usuarios.
- Inicio y cierre de sesión.
- Consulta de centros deportivos.
- Consulta de deportes disponibles.
- Consulta de pistas deportivas.
- Consulta de disponibilidad por fecha.
- Navegación entre días mediante controles de fecha.
- Creación de reservas.
- Cancelación de reservas.
- Gestión de acompañantes.
- Registro de anotaciones deportivas.
- Consulta del historial de reservas.

### Profesores

- Gestión de clases.
- Creación de reservas fijas.
- Liberación de clases.
- Programación de recuperaciones.
- Cancelación de recuperaciones.
- Consulta detallada del historial de clases y recuperaciones.

### Administración

- Gestión de usuarios.
- Activación y desactivación de cuentas.
- Consulta de reservas fijas.
- Gestión de clases y recuperaciones.
- Acceso a funcionalidades de profesor.

## API REST

El backend expone distintos endpoints PHP para:

- Autenticación.
- Gestión de usuarios.
- Gestión de reservas.
- Gestión de acompañantes.
- Gestión de anotaciones.
- Consulta de disponibilidad.
- Gestión de reservas fijas.
- Gestión de recuperaciones.

Todos los endpoints han sido probados mediante Postman.

## Control de versiones

El proyecto utiliza Git para el control de versiones y se encuentra alojado en GitHub.

Repositorio:

https://github.com/ChemaNunnez/pistalibre

## Autor

Jose Maria Nuñez Gonzalez

Proyecto desarrollado como trabajo final del ciclo formativo de Desarrollo de Aplicaciones Web (DAW).