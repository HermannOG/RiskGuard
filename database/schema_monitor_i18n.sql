ALTER TABLE monitor_variables
    ADD COLUMN nombre_en VARCHAR(150) NULL AFTER nombre,
    ADD COLUMN descripcion_en VARCHAR(255) NULL AFTER descripcion,
    ADD COLUMN banda_verde_en VARCHAR(255) NULL AFTER banda_rojo,
    ADD COLUMN banda_amarillo_en VARCHAR(255) NULL AFTER banda_verde_en,
    ADD COLUMN banda_anaranjado_en VARCHAR(255) NULL AFTER banda_amarillo_en,
    ADD COLUMN banda_rojo_en VARCHAR(255) NULL AFTER banda_anaranjado_en;

UPDATE monitor_variables SET
    nombre = 'Procesos actuales',
    descripcion = 'Número de procesos activos respecto al límite',
    banda_verde = 'Número de procesos muy por debajo del límite configurado, sin riesgo de saturación.',
    banda_amarillo = 'Empieza a acercarse al límite, vale la pena vigilarlo.',
    banda_anaranjado = 'Cerca del límite de procesos, riesgo de saturación si sigue subiendo.',
    banda_rojo = 'Al límite o por encima, la BD puede empezar a rechazar conexiones nuevas.',
    nombre_en = 'Current processes',
    descripcion_en = 'Number of active processes relative to the limit',
    banda_verde_en = 'Number of processes well below the configured limit, no risk of saturation.',
    banda_amarillo_en = 'Starting to approach the limit, worth keeping an eye on.',
    banda_anaranjado_en = 'Close to the process limit, risk of saturation if it keeps rising.',
    banda_rojo_en = 'At or above the limit, the DB may start rejecting new connections.'
WHERE id = 'p1';

UPDATE monitor_variables SET
    nombre = 'Sesiones activas',
    descripcion = 'Sesiones que están ejecutando actividad ahora mismo',
    banda_verde = 'Pocas sesiones haciendo trabajo activo, carga normal.',
    banda_amarillo = 'Actividad moderada, dentro de lo esperado.',
    banda_anaranjado = 'Muchas sesiones activas a la vez, posible cuello de botella.',
    banda_rojo = 'Sobrecarga de sesiones activas, el servidor puede volverse lento.',
    nombre_en = 'Active sessions',
    descripcion_en = 'Sessions currently doing active work',
    banda_verde_en = 'Few sessions doing active work, normal load.',
    banda_amarillo_en = 'Moderate activity, within expected range.',
    banda_anaranjado_en = 'Many sessions active at once, possible bottleneck.',
    banda_rojo_en = 'Overload of active sessions, the server may become slow.'
WHERE id = 'p2';

UPDATE monitor_variables SET
    nombre = 'Sesiones bloqueadas',
    descripcion = 'Sesiones que esperan por otra sesión (locks)',
    banda_verde = 'Ninguna o casi ninguna sesión esperando por un candado (lock).',
    banda_amarillo = 'Algunos bloqueos puntuales, normalmente pasajeros.',
    banda_anaranjado = 'Varias sesiones bloqueadas, puede indicar una transacción mal cerrada.',
    banda_rojo = 'Muchas sesiones bloqueadas, riesgo real de que la BD se trabe.',
    nombre_en = 'Blocked sessions',
    descripcion_en = 'Sessions waiting on another session (locks)',
    banda_verde_en = 'None or almost none waiting on a lock.',
    banda_amarillo_en = 'Some occasional locks, usually transient.',
    banda_anaranjado_en = 'Several blocked sessions, may indicate a transaction that wasn''t closed properly.',
    banda_rojo_en = 'Many blocked sessions, real risk the DB will lock up.'
WHERE id = 'p3';

UPDATE monitor_variables SET
    nombre = 'Operaciones prolongadas',
    descripcion = 'Consultas/operaciones que llevan tiempo excesivo corriendo',
    banda_verde = 'No hay consultas tardadas, todo responde rápido.',
    banda_amarillo = 'Alguna consulta ocasional tardada, vigilar si se repite.',
    banda_anaranjado = 'Varias consultas lentas a la vez, revisar índices o consultas mal escritas.',
    banda_rojo = 'Muchas operaciones prolongadas, la BD está trabajando con dificultad.',
    nombre_en = 'Long-running operations',
    descripcion_en = 'Queries/operations that have been running for too long',
    banda_verde_en = 'No slow queries, everything responds quickly.',
    banda_amarillo_en = 'An occasional slow query, watch if it repeats.',
    banda_anaranjado_en = 'Several slow queries at once, review indexes or poorly written queries.',
    banda_rojo_en = 'Many long-running operations, the DB is struggling.'
WHERE id = 'p4';

UPDATE monitor_variables SET
    nombre = 'Uso de buffer/caché',
    descripcion = 'Porcentaje de uso del área de memoria compartida del motor',
    banda_verde = 'El motor usa poca memoria caché, tiene margen de sobra.',
    banda_amarillo = 'Uso moderado de memoria, dentro de lo normal.',
    banda_anaranjado = 'Uso alto de memoria caché, se acerca a su límite configurado.',
    banda_rojo = 'Memoria caché casi llena, riesgo de que el motor empiece a fallar por falta de memoria.',
    nombre_en = 'Buffer/cache usage',
    descripcion_en = 'Percentage of use of the engine''s shared memory area',
    banda_verde_en = 'The engine uses little cache memory, plenty of headroom.',
    banda_amarillo_en = 'Moderate memory use, within normal range.',
    banda_anaranjado_en = 'High cache memory use, approaching its configured limit.',
    banda_rojo_en = 'Cache memory nearly full, risk the engine starts failing from lack of memory.'
WHERE id = 'm1';

UPDATE monitor_variables SET
    nombre = 'Presión de memoria',
    descripcion = 'Indicador de sobre-asignación o falta de memoria disponible',
    banda_verde = 'Sin señales de presión, el motor tiene memoria disponible de sobra.',
    banda_amarillo = 'Presión leve, todavía no es preocupante.',
    banda_anaranjado = 'Presión notable de memoria, el motor empieza a esperar por espacio libre.',
    banda_rojo = 'Presión crítica de memoria, puede afectar el rendimiento de forma seria.',
    nombre_en = 'Memory pressure',
    descripcion_en = 'Indicator of over-allocation or lack of available memory',
    banda_verde_en = 'No signs of pressure, the engine has plenty of memory available.',
    banda_amarillo_en = 'Mild pressure, not yet a concern.',
    banda_anaranjado_en = 'Noticeable memory pressure, the engine starts waiting for free space.',
    banda_rojo_en = 'Critical memory pressure, can seriously affect performance.'
WHERE id = 'm2';

UPDATE monitor_variables SET
    nombre = 'Cache hit ratio',
    descripcion = 'Porcentaje de aciertos de caché (más alto = mejor salud)',
    banda_verde = 'Cache hit ratio muy alto: casi todo se responde desde memoria, sin tocar disco.',
    banda_amarillo = 'Cache hit ratio bueno, con algo de lectura a disco.',
    banda_anaranjado = 'Cache hit ratio bajo, el motor está leyendo disco más de lo ideal.',
    banda_rojo = 'Cache hit ratio muy bajo: la mayoría de las consultas van directo a disco, lento.',
    nombre_en = 'Cache hit ratio',
    descripcion_en = 'Percentage of cache hits (higher = healthier)',
    banda_verde_en = 'Very high cache hit ratio: almost everything is served from memory, no disk hit.',
    banda_amarillo_en = 'Good cache hit ratio, with some disk reads.',
    banda_anaranjado_en = 'Low cache hit ratio, the engine is reading disk more than ideal.',
    banda_rojo_en = 'Very low cache hit ratio: most queries go straight to disk, slow.'
WHERE id = 'm3';

UPDATE monitor_variables SET
    nombre = 'Archivos fuera de línea',
    descripcion = 'Datafiles/archivos críticos que no están ONLINE',
    banda_verde = 'Todos los archivos/datafiles críticos están en línea y accesibles.',
    banda_amarillo = 'N/A para esta variable (es binaria: en línea o no).',
    banda_anaranjado = 'N/A para esta variable (es binaria: en línea o no).',
    banda_rojo = 'Hay al menos un archivo crítico fuera de línea, requiere atención inmediata.',
    nombre_en = 'Offline files',
    descripcion_en = 'Critical datafiles/files that are not ONLINE',
    banda_verde_en = 'All critical files/datafiles are online and accessible.',
    banda_amarillo_en = 'N/A for this variable (it''s binary: online or not).',
    banda_anaranjado_en = 'N/A for this variable (it''s binary: online or not).',
    banda_rojo_en = 'At least one critical file is offline, requires immediate attention.'
WHERE id = 'a1';

UPDATE monitor_variables SET
    nombre = 'Espacio libre',
    descripcion = 'Porcentaje de espacio libre en tablespaces/discos',
    banda_verde = 'Bastante espacio libre en disco, sin riesgo de quedarse sin espacio.',
    banda_amarillo = 'Espacio libre moderado, vale la pena empezar a vigilarlo.',
    banda_anaranjado = 'Espacio libre bajo, planificar una limpieza o ampliación pronto.',
    banda_rojo = 'Espacio libre muy bajo, riesgo alto de que la BD deje de aceptar escrituras.',
    nombre_en = 'Free space',
    descripcion_en = 'Percentage of free space in tablespaces/disks',
    banda_verde_en = 'Plenty of free disk space, no risk of running out.',
    banda_amarillo_en = 'Moderate free space, worth starting to monitor.',
    banda_anaranjado_en = 'Low free space, plan a cleanup or expansion soon.',
    banda_rojo_en = 'Very low free space, high risk the DB stops accepting writes.'
WHERE id = 'a2';

UPDATE monitor_variables SET
    nombre = 'Archivos con problemas',
    descripcion = 'Archivos inválidos o inaccesibles',
    banda_verde = 'Ningún archivo inválido o inaccesible detectado.',
    banda_amarillo = 'N/A para esta variable (es binaria: con problema o no).',
    banda_anaranjado = 'N/A para esta variable (es binaria: con problema o no).',
    banda_rojo = 'Hay archivos inválidos o inaccesibles, requiere revisión inmediata.',
    nombre_en = 'Files with problems',
    descripcion_en = 'Invalid or inaccessible files',
    banda_verde_en = 'No invalid or inaccessible files detected.',
    banda_amarillo_en = 'N/A for this variable (it''s binary: has a problem or not).',
    banda_anaranjado_en = 'N/A for this variable (it''s binary: has a problem or not).',
    banda_rojo_en = 'There are invalid or inaccessible files, requires immediate review.'
WHERE id = 'a3';