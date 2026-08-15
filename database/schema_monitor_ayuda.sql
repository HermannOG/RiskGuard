ALTER TABLE monitor_variables
    ADD COLUMN banda_verde      VARCHAR(255) NULL AFTER limite_default,
    ADD COLUMN banda_amarillo   VARCHAR(255) NULL AFTER banda_verde,
    ADD COLUMN banda_anaranjado VARCHAR(255) NULL AFTER banda_amarillo,
    ADD COLUMN banda_rojo       VARCHAR(255) NULL AFTER banda_anaranjado;

UPDATE monitor_variables SET
    banda_verde      = 'Numero de procesos muy por debajo del limite configurado, sin riesgo de saturacion.',
    banda_amarillo   = 'Empieza a acercarse al limite, vale la pena vigilarlo.',
    banda_anaranjado = 'Cerca del limite de procesos, riesgo de saturacion si sigue subiendo.',
    banda_rojo       = 'Al limite o por encima, la BD puede empezar a rechazar conexiones nuevas.'
WHERE id = 'p1';

UPDATE monitor_variables SET
    banda_verde      = 'Pocas sesiones haciendo trabajo activo, carga normal.',
    banda_amarillo   = 'Actividad moderada, dentro de lo esperado.',
    banda_anaranjado = 'Muchas sesiones activas a la vez, posible cuello de botella.',
    banda_rojo       = 'Sobrecarga de sesiones activas, el servidor puede volverse lento.'
WHERE id = 'p2';

UPDATE monitor_variables SET
    banda_verde      = 'Ninguna o casi ninguna sesion esperando por un candado (lock).',
    banda_amarillo   = 'Algunos bloqueos puntuales, normalmente pasajeros.',
    banda_anaranjado = 'Varias sesiones bloqueadas, puede indicar una transaccion mal cerrada.',
    banda_rojo       = 'Muchas sesiones bloqueadas, riesgo real de que la BD se trabe.'
WHERE id = 'p3';

UPDATE monitor_variables SET
    banda_verde      = 'No hay consultas tardadas, todo responde rapido.',
    banda_amarillo   = 'Alguna consulta ocasional tardada, vigilar si se repite.',
    banda_anaranjado = 'Varias consultas lentas a la vez, revisar indices o consultas mal escritas.',
    banda_rojo       = 'Muchas operaciones prolongadas, la BD esta trabajando con dificultad.'
WHERE id = 'p4';

UPDATE monitor_variables SET
    banda_verde      = 'El motor usa poca memoria cache, tiene margen de sobra.',
    banda_amarillo   = 'Uso moderado de memoria, dentro de lo normal.',
    banda_anaranjado = 'Uso alto de memoria cache, se acerca a su limite configurado.',
    banda_rojo       = 'Memoria cache casi llena, riesgo de que el motor empiece a fallar por falta de memoria.'
WHERE id = 'm1';

UPDATE monitor_variables SET
    banda_verde      = 'Sin senales de presion, el motor tiene memoria disponible de sobra.',
    banda_amarillo   = 'Presion leve, todavia no es preocupante.',
    banda_anaranjado = 'Presion notable de memoria, el motor empieza a esperar por espacio libre.',
    banda_rojo       = 'Presion critica de memoria, puede afectar el rendimiento de forma seria.'
WHERE id = 'm2';

UPDATE monitor_variables SET
    banda_verde      = 'Cache hit ratio muy alto: casi todo se responde desde memoria, sin tocar disco.',
    banda_amarillo   = 'Cache hit ratio bueno, con algo de lectura a disco.',
    banda_anaranjado = 'Cache hit ratio bajo, el motor esta leyendo disco mas de lo ideal.',
    banda_rojo       = 'Cache hit ratio muy bajo: la mayoria de las consultas van directo a disco, lento.'
WHERE id = 'm3';

UPDATE monitor_variables SET
    banda_verde      = 'Todos los archivos/datafiles criticos estan en linea y accesibles.',
    banda_amarillo   = 'N/A para esta variable (es binaria: en linea o no).',
    banda_anaranjado = 'N/A para esta variable (es binaria: en linea o no).',
    banda_rojo       = 'Hay al menos un archivo critico fuera de linea, requiere atencion inmediata.'
WHERE id = 'a1';

UPDATE monitor_variables SET
    banda_verde      = 'Bastante espacio libre en disco, sin riesgo de quedarse sin espacio.',
    banda_amarillo   = 'Espacio libre moderado, vale la pena empezar a vigilarlo.',
    banda_anaranjado = 'Espacio libre bajo, planificar una limpieza o ampliacion pronto.',
    banda_rojo       = 'Espacio libre muy bajo, riesgo alto de que la BD deje de aceptar escrituras.'
WHERE id = 'a2';

UPDATE monitor_variables SET
    banda_verde      = 'Ningun archivo invalido o inaccesible detectado.',
    banda_amarillo   = 'N/A para esta variable (es binaria: con problema o no).',
    banda_anaranjado = 'N/A para esta variable (es binaria: con problema o no).',
    banda_rojo       = 'Hay archivos invalidos o inaccesibles, requiere revision inmediata.'
WHERE id = 'a3';