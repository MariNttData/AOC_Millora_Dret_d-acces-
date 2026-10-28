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

### Pas 2: Instal·lar PHP i les extensions necessàries (Windows)

1. Descarregar PHP 8.5.12 per a Windows x86, Thread Safe (TS), VS17 des de [PHP for Windows](https://windows.php.net/download/). Extreure'l, per exemple, a `C:\Program Files (x86)\php-8.5.12-1-Win32-vs17-x86`.
2. Instal·lar el [Microsoft Visual C++ Redistributable 2015-2022](https://learn.microsoft.com/cpp/windows/latest-supported-vc-redist) de 32 bits (x86).
3. Crear `php.ini` a la carpeta de PHP copiant-hi `php.ini-development`. Editar-lo com a administrador i configurar la carpeta `ext` i les extensions necessàries:
   ```ini
   extension_dir = "C:\Program Files (x86)\php-8.5.12-1-Win32-vs17-x86\ext"
   extension=curl
   extension=openssl
   extension=php_oci8_19.dll
   ```
4. Descarregar OCI8 3.4.0 des de [PECL - OCI8 per a Windows](https://pecl.php.net/package/oci8/3.4.0/windows). Escollir exactament el paquet **PHP 8.5, Thread Safe (TS), x86, VS17** (`php_oci8-3.4.0-8.5-ts-vs17-x86.zip`) i copiar `php_oci8_19.dll` a la carpeta `ext` de PHP indicada a `extension_dir`.
5. Descarregar el paquet **Basic** d'Oracle Instant Client 21.23 per a [Windows de 32 bits](https://www.oracle.com/database/technologies/instant-client/microsoft-windows-32-downloads.html). Descomprimir-lo, per exemple, a `C:\oracle\instantclient_21_23`. Afegir al `Path` de Windows la carpeta que conté `oci.dll` (la carpeta extreta `instantclient_21_23`, no la carpeta pare).
6. Després de modificar el `Path`, tancar i tornar a obrir VS Code/terminals i reiniciar el servidor web perquè hereti el canvi.
7. Obrir una terminal nova i comprovar que PHP carrega les extensions i troba Oracle:
   ```powershell
   php --ini
   php -m | findstr /i "curl openssl oci8"
   php --ri oci8
   where.exe oci.dll
   ```
   `php --ini` ha d'indicar el `php.ini` creat al pas 3; `php --ri oci8` ha de mostrar `OCI8 Support => enabled` i `where.exe oci.dll` ha de mostrar primer la ruta de l'Instant Client nou.

> Si utilitzes PHP Server, XAMPP, WAMP o Apache, comprova també el `php.ini` i la versió de PHP que utilitza el servidor web: poden ser diferents dels de la terminal. Reinicia el servidor després de canviar la configuració.

### Pas 3: Configurar el Projecte

1. Copiar `source/.env.example` com a `source/.env` (a PowerShell, des de l'arrel del projecte):
   ```powershell
   Copy-Item source\.env.example source\.env
   ```
2. Editar `source/.env` amb les credencials reals (veure secció [Configuració](#%EF%B8%8F-configuració)).

## ⚙️ Configuració

### Arxiu .env

L'arxiu `.env` ha de contenir les següents variables. Utilitzar com a base l'arxiu `.env.example`:

```env
# ==========================================
# CONFIGURACIÓ DE BASE DE DADES ORACLE
# ==========================================
DB_HOST=nom_servidor_oracle*
DB_PORT=1521*
DB_SERVICE=nom_servei_oracle*
DB_USER=usuari_bd*
DB_PASS=contrasenya_bd*

# ==========================================
# CONFIGURACIÓ MICROSOFT/DYNAMICS
# ==========================================
MICROSOFT_ENDPOINT=https://login.microsoftonline.com*
MICROSOFT_TENANT_ID=tu_tenant_id*
MICROSOFT_CLIENT_ID=tu_client_id*
MICROSOFT_CLIENT_SECRET=tu_client_secret*
MICROSOFT_SCOPE=https://nom.dynamics.com/.default*
DYNAMICS_ENDPOINT=https://nom.api.dynamics.com*
```

*Les credencials a informar en .env es poden trobar en la carpeta SP: C:\Users\user\Consorci Administració Oberta de Catalunya\Operacions - SUPORT_TECNIC\SOFTWARE\MilloraDretdacces

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

### Opció 1: Amb el servidor integrat de PHP (recomanat per al desenvolupament)

Des de l'arrel del projecte, en una terminal nova:

```powershell
php -S localhost:3000 -t source
```

Accedir a `http://localhost:3000/pages/index.php`. Aturar el servidor amb `Ctrl+C`.

### Opció 2: Amb Apache (XAMPP/WAMP) o l'extensió PHP Server de VS Code

Configurar el servidor perquè utilitzi la mateixa instal·lació de PHP 8.5.12, el mateix `php.ini` i el `Path` de l'Oracle Instant Client. Copiar la carpeta del projecte a `htdocs/` (XAMPP) o `www/` (WAMP), iniciar Apache i accedir a `http://localhost/AOC_Millora_Dret_d-acces/source/pages/index.php`. Si s'utilitza PHP Server, iniciar-lo des de `source` i comprovar el PHP que executa.

## 🆘 Solució de Problemes

### Error: "OCI8 extension is not available" o "Unable to load dynamic library"

1. Comprovar que `extension=php_oci8_19.dll` està activa al `php.ini` que carrega el servidor (`php --ini` per a la terminal; `source/dynamics/diagnostico.php` per al servidor web).
2. Comprovar que la DLL és a la carpeta `ext` d'aquella instal·lació de PHP i que correspon a PHP 8.5 TS x86 VS17.
3. Comprovar que `where.exe oci.dll` troba primer la carpeta de l'Instant Client per a Windows x86. La carpeta afegida al `Path` ha de contenir `oci.dll`.
4. Tancar i tornar a obrir VS Code/terminals i reiniciar el servidor web. El `Path` modificat només a `$env:Path` afecta la terminal actual.
5. Verificar amb `php --ri oci8` i tornar a carregar `http://localhost:3000/dynamics/diagnostico.php` si el servidor web encara no carrega OCI8 quan s'inicia des de l'arrel del projecte amb el servidor integrat.

### Error de Connexió a Oracle

1. Verificar paràmetres a `.env`

### Error de Credencials Microsoft Dynamics

1. Verificar que Azure AD té l'aplicació registrada
2. Comprovar que els permisos estan correctament configurats
3. Verificar que el CLIENT_SECRET no ha expirat

Per a qualsevol problema, contactar amb OTSuportTecnic@aoc.cat , nlararob@emeal.nttdata.com o msalarga@emeal.nttdata.com
