# AOC - Millora Dret d'Accés

Aplicación web para la obtención de datos de usuarios (AOC) integrada con Oracle Database y Microsoft Dynamics 365.

## 📋 Tabla de Contenidos

- [Requisitos del Sistema](#%EF%B8%8F-requisitos-del-sistema)
- [Instalación](#-instalación)
- [Configuración](#%EF%B8%8F-configuración)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Ejecución](#-ejecución)
- [Soluciones de problemas](#-solución-de-problemas)

## 🖥️ Requisitos del Sistema

### Versiones Necesarias

- **PHP**: 7.4.33 x86
- **ZTS Visual C++**: 2017 x86
- **Oracle Instant Client**: 19.28.0.0.0 x86 linux
- **Servidor Web**: Apache o extension de Visual Studio Code: PHP Server
- **Node.js**: v22.17.0

## 📦 Instalación

### Paso 1: Clonar o Descargar el Proyecto

```bash
git clone <url-del-repositorio>
cd AOC_Millora_Dret_d-acces
```

### Paso 2: Instalar Requisitos del sistema

### Paso 3: Instalar Extensiones PHP

#### OCI8 (Conexión a Oracle)

**Windows:**
1. Descargar la DLL correspondiente a tu versión PHP (php_oci8_11g.dll)
2. Copiar `php_oci8_11g.dll` a la carpeta `ext` de PHP
3. Añadir a `php.ini` con administrador de tareas:
   ```ini
   extension=oci8_11g;
   extension=openssl;
   extension=curl;
   ```

### Paso 4: Configurar el Proyecto

1. Copiar el archivo de variables de entorno:
   ```bash
   cp .env.example .env
   ```

2. Editar el archivo `.env` con las credenciales reales (ver sección [Configuración](#configuración))

## ⚙️ Configuración

### Archivo .env

El archivo `.env` debe contener las siguientes variables. Usar como base el archivo `.env.example`:

```env
# ==========================================
# CONFIGURACIÓN DE BASE DE DATOS ORACLE
# ==========================================
DB_HOST=nombre_servidor_oracle
DB_PORT=1521
DB_SERVICE=nombre_servicio_oracle
DB_USER=usuario_bd
DB_PASS=contraseña_bd

# ==========================================
# CONFIGURACIÓN MICROSOFT/DYNAMICS
# ==========================================
MICROSOFT_ENDPOINT=https://login.microsoftonline.com
MICROSOFT_TENANT_ID=tu_tenant_id
MICROSOFT_CLIENT_ID=tu_client_id
MICROSOFT_CLIENT_SECRET=tu_client_secret
MICROSOFT_SCOPE=https://nombre.dynamics.com/.default
DYNAMICS_ENDPOINT=https://nombre.api.dynamics.com
```

Las credenciales a informar os las adjuntarán los developers de la aplicación.

- **Seguridad**: Nunca compartir el archivo `.env` con credenciales reales. Usar `.gitignore` para excluirlo del repositorio

## 📂 Estructura del Proyecto

```
source/
├── assets/              # Archivos estáticos 
│   └── CONSULTAS.txt
├── dependencies/        # Dependencias frontend
│   ├── css/            # Bootstrap CSS
│   └── js/             # Bootstrap JavaScript
├── dynamics/           # Lógica relacionada con Dynamics
│   ├── config.php      # Configuración y credenciales
│   ├── dynamics.php    # Funciones Dynamics
│   └── test-dynamics.php
├── pages/              # Páginas principales de la aplicación
│   ├── index.php       # Página principal
│   ├── informe.php     # Informes
│   └── db.php          # Funciones de BD
└── scripts/            # JavaScript de validación
    └── formValidations.js
```

## 🚀 Ejecución

### Opción 1: Con Apache (XAMPP/WAMP)

1. Copiar la carpeta del proyecto a `htdocs/` (XAMPP) o `www/` (WAMP)
2. Iniciar Apache
3. Acceder a: `http://localhost/AOC_Millora_Dret_d-acces/source/pages/`

### Opción 2: Con extensión VSC (PHP Server)

1. Click derecho sobre la pantalla de index.php y "PHP Server: serve project"
2. Se abrirá una ventana en google con el servidor levantado

## 🆘 Solución de Problemas

### Error: "OCI8 extension is not available"

1. Verificar que la extensión está instalada: `php -m | grep oci8`
2. Reiniciar el servidor web
3. Verificar la ruta del Oracle Instant Client en PATH

### Error de Conexión a Oracle

1. Verificar parámetros en `.env`

### Error de Credenciales Microsoft Dynamics

1. Verificar que Azure AD tiene la aplicación registrada
2. Comprobar que los permisos están correctamente configurados
3. Verificar que el CLIENT_SECRET no ha expirado

Cualquier problema, contactar con Maria Jose Salar Garcia o Natalia Rebeca Lara Robles