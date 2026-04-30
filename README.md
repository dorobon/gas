# Gasóleos y Gasolinas

Portal Laravel 12 orientado a consultar precios de carburantes en España con varias secciones públicas:

- portada con precios medios nacionales
- ranking de gasolineras más baratas
- histórico por carburante y provincia
- buscador de estaciones
- fichas individuales con últimos precios
- sección editorial de informes

El proyecto usa SQLite por defecto y puede funcionar con datos demo o con importación oficial del Ministerio.

## Qué incluye

- Laravel 12
- SQLite por defecto para desarrollo ligero
- esquema SQL propio en `database/sql/sqlite/`
- referencia MySQL en `database/sql/mysql/`
- importación oficial desde XLS con fallback REST JSON
- scheduler diario a las `07:00`
- endpoint protegido para importación manual
- tests funcionales del portal

## Rutas principales

- `/` → precios actuales
- `/baratas` → gasolineras más baratas
- `/historico` → evolución histórica
- `/gasolineras` → buscador
- `/gasolineras/{id}` → ficha de estación
- `/informes` → noticias e informes

## Arranque rápido

### 1. Instalar dependencias

En este entorno Windows conviene usar `composer.bat` si `composer` no está en `PATH` de la shell actual.

```bash
composer install --no-interaction --prefer-dist --ignore-platform-req=ext-gd
```

### 2. Preparar entorno

```bash
copy .env.example .env
```

El proyecto ya viene preparado para SQLite:

- `DB_CONNECTION=sqlite`
- `DB_DATABASE=database/database.sqlite`

Si el archivo no existe, Laravel lo creará cuando arranque el bootstrap del dominio.

### 3. Verificar extensiones PHP

El portal necesita al menos:

- `pdo_sqlite`
- `pdo_mysql` si se quiere usar MySQL más adelante
- `curl`
- `mbstring`
- `zip`

Puedes comprobarlo con:

```bash
php -m
```

Si `pdo_sqlite` no aparece, revisa `php.ini`.

### 4. Ejecutar la app

```bash
php artisan serve
```

## Esquema de base de datos

No se han añadido migrations nuevas para el dominio de carburantes. En su lugar:

- SQLite: `database/sql/sqlite/fuel_portal_schema.sql`
- MySQL: `database/sql/mysql/fuel_portal_schema.sql`

El arranque automático del esquema lo gestiona:

- `App\Libraries\Fuel\FuelDataBootstrapLibrary`

Además, en `local` y `testing`, si no hay datos se cargan datos demo automáticamente mediante:

- `App\Libraries\Fuel\FuelDemoDataLibrary`

## Importación oficial

La importación soporta dos fuentes oficiales:

- XLS: `https://geoportalgasolineras.es/resources/files/preciosEESS_es.xls`
- REST JSON: `https://sedeaplicaciones.minetur.gob.es/ServiciosRESTCarburantes/PreciosCarburantes/EstacionesTerrestres/`

La estrategia por defecto es `auto`:

1. intenta XLS
2. si el XLS falla o no se puede parsear, cae automáticamente al REST JSON

Variables relevantes en `.env`:

```dotenv
PRICE_IMPORT_TOKEN=change-me-before-production
PRICE_IMPORT_SOURCE=https://geoportalgasolineras.es/resources/files/preciosEESS_es.xls
PRICE_IMPORT_REST_SOURCE=https://sedeaplicaciones.minetur.gob.es/ServiciosRESTCarburantes/PreciosCarburantes/EstacionesTerrestres/
PRICE_IMPORT_STRATEGY=auto
```

### Importación por consola

```bash
php artisan fuel:import
php artisan fuel:import --source=rest
php artisan fuel:import --source=xls
```

También puedes indicar una URL alternativa:

```bash
php artisan fuel:import --url="https://..."
```

### Importación por API

Endpoint protegido:

- `POST /api/update-prices`

Admite:

- header `X-Import-Token`
- body `token`
- body `source=auto|xls|rest`

## Comandos útiles

```bash
php artisan fuel:about
php artisan fuel:seed-demo --refresh
php artisan fuel:import --source=auto
```

## Scheduler

La importación diaria queda registrada en `routes/console.php`:

- `Schedule::command(ImportFuelPricesCommand::class)->dailyAt('07:00');`

En producción necesitarás el scheduler habitual de Laravel.

## Testing

Suite validada con PHPUnit.

```bash
php artisan test
```

Si tu entorno CLI pierde SQLite por configuración local, revisa `php.ini` antes de ejecutar los tests.

## Estructura relevante

### Dominio

- `app/Models/GasStation.php`
- `app/Models/Price.php`
- `app/Repositories/`
- `app/Libraries/Fuel/`

### HTTP

- `app/Http/Controllers/PriceController.php`
- `app/Http/Controllers/StationController.php`
- `app/Http/Controllers/ReportController.php`
- `app/Http/Controllers/ImportController.php`
- `app/Http/Controllers/Api/StationPriceController.php`

### Vistas

- `resources/views/layouts/app.blade.php`
- `resources/views/prices/*`
- `resources/views/stations/*`
- `resources/views/reports/*`

## Notas operativas

- `PhpSpreadsheet` se usa para leer el XLS oficial.
- Para evitar romper el bootstrap en shells problemáticas, la app comprueba SQLite antes de inicializar el esquema automático.
- Si no hay datos importados, la demo mantiene la UI navegable y testeable.

## Licencia

Proyecto basado en Laravel, mantenido dentro de este repositorio para la web de precios de carburantes.
