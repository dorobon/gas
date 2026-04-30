## AGENTS.md — Instrucciones para agentes (Laravel ligero + SQLite)

Propósito: proveer a agentes de IA instrucciones concisas y acciones seguras para trabajar en un proyecto Laravel "pelado" orientado a respuesta rápida y pocos recursos, con base de datos SQLite.

Contexto rápido
- Stack esperado: PHP 8.1+, Composer, extensión PDO_SQLITE, Laravel 9/10 (compatible).
- BBDD por defecto: SQLite (archivo en `database/database.sqlite`).

Inicio rápido (mínimo)

1. Instalar dependencias

```bash
composer install --no-interaction --prefer-dist
cp .env.example .env
```

2. Configurar SQLite (archivo de DB)

```bash
mkdir -p database
type nul > database\database.sqlite   # Windows: crea archivo vacío
# en Unix: touch database/database.sqlite
```

En `.env`:

```
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

3. Generar key y migrar

```bash
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

Consejos para entornos de pocos recursos
- Usar `php artisan serve` y SQLite evita necesidad de servicios adicionales (MySQL, Redis) para la mayoría de tareas locales.
- Cache driver: `file` (por defecto) o `array` para pruebas rápidas.
- Mail driver: `log` o `null` para no depender de servicios externos.
- No arrancar workers ni procesos en background durante desarrollo ligero.

Testing
- Para tests rápidos y aislados, usar SQLite en memoria en `phpunit.xml` o `php artisan test`:

```xml
<!-- en phpunit.xml: -->
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Recomendaciones para agentes
- Antes de ejecutar tests o migraciones, asegúrate de que `database/database.sqlite` exista cuando uses archivo en disco.
- Evitar operaciones pesadas como `vendor:publish` de paquetes grandes sin confirmación.
- Si se editan rutas (`routes/api.php` o `routes/web.php`), invalidar/actualizar caché de rutas: `php artisan route:clear` y `php artisan route:cache` si se desea cache en producción.

Puntos comunes y debugs rápidos
- En Windows, algunos comandos interactivos o policies pueden requerir `npm.cmd` o ajustes del ExecutionPolicy; preferir comandos no interactivos en scripts.
- Si los tests fallan por rutas 404 tras cambiar `routes/api.php`, limpiar caché de rutas (ver arriba).
- Verificar que la extensión PDO_SQLITE esté habilitada en PHP.
- Permisos: `storage` y `bootstrap/cache` deben ser escribibles; en Windows suele bastar con permisos de usuario actual.

Links útiles
- Laravel docs: https://laravel.com/docs
- SQLite: https://www.sqlite.org/index.html

Comportamiento esperado del agente
- Preferir comandos que no requieran infraestructura adicional.
- Documentar cualquier cambio que cambie la arquitectura (añadir Redis, Horizon, colas, etc.).
- Pedir confirmación antes de ejecutar migraciones destructivas en entornos no marcados como `testing`.

Feedback
Si quieres que añada plantillas para CI (GitHub Actions), instrucciones para Docker ligero, o un `setup.sh`/`setup.ps1` para automatizar la creación del `database.sqlite`, dímelo y lo genero.
