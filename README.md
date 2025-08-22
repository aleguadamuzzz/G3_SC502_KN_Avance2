# Red de Intercambio de Alimentos - Desperdicio Cero

##  Objetivo del proyecto

Desarrollar una aplicación web colaborativa que facilite el intercambio o donación de alimentos en riesgo de ser desperdiciados. La plataforma conectará a personas, pequeños comercios y organizaciones sociales que poseen alimentos disponibles con usuarios que puedan aprovecharlos, mediante un sistema integrado de geolocalización, reputación digital y comunicación directa. Su propósito es reducir el desperdicio alimentario, fomentar la solidaridad comunitaria y promover un consumo más responsable y sostenible.

##  Descripción del sistema

La solución consiste en una plataforma web interactiva dividida en distintos módulos funcionales, pensados para facilitar el proceso de ofrecer y solicitar alimentos:

-  **Autenticación y registro**: los usuarios podrán registrarse como personas o comercios, iniciar sesión y gestionar su cuenta.
-  **Publicación de alimentos**: se podrán subir alimentos con nombre, descripción, imagen, fecha de caducidad y ubicación.
-  **Geolocalización**: los alimentos se mostrarán en un mapa y en listas, con filtros por categoría, distancia y disponibilidad.
-  **Reservas y comunicación**: los usuarios podrán solicitar alimentos, enviar mensajes internos y recibir notificaciones.
-  **Sistema de reputación**: al finalizar un intercambio, los participantes podrán calificarse mutuamente para generar confianza.
-  **Panel de usuario**: acceso al historial de publicaciones, reservas, valoraciones y configuraciones.
-  **Notificaciones**: alertas sobre alimentos cercanos, reservas activas o vencidas.
-  **Módulo de administración**: control de usuarios, moderación de contenido y estadísticas de impacto (alimentos rescatados).

## Estructura del proyecto

proyecto/
├── index.html
├── login.php
├── register.php
├── publicar.php
├── buscar.php
├── reservar.php 
├── mensajes.php 
├── panel.php
├── admin.php
├── check_session.php
├── logout.php
├── README.md
├── db.sql
├── Red de Intercambio de Alimentos.png
├── Evidencias/
├── Descipción de los modulos/
├── config/
│   └── db.php
├── css/
│   └── style.css
├── js/
│   └── script.js
└── README.md

## Validaciones implementadas

- **Contra**: Mínimo 6 caracteres.
- **Nombre de usuario**: Mínimo 3 caracteres.
- **Publicar alimento**: Nombre: obligatorio, Descripción: mínimo 5 caracteres, Fecha de caducidad: debe seleccionarse, Ubicación: formato latitud,longitud (ej. 9.9312,-84.0791).

- Estas validaciones son clientes y se por lo tanto se tienen que complementar con el servidor y ademas se pueden personalizar los mensajes en lugar de un simple alert para un diseño mas estetico.
