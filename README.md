# Factu - Sistema de Facturación Electrónica (CFDI)

**Factu** es un sistema integral de facturación electrónica diseñado para automatizar la emisión de Comprobantes Fiscales Digitales por Internet (CFDI) en México. El proyecto actúa como un puente entre sistemas operativos (Hoteles y Restaurantes) y el Proveedor Autorizado de Certificación (PAC) **Facturama**.

## 🚀 Características Principales

*   **Emisión de CFDI 4.0:** Integración completa con la API de **Facturama** para el timbrado de facturas, notas de crédito y complementos de pago.
*   **Integración con Cloudbeds:** Módulo especializado para extraer datos de reservaciones directamente desde la API de **Cloudbeds**, facilitando la facturación para el sector hotelero.
*   **Módulo Pcbrestaurant:** Soporte dedicado para la gestión de facturación proveniente de consumos en sistemas de restaurantes.
*   **Procesamiento en Segundo Plano:** Uso de **Laravel Jobs y Queues** para el timbrado de facturas, asegurando una experiencia de usuario fluida sin tiempos de espera prolongados.
*   **Notificaciones Automatizadas:** Envío automático de archivos XML y PDF a clientes y administradores mediante correos electrónicos personalizados.
*   **Gestión Fiscal:** Catálogos actualizados de regímenes fiscales, usos de CFDI y métodos de pago según los estándares del SAT.

## 🛠️ Stack Tecnológico

*   **Backend:** [Laravel 12.x](https://laravel.com/) (PHP 8.2+)
*   **Frontend:** [Vue.js 3](https://vuejs.org/) con [Inertia.js](https://inertiajs.com/)
*   **Estilos:** [Tailwind CSS 4.0](https://tailwindcss.com/)
*   **Herramienta de Construcción:** [Vite](https://vitejs.dev/)
*   **Base de Datos:** MySQL / MariaDB
*   **Integraciones:** Facturama API, Cloudbeds API

## 📋 Requisitos

*   PHP >= 8.2
*   Composer
*   Node.js & NPM
*   MySQL / MariaDB

## 🔧 Instalación y Configuración

El proyecto incluye un script de configuración automática para agilizar el despliegue:

1.  **Clonar el repositorio:**
    ```bash
    git clone <url-del-repositorio>
    cd factu
    ```

2.  **Ejecutar el script de configuración:**
    Este comando instalará dependencias (PHP y JS), generará la clave de la aplicación, ejecutará las migraciones y compilará los assets:
    ```bash
    composer run setup
    ```

3.  **Configurar variables de entorno:**
    Edita el archivo `.env` con tus credenciales de base de datos y servicios externos:
    *   `FACTURAMA_USER` y `FACTURAMA_PASSWORD`
    *   `CLOUDBEDS_API_KEY`
    *   Configuración de correo (SMTP)

4.  **Iniciar servidores de desarrollo:**
    ```bash
    composer run dev
    ```

## 📁 Estructura del Proyecto

*   `app/Services/Facturama`: Lógica de comunicación con el PAC.
*   `app/Services/CloudbedsService.php`: Integración con la API de hotelería.
*   `app/Jobs/ProcessFacturamaInvoice.php`: Tarea asíncrona para el timbrado.
*   `resources/js/Pages`: Componentes de la interfaz de usuario en Vue.
*   `routes/web.php`: Definición de rutas y endpoints del sistema.

---
Desarrollado por PCBTRONIKS para la gestión fiscal eficiente.
