# Diseño de la web de precios de carburantes (estilo Dieselogasolina)

El portal **Dieselogasolina.com** (Diésel o Gasolina) ofrece varias secciones orientadas a informar sobre los **precios de los carburantes** en España. A continuación se describen las secciones relevantes centradas en precios, junto con la propuesta de arquitectura (base de datos, modelos, controladores, tareas programadas, plantillas Blade) para desarrollar una web similar con Laravel y SQLite. Se incluyen también consideraciones de contenido y SEO para cada sección.

## Sección “Precios actuales de carburantes” (página principal)
Esta sección muestra los **precios medios nacionales actuales** de los distintos tipos de carburantes (gasolinas y gasóleos) y suele indicar cómo evolucionan respecto al día anterior. Por ejemplo, el encabezado puede listar valores como “Gasóleo A: 1,738 €/l (ayer 1,736 €)”, “Gasolina 95 E10: 1,529 €/l (ayer 1,500 €)”, etc. Debajo se suele incluir un **aviso legal** del tipo “*Estos datos pueden variar a medida que las gasolineras notifiquen sus precios al sistema durante el día*” para indicar que las cifras son dinámicas. Esta página sirve como resumen destacado de los precios de hoy en España.  

- **Contenido SEO:** Incluir palabras clave como “precio gasolina hoy”, “precio diésel España”, “evolución precios carburantes”, etc. Un título meta podría ser “Precio de la gasolina y diésel hoy en España – Diésel o Gasolina” y la descripción hablar de la consulta diaria de precios medios (introduciendo términos como “gasolineras”, “€/litro”, “promedio nacional”). Por ejemplo: “Consulta el precio de la gasolina y el diésel hoy en España. Datos actualizados de los carburantes más comunes para ayudarte a encontrar la mejor oferta en tu próxima repostada.”  
- **Información mostrada:** Listado tabulado o gráfico con cada carburante (Gasolina 95, Gasolina 98, Gasóleo A, Gasóleo B, etc.) y sus precios actuales, además de la comparación con días anteriores. Puede incluir un **gráfico comparativo** de los últimos días. 

## Sección “Gasolineras más baratas (hoy)”
Aquí se listan las **estaciones de servicio con los precios más bajos** en un área determinada. Se suele permitir filtrar por provincia, localidad o marca. Por ejemplo, una caja de filtros permite seleccionar “Provincia” (por defecto “Toda España”), “Marca” (Repsol, Cepsa, etc.) y “Tipo de carburante” (Gasolina 95, Gasóleo A, etc.). Al aplicar filtros, se muestran las gasolineras ordenadas de menor a mayor precio para ese combustible. Cada entrada indica nombre/rotulo de la gasolinera, dirección, horario y el precio vigente. A menudo se muestran por separado las listas de **gasolinas más baratas** y **diésel más baratos**.

- **Contenido SEO:** Optimizar para “gasolineras baratas [provincia]” o “gasolina barata [ciudad]”. Por ejemplo: “Descubre las gasolineras más baratas de Madrid con Gasolina 95. Filtro por marca y localidad para ahorrar en cada repostaje.” Incluir también “precios actualizados”, “ahorro en combustible”, etc.  
- **Elementos de la página:** Filtros desplegables (provincia, localidad, marca), opciones de ordenación (precio ascendente, más cercanas), y lista de resultados. En móvil es útil permitir “Cerca de mí” usando geolocalización. Cada resultado puede enlazar a la ficha detallada de esa gasolinera (ver sección Buscador más abajo).

## Sección “Histórico de precios”
Esta sección permite al usuario consultar la **evolución histórica** de precios de carburantes. Por ejemplo, seleccionar un mes/año o un rango de fechas y una provincia, y mostrar gráficos de líneas o barras con la evolución del precio medio diario o mensual. También puede incluir gráficos predefinidos como “gráfico del mes actual” o comparativas año a año. Se apoyaría en los datos almacenados en la base de datos con las capturas históricas de precios (véase la sección de descarga de Excel).  

- **Contenido SEO:** Palabras clave como “histórico precio gasolina España”, “gráfica evolución carburantes”, “histórico diésel” etc. Ejemplo de descripción: “Consulta el histórico de precios de la gasolina y el diésel en España desde 2011 hasta hoy. Visualiza gráficos por provincias y analiza tendencias de precios de los carburantes.”  
- **Elementos de la página:** Selectores de fecha, provincia, tipo de carburante, y gráficos dinámicos. También se puede ofrecer descarga de datos históricos o enlaces a recursos gubernamentales (por ejemplo, fuente oficial).

## Sección “Buscador de Gasolineras”
Aunque es un buscador general, incluye datos de precios de cada estación. Esta sección permite **buscar cualquier gasolinera de España** (más de 9.000 estaciones) por provincia, municipio, marca o nombre. Al buscar, se muestra una lista o mapa de gasolineras con sus datos: nombre, dirección, coordenadas, horarios, tipos de servicio y, sobre todo, los **precios de los carburantes** que venden. Por tanto, aunque la funcionalidad central es la búsqueda de estaciones, también es relevante para precios. 

- **Contenido SEO:** “buscador gasolineras España”, “gasolineras cerca de mí”, “precios gasolina estacione” etc. Título meta ejemplo: “Buscador de gasolineras en España – precios y ubicación” y descripción: “Encuentra fácilmente las gasolineras que necesitas. Busca por provincia, localidad o marca y consulta sus precios de gasolina y diésel actualizados.”  
- **Elementos de la página:** Barra de búsqueda/autocompletar, filtros por marca/tipo de combustible, resultados con enlace a fichas individuales (mostrando precios por combustible, horarios, formas de pago, etc.), e integración con mapa interactivo (por ejemplo, Google Maps o Leaflet) para localizar cada gasolinera.

## Sección “Noticias / Informes de precios”
En Dieselogasolina existe un apartado de noticias (blog) donde se publican **informes y novedades** sobre los precios de los carburantes (subidas impositivas, medidas gubernamentales, análisis de tendencias). Si se incluye, sería una “Sección de Informes” o “Actualidad de carburantes”, con artículos cronológicos. Aunque es más editorial, guarda relación con el tema de precios. Se presentaría como un archivo o blog temático. 

- **Contenido SEO:** Incluir temáticas de actualidad (“aumento del gasóleo”, “previsión precio petróleo”, “impuestos carburantes”, etc.). Ejemplo descripción: “Últimas noticias y análisis sobre los precios de la gasolina y el diésel en España. Infórmate de factores que influyen en el precio de los carburantes.”  
- **Elementos de la página:** Listado de posts con fecha, extracto, y enlace a detalle. Sidebar con categorías (“Precios Carburantes”, “Tendencias”, etc.). 

## Base de datos (SQLite)

La base de datos deberá almacenar la información estructurada de las estaciones y sus precios. Usando SQLite como motor, se puede diseñar al menos dos tablas principales:

- **Tabla `stations` (estaciones de servicio)**: contiene los datos estáticos de cada gasolinera. Campos sugeridos:
  - `id` (integer, PK, autoincrement)
  - `province` (text) – nombre o código de provincia. *(Ej: “ALBACETE”)*.
  - `municipality` (text) – municipio. *(Ej: “ALATOZ”)*.
  - `locality` (text) – localidad (pueblo/barrios). *(Ej: “ABENGIBRE”)*.
  - `postal_code` (text) – código postal. *(Ej: “02250”)*.
  - `address` (text) – dirección completa. *(Ej: “AVENIDA CASTILLA LA MANCHA, 26”)*【19†L1693-L1702】.
  - `margin` (text) – margen de la carretera (valores “D”=derecho, “I”=izquierdo, “N”=no aplica)【19†L1693-L1702】.
  - `longitude` (float) – coordenada geográfica longitud【19†L1693-L1702】.
  - `latitude` (float) – coordenada geográfica latitud【19†L1693-L1702】.
  - `brand` (text) – rótulo o nombre comercial (p. ej. “REPSOL”)【19†L1709-L1718】.
  - `sale_type` (text) – tipo de venta (“P” público general o “R” restringida)【19†L1714-L1718】.
  - `provider` (text) – origen datos (OM = mayorista, dm = minorista)【19†L1714-L1718】.
  - `hours` (text) – horario de apertura (“L-D: 7:00-23:00” etc.)【19†L1714-L1718】.
  - `service_type` (text) – tipo de servicio (“P” asistido, “A” autoservicio con personal, “D” sin personal)【19†L1714-L1718】.
  
- **Tabla `prices` (precios históricos)**: cada fila guarda los precios de la estación en un momento dado. Campos sugeridos:
  - `id` (integer, PK, autoincrement)
  - `station_id` (integer, FK a `stations.id`)
  - `collected_at` (datetime) – fecha y hora de captura de datos (p. ej. “2026-04-16 15:00”)【19†L1693-L1702】. 
  - Precios de carburantes (numéricos con decimales, p. ej. decimal(6,3)):
    - `gas95_e5` – Gasolina 95 E5【19†L1693-L1699】.
    - `gas95_e10` – Gasolina 95 E10【19†L1696-L1700】.
    - `gas95_e5_premium` – Gasolina 95 E5 Premium【19†L1697-L1700】.
    - `gas98_e5` – Gasolina 98 E5【19†L1699-L1701】.
    - `gas98_e10` – Gasolina 98 E10【19†L1699-L1701】.
    - `diesel_a` – Gasóleo A【19†L1700-L1702】.
    - `diesel_premium` – Gasóleo Premium【19†L1701-L1704】.
    - `diesel_b` – Gasóleo B【19†L1703-L1705】.
    - `diesel_c` – Gasóleo C【19†L1704-L1705】.
    - `bioethanol` – Bioetanol (el precio del E85)【19†L1705-L1707】.
    - `pct_bio` – % biocombustible en bioetanol【19†L1705-L1707】.
    - `biodiesel` – Biodiésel (Éster metílico)【19†L1707-L1709】.
    - `pct_ester` – % éster metílico en el biodiésel【19†L1707-L1709】.
    - `glp` – Gases licuados del petróleo (GPL)【19†L1710-L1713】.
    - `gnc` – Gas natural comprimido【19†L1710-L1713】.
    - `gnl` – Gas natural licuado【19†L1710-L1713】.
    - `hydrogen` – Hidrógeno【19†L1710-L1713】.
    - (y otros campos nuevos como AdBlue, gasolina E85, amoníaco, dependiendo de futuras actualizaciones del Excel).  

La **fuente oficial de datos** es el portal del Ministerio (Geoportal Gasolineras) que publica un fichero Excel (`preciosEESS_es.xls`) con la información completa. Este archivo, de estructura constante a lo largo del tiempo, se renueva **cada hora** con precios actualizados【17†L1602-L1609】. En esa tabla oficial se detalla cada campo (ver *Tabla 4.2: Estructura contenido del fichero Excel*【19†L1693-L1702】【19†L1710-L1718】). Usaremos esa estructura para mapear los datos en la base de datos.

## Descarga diaria de datos (Tarea programada)

Para poblar la base de datos se debe descargar **diariamente a las 07:00** el fichero Excel desde `https://geoportalgasolineras.es/resources/files/preciosEESS_es.xls`. Puede implementarse de la siguiente forma:
- Crear un **comando Artisan** o **servicio** en Laravel que, al ejecutarse, realice una petición HTTP GET al enlace del Excel. Usar librerías como Guzzle o la fachada `Http` de Laravel para obtener el archivo.  
- Al recibirlo, cargarlo en memoria o guardarlo temporalmente. Luego parsear sus hojas/filas usando una librería PHP de hojas de cálculo (por ejemplo [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/es/)).  
- Leer fila por fila, identificar estación (por código o dirección) y sus precios en ese momento. Se pueden crear o actualizar los registros en la tabla `stations` (si no existe) y guardar un nuevo registro en `prices` con `collected_at`=hora del archivo (campo “Toma de datos” en el Excel).  
- Programar la ejecución diaria: en Laravel usar `$schedule->command('comando:importar-precios')->dailyAt('7:00');` en el scheduler (`App\Console\Kernel`) o, si se prefiere un endpoint, exponer una ruta POST (p.ej. `/api/update-prices`) que invoque este proceso. Luego, desde un CRON externo o `Scheduler`, realizar una petición HTTP POST a ese endpoint cada mañana a las 07:00.  

Según el portal oficial, todas las estaciones de servicio deben notificar cambios con 12 horas de antelación, por lo que la información descargada siempre estará relativamente actualizada【17†L1602-L1609】. El proceso debe limpiar/reescribir los datos de precios históricos para ese instante; se puede mantener o actualizar historiales según diseño (p. ej. conservar todas las capturas diarias para análisis en la sección histórica).

## Modelos, Repositorios y Controladores (Laravel)

Para estructurar el backend en Laravel siguiendo MVC y el patrón repositorio:

- **Modelos Eloquent:** Crear `GasStation` (estaciones) y `Price` (precios). El modelo `GasStation` tendrá relación *uno a muchos* con `Price`. En `GasStation` definir campos según la tabla `stations`. En `Price` los campos de precios y la relación `station_id`.  
- **Repositorios:** Definir interfaces y clases de repositorio (p.ej. `GasStationRepository`, `PriceRepository`) para encapsular la lógica de acceso a datos. Por ejemplo, `GasStationRepository::findByProvince($provincia)` para filtrar gasolineras, `PriceRepository::latest()` para obtener los últimos precios registrados, etc.  
- **Controladores:** Crear controladores dedicados, como:
  - `PriceController`: métodos para servir las páginas principales de precios. Ejemplo: `index()` devuelve vista con precios actuales, usando `PriceRepository` para obtener datos recientes. `historial(Request $req)` genera datos según filtros de fecha/provincia.  
  - `StationController`: métodos para buscador de gasolineras. Ejemplos: `search(Request $req)` recibe filtros (provincia, marca, etc.) y devuelve lista; `show($id)` muestra detalle de una gasolinera con sus precios actuales e históricos.  
  - `ReportController` (opcional): para sección de informes/noticias, con métodos `index()` (listado de posts) y `show($id)` (detalle).  
  - `ImportController`: método `updatePrices()` que se invocaría vía POST para ejecutar la descarga e importación del Excel.  

- **Rutas (web/api):** Definir rutas RESTful o web según convenga. Por ejemplo:
  - `GET /` → `PriceController@index` (precios actuales).
  - `GET /baratas` → `PriceController@cheapest` (gasolineras baratas).
  - `GET /historico` → `PriceController@historico` (gráficos históricos).
  - `POST /api/update-prices` → `ImportController@updatePrices`.
  - `GET /gasolineras` → `StationController@search`.
  - `GET /gasolineras/{id}` → `StationController@show`.
  - `GET /informes` → `ReportController@index`.
  - `GET /informes/{slug}` → `ReportController@show`.
  In `routes/web.php` se definirán las rutas públicas para vistas, y en `routes/api.php` la ruta POST si se prefiere. Añadir middleware auth o protección si fuera necesario para el endpoint de importación.  

- **Lógica de negocio adicional:** Implementar filtros (por combustible, precio máximo, etc.) en los repositorios. Por ejemplo, método `PriceRepository::getCheapest($fuel, $province)` que retorne las estaciones más baratas para un tipo de carburante.  

## Tareas programadas (Scheduler / Cron)

Además de la descarga diaria, se pueden programar otras tareas automáticas:
- **Descarga/importación diaria:** Como se indicó, usando Laravel Scheduler con `->dailyAt('7:00')`. 
- **Actualización regular de caches o índices:** Si se usan cachés de consultas pesadas (gráficos históricos mensuales, listados de baratas), programar regeneración nocturna.
- **Alertas o informes periódicos:** En caso de querer enviar newsletters o avisos al subir mucho el precio, podría haber comandos cron.

La tarea diaria de importación puede implementarse como comando Artisan (p.ej. `php artisan prices:import`) y luego sólo referirse a él en el scheduler, sin necesidad de endpoint. Sin embargo, si se requiere activarla externamente, el endpoint POST mencionado servirá.

## Plantillas Blade (vistas)

Para cada sección se creará una plantilla Blade que utilice un layout común (header, footer, menús). Por ejemplo:
- `layouts/app.blade.php` con el HTML base (incluye menús de navegación, CSS/JS comunes).
- `prices/index.blade.php` para la página principal de precios actuales: muestra la tabla o tarjetas con precios. Incluir bloques para título de página, gráfico y lista de carburantes.  
- `prices/cheapest.blade.php` para gasolineras baratas: muestra filtros (formularios de selección) y tabla de resultados.  
- `prices/historic.blade.php` para histórico: incluye formularios de selección de fecha/provincia y contenedores para los gráficos (por ejemplo, `<canvas>` si se usa Chart.js).  
- `stations/search.blade.php` para el buscador: formulario de búsqueda y resultados listados.  
- `stations/show.blade.php` para ficha de gasolinera: muestra detalles de la estación y los últimos precios.  
- `reports/index.blade.php` y `reports/show.blade.php` para el blog de informes, si aplica.  

En las vistas, usar rutas con `route()` y enlaces hacia filtrados de controladores. Por ejemplo, el formulario de provincia apuntaría a la ruta que `PriceController` escucha para filtrar precios. Para datos dinámicos (como mapas o gráficos) se pueden incluir scripts que consuman endpoints JSON (creados en los controladores) y rendericen con bibliotecas JS.  

## Estructura de información por sección

Cada sección debe incluir los datos e información pertinente, por ejemplo:

- **Precios actuales:** Título y explicación breve (“Estos son los precios medios actuales…”). Tabla/gráfico con *Gasolina 95 E10, Gasolina 98, Gasóleo A, Gasóleo B*, etc. Posible nota de fuente (Ministerio o Geoportal) y fecha/hora de última actualización. SEO: incluir sinónimos (“carburantes”, “combustible”, “euro por litro”).  
- **Gasolineras baratas:** Introducción indicando “Esta es la lista de gasolineras con el combustible más económico hoy”. Instrucciones de uso de filtros (“Filtra por provincia o busca cerca”). Resultados con nombre, dirección, distancia (si aplica), y precio. SEO: destacar “ahorrar en gasolina”, “estación barata”, “combustible al mejor precio”.  
- **Histórico:** Textos explicativos sobre cómo interpretar los gráficos (“Selecciona rango de fechas…”, “Compara los precios mensuales”), y posiblemente recomendaciones (“Consulta el histórico para ver cuándo el combustible ha sido más barato”). SEO: “evolución del precio”, “gráfica histórica carburantes”.  
- **Buscador de gasolineras:** Breve descripción (“Busca gasolineras por provincia, marca o ubicación”). SEO: “encuentra gasolineras cerca”, “mapa gasolineras España”.  
- **Informes/noticias:** Titulares de artículos y resúmenes cortos. SEO: “informes precios carburantes”, “noticias diésel gasolina”.

Durante el desarrollo es crucial que cada página tenga `<title>`, `<meta description>` y encabezados `<h1>...` apropiados con las palabras clave previstas, para mejorar posicionamiento. Por ejemplo, la página principal de precios podría usar `<h1>Precio de la gasolina y diésel HOY en España</h1>`. Cada ficha de gasolinera incluirá `<h1>{Nombre de estación} – precios actualizados en {Localidad}</h1>`. 

## Resumen del flujo de datos

1. **Descarga y parseo del Excel:** El comando/artisanal recupera `preciosEESS_es.xls` a diario y extrae filas (cada fila corresponde a una estación y su último precio de hora).  
2. **Actualización de base de datos:** Para cada fila, buscar (o crear) el registro en `stations`. Luego insertar un nuevo registro en `prices` con los valores de precios (campos: `gas95_e5`, `diesel_a`, etc. según la tabla oficial【19†L1693-L1702】【19†L1710-L1718】) y la marca/hora de toma de datos.  
3. **Servicio web:** Los controladores consultan la BD para obtener datos relevantes. Por ejemplo, `PriceController@index` puede obtener todos los precios más recientes (`Price::latest('collected_at')->get()`), mientras que `StationController@search` filtra gasolineras por los criterios recibidos, devolviendo precios mediante relaciones Eloquent.  
4. **Presentación al usuario:** Las vistas Blade muestran la información de forma legible. Los datos de precios dinámicos pueden cargarse vía AJAX de endpoints API (por ejemplo, una ruta `api/stations/{id}/prices` que devuelva JSON de los precios históricos de una estación para graficar).  

En conjunto, esta estructura permite capturar automáticamente los precios oficiales de los carburantes, almacenarlos en SQLite y exponerlos mediante una aplicación web Laravel organizada por secciones temáticas (actualidad, comparativas, buscador), optimizando el contenido para SEO según las palabras clave de interés (precio de gasolina, gasolineras baratas, histórico carburantes, etc.).  

**Fuentes:** La estructura de datos y la periodicidad de actualización provienen del portal oficial Geoportal Gasolineras (Ministerio de Transición Ecológica), que publica el fichero `preciosEESS_es.xls` con toda la información de precios y estaciones【17†L1602-L1609】. La tabla oficial de campos (“*Estructura contenido del fichero Excel*”) detalla los nombres y ejemplos de cada columna (provincia, municipio, tipos de carburante y sus precios, rótulo, etc.)【19†L1693-L1702】【19†L1710-L1718】. Estos datos guían el diseño de la base de datos y las secciones de la web.