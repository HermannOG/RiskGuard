# RiskGuard

Consultoría en administración de riesgos de seguridad de la información,
con evaluación de riesgos de base de datos, login (empresa/admin) y panel
de administración.

## Estructura

Todo vive en la raíz del repo (sin subcarpetas anidadas tipo `src/`):

- `index.php`, `login.php`, `logout.php`, `registro.php`, `evaluacion-riesgos.php`
- `admin-evaluaciones.php`, `admin-evaluacion-detalle.php` — panel de admin
- `crear-admin.php` — crea el primer admin (bórralo después de usarlo)
- `includes/` — helpers PHP: `db.php` (conexión PDO), `auth.php` (sesión),
  `EmpresaRepository.php`, `EvaluacionRepository.php`, `UsuarioRepository.php`,
  `header.php`, `footer.php`, `navbar.php`, `i18n.php`, `lang/`, `cuestionario-data.php`
- `api/guardar-evaluacion.php` — guarda una evaluación completada
- `assets/` — CSS y JS
- `database/schema.sql` — esquema completo + catálogo de controles/preguntas

## Despliegue en InfinityFree

### 1. Base de datos

En phpMyAdmin (panel de InfinityFree), en una base de datos **vacía**,
importa `database/schema.sql` completo (pestaña "Importar").

### 2. Credenciales

Copia `includes/config.example.php` como `includes/config.php` (mismo
folder) y coloca ahí el host/usuario/password/nombre de base de datos
reales que muestra el panel "MySQL Connection Details" de InfinityFree.
Ese archivo **no se sube a git** (está en `.gitignore`) — hay que crearlo
manualmente en el servidor (Online File Manager) o subirlo por FTP aparte,
nunca por `git push`.

### 3. Primer administrador


Sube el sitio, entra a `tudominio.com/crear-admin.php`, crea tu usuario
admin, y **borra ese archivo del servidor** por seguridad.

### 4. Despliegue automático por GitHub Actions (opcional)

El repo incluye `.github/workflows/deploy.yml`, que sube el sitio por FTP
en cada push a `main`. Debes configurar estos Secrets en
`Settings → Secrets and variables → Actions` de tu repo de GitHub:

| Secret | Valor |
|---|---|
| `FTP_SERVER` | el host FTP de InfinityFree (ej. `ftpupload.net`) |
| `FTP_USERNAME` | tu usuario FTP de InfinityFree |
| `FTP_PASSWORD` | tu contraseña FTP de InfinityFree |

Importante: como este workflow sube por `git push`, y `includes/config.php`
NO está en git, ese archivo **nunca se sube automáticamente**. Debes
subirlo una sola vez a mano por FTP (o crearlo en el Online File Manager)
y no lo vuelvas a tocar desde el repo.


Revisa también `server-dir` dentro de `deploy.yml`: si tu dominio nuevo
no es el dominio principal de la cuenta de InfinityFree, la ruta cambia
(consulta el Online File Manager para confirmar la ruta real de tu
dominio antes del primer despliegue).

