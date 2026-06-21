# RedSky Ecosystem - Memoria del Proyecto

## 📌 Arquitectura General

El ecosistema está dividido en tres proyectos principales:

### 1. redsky-framework
Framework base tipo Laravel construido desde cero en PHP.

- Contiene solo el núcleo del sistema (core)
- No tiene punto de entrada (NO public/index.php)
- No tiene bootstrap ni config de aplicación
- Se distribuye como paquete Composer

Responsabilidades:
- Container (inyección de dependencias)
- Kernel (ciclo de request/response)
- Router
- Middleware pipeline
- HTTP layer (Request/Response)
- Database layer (QueryBuilder, Connection, Grammars)
- Support utilities

---

### 2. redsky-api
Aplicación backend que utiliza el framework.

- Es el punto de ejecución real del sistema
- Contiene el entry point

Estructura:
- public/index.php → punto de entrada
- bootstrap/app.php → inicialización del framework
- config/ → configuración de la aplicación
- routes/ → definición de rutas
- .env → variables de entorno
- composer.json → dependencia de redsky-framework

Responsabilidad:
- Arrancar el framework
- Resolver dependencias del framework
- Ejecutar Kernel

---

### 3. redsky-ui
Frontend separado (SSR o UI helpers)

- Consume redsky-framework indirectamente vía API o helpers
- Independiente del backend

---

## 🔁 Flujo de ejecución
