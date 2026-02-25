# AOC - Millora Dret d'Accés

Aplicació web per a l'obtenció de dades d'usuaris (AOC) integrada amb Oracle Database i Microsoft Dynamics 365.

## 📋 Taula de Continguts

- [Requisits del Sistema](#%EF%B8%8F-requisits-del-sistema)
- [Instal·lació](#-installació)
- [Configuració](#%EF%B8%8F-configuració)
- [Estructura del Projecte](#-estructura-del-projecte)
- [Execució](#-execució)
- [Solucions de Problemes](#-solució-de-problemes)

## 🖥️ Requisits del Sistema

### Versions Necessàries

- **PHP**: 7.4.33 x86
- **ZTS Visual C++**: 2017 x86
- **Oracle Instant Client**: 19.28.0.0.0 x86 linux
- **Servidor Web**: Apache o extensió de Visual Studio Code: PHP Server
- **Node.js**: v22.17.0

## 📦 Instal·lació

### Pas 1: Clonar o Descarregar el Projecte

```bash
git clone <url-del-repositori>
cd AOC_Millora_Dret_d-acces
```

### Pas 2: Instal·lar Requisits del Sistema

### Pas 3: Instal·lar Extensions PHP

#### OCI8 (Connexió a Oracle)

**Windows:**
1. Descarregar la DLL corresponent a la teva versió PHP (php_oci8_11g.dll)
2. Copiar `php_oci8_11g.dll` a la carpeta `ext` de PHP
3. Afegir a `php.ini` amb administrador de tasques:
   ```ini
   extension=oci8_11g;
   extension=openssl;
   extension=curl;
   ```

### Pas 4: Configurar el Projecte

1. Copiar l'arxiu de variables d'entorn:
   ```bash
   cp .env.example .env
   ```

2. Editar l'arxiu `.env` amb les credencials reals (veure secció [Configuració](#%EF%B8%8F-configuració))

## ⚙️ Configuració

### Arxiu .env

L'arxiu `.env` ha de contenir les següents variables. Utilitzar com a base l'arxiu `.env.example`:

```env
# ==========================================
# CONFIGURACIÓ DE BASE DE DADES ORACLE
# ==========================================
DB_HOST=nom_servidor_oracle
DB_PORT=1521
DB_SERVICE=nom_servei_oracle
DB_USER=usuari_bd
DB_PASS=contrasenya_bd

# ==========================================
# CONFIGURACIÓ MICROSOFT/DYNAMICS
# ==========================================
MICROSOFT_ENDPOINT=https://login.microsoftonline.com
MICROSOFT_TENANT_ID=tu_tenant_id
MICROSOFT_CLIENT_ID=tu_client_id
MICROSOFT_CLIENT_SECRET=tu_client_secret
MICROSOFT_SCOPE=https://nom.dynamics.com/.default
DYNAMICS_ENDPOINT=https://nom.api.dynamics.com
```

Les credencials a informar us les adjuntaran els developers de l'aplicació.

- **Seguretat**: Mai compartir l'arxiu `.env` amb credencials reals. Utilitzar `.gitignore` per excloure'l del repositori

## 📂 Estructura del Projecte

```
source/
├── assets/              # Arxius estàtics 
│   └── CONSULTAS.txt
├── dependencies/        # Dependències frontend
│   ├── css/            # Bootstrap CSS
│   └── js/             # Bootstrap JavaScript
├── dynamics/           # Lògica relacionada amb Dynamics
│   ├── config.php      # Configuració i credencials
│   ├── dynamics.php    # Funcions Dynamics
│   └── test-dynamics.php
├── pages/              # Pàgines principals de l'aplicació
│   ├── index.php       # Pàgina principal
│   ├── informe.php     # Informes
│   └── db.php          # Funcions de BD
└── scripts/            # JavaScript de validació
    └── formValidations.js
```

## 🚀 Execució

### Opció 1: Amb Apache (XAMPP/WAMP)

1. Copiar la carpeta del projecte a `htdocs/` (XAMPP) o `www/` (WAMP)
2. Iniciar Apache
3. Accedir a: `http://localhost/AOC_Millora_Dret_d-acces/source/pages/`

### Opció 2: Amb extensió VSC (PHP Server)

1. Click dret sobre la pantalla de index.php i "PHP Server: serve project"
2. S'obrirà una finestra en Google amb el servidor llençat

## 🆘 Solució de Problemes

### Error: "OCI8 extension is not available"

1. Verificar que l'extensió està instal·lada: `php -m | grep oci8`
2. Reiniciar el servidor web
3. Verificar la ruta del Oracle Instant Client a PATH

### Error de Connexió a Oracle

1. Verificar paràmetres a `.env`

### Error de Credencials Microsoft Dynamics

1. Verificar que Azure AD té l'aplicació registrada
2. Comprovar que els permisos estan correctament configurats
3. Verificar que el CLIENT_SECRET no ha expirat

Per a qualsevol problema, contactar amb Maria Jose Salar Garcia o Natalia Rebeca Lara Robles