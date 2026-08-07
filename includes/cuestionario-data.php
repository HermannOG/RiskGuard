<?php
/**
 * Catálogo del Módulo de Evaluación de Riesgos.
 *
 * Basado en controles reales de ISO/IEC 27002:2022 aplicables a la
 * administración de bases de datos. Cada control agrupa 1 o varias
 * preguntas (Sí / No / No aplica). peso_c/peso_i/peso_d representa el
 * nivel de contribución del control a cada dimensión de la triada CIA
 * (0 = sin influencia ... 3 = alta), y "peso" es la importancia relativa
 * del control dentro de su dimensión dominante (1-3).
 *
 * Este arreglo es la única fuente de verdad: se usa para pintar el
 * formulario en PHP y también se serializa a JSON para que
 * assets/js/evaluacion.js calcule los resultados sin duplicar datos.
 * Debe mantenerse sincronizado con el catálogo sembrado en
 * database/schema.sql (el catálogo en BD solo guarda el texto en
 * español; es un catálogo interno que nunca se relee para mostrar en
 * pantalla, así que no necesita variante en inglés).
 *
 * Los IDs, pesos y estructura de controles/preguntas son una única
 * fuente (no se duplican los números); solo el texto (nombre, objetivo,
 * pregunta) tiene variante _es/_en, seleccionada al final del archivo
 * según el idioma activo ($LANG, definido por includes/i18n.php).
 */

$textos_es = [
    'grupos' => [
        'I' => 'Integridad',
        'D' => 'Disponibilidad',
        'C' => 'Confidencialidad',
    ],
    'dominios' => [
        'ORG' => 'Organizacional',
        'TEC' => 'Tecnológico',
    ],
    'controles' => [
        1  => ['nombre' => 'Control de acceso', 'objetivo' => 'Asegurar el acceso autorizado y prevenir el acceso no autorizado a la base de datos.'],
        2  => ['nombre' => 'Autenticación segura', 'objetivo' => 'Garantizar procedimientos de autenticación seguros de acuerdo con la política de control de acceso.'],
        3  => ['nombre' => 'Segregación de funciones', 'objetivo' => 'Separar funciones y áreas de responsabilidad en conflicto para reducir el riesgo de modificaciones no autorizadas.'],
        4  => ['nombre' => 'Gestión de cambios', 'objetivo' => 'Someter los cambios a las instalaciones de procesamiento a procedimientos de control de cambios.'],
        5  => ['nombre' => 'Gestión de configuración', 'objetivo' => 'Establecer, documentar, implementar y monitorear las configuraciones de seguridad.'],
        6  => ['nombre' => 'Registro de eventos (logging)', 'objetivo' => 'Producir, almacenar, proteger y analizar registros de eventos.'],
        7  => ['nombre' => 'Monitoreo de actividades', 'objetivo' => 'Monitorear redes, sistemas y aplicaciones para detectar comportamiento anómalo.'],
        8  => ['nombre' => 'Respaldo de información', 'objetivo' => 'Mantener copias de respaldo probadas de la información, software y sistemas.'],
        9  => ['nombre' => 'Redundancia', 'objetivo' => 'Implementar redundancia suficiente para cumplir los requisitos de disponibilidad.'],
        10 => ['nombre' => 'Continuidad de TI', 'objetivo' => 'Planificar, implementar, mantener y probar la continuidad de TI ante disrupciones.'],
        11 => ['nombre' => 'Seguridad de redes', 'objetivo' => 'Asegurar y gestionar las redes para proteger la información en sistemas y aplicaciones.'],
        12 => ['nombre' => 'Uso de criptografía', 'objetivo' => 'Definir y aplicar reglas para el uso efectivo de la criptografía.'],
        13 => ['nombre' => 'Enmascaramiento de datos', 'objetivo' => 'Usar enmascaramiento de datos para limitar la exposición de información sensible.'],
        14 => ['nombre' => 'Prevención de fuga de datos', 'objetivo' => 'Aplicar medidas de prevención de fuga de datos (DLP) a sistemas que procesan información sensible.'],
        15 => ['nombre' => 'Clasificación de la información', 'objetivo' => 'Clasificar la información según los requisitos de confidencialidad, integridad y disponibilidad.'],
        16 => ['nombre' => 'Inventario de activos', 'objetivo' => 'Desarrollar y mantener un inventario de activos de información.'],
        17 => ['nombre' => 'Seguridad en acuerdos con proveedores', 'objetivo' => 'Establecer y acordar requisitos de seguridad con proveedores según el tipo de relación.'],
        18 => ['nombre' => 'Gestión de capacidad', 'objetivo' => 'Monitorear y ajustar el uso de recursos según proyecciones de capacidad futura.'],
        19 => ['nombre' => 'Gestión de servicios de proveedores', 'objetivo' => 'Monitorear y revisar regularmente los servicios prestados por proveedores según acuerdos definidos.'],
    ],
    'preguntas' => [
        1 => [
            'texto' => '¿Existe control de cambios formal antes de modificar tablas/índices de la BD?',
            'ayuda' => 'Se refiere a si hay un proceso definido (solicitud, revisión, aprobación) antes de alterar la estructura de la base de datos, en vez de hacer cambios directos y espontáneos.',
            'niveles' => [
            0 => 'No existe ningún proceso; cualquiera puede modificar la estructura de la BD sin aviso ni aprobación.',
            1 => 'Ocasionalmente se avisa antes de un cambio, pero depende de la persona, no hay pasos definidos.',
            2 => 'Hay algunas prácticas de aviso previo, pero no se siguen de forma consistente en todos los cambios.',
            3 => 'Existe un procedimiento documentado de solicitud y aprobación que se aplica en la mayoría de los cambios.',
            4 => 'El proceso se aplica siempre, se supervisa, y queda evidencia (bitácora de aprobaciones) de cada cambio.',
            5 => 'El proceso de control de cambios se mide, se audita periódicamente y se mejora continuamente.',
            ],
        ],
        2 => [
            'texto' => '¿La BD tiene integridad referencial (llaves foráneas, constraints, triggers)?',
            'ayuda' => 'Pregunta si la base de datos usa mecanismos técnicos (llaves foráneas, restricciones, triggers) para impedir que se guarden datos inconsistentes o huérfanos.',
            'niveles' => [
            0 => 'No se usa ningún mecanismo de integridad referencial; los datos pueden quedar inconsistentes sin que el motor lo impida.',
            1 => 'Existen algunas restricciones puestas de forma aislada, sin un criterio claro de cuándo aplicarlas.',
            2 => 'Varias tablas tienen llaves foráneas o constraints, pero no se aplica de forma pareja en todo el modelo.',
            3 => 'El modelo de datos documenta y aplica integridad referencial en la mayoría de las relaciones entre tablas.',
            4 => 'La integridad referencial está implementada en todo el modelo y se revisa cuando se modifica el esquema.',
            5 => 'Se audita y ajusta continuamente conforme el modelo evoluciona, sin excepciones no justificadas.',
            ],
        ],
        3 => [
            'texto' => '¿Se registra en bitácora quién modifica los datos?',
            'ayuda' => 'Se refiere a si el sistema guarda un registro (log) de qué usuario hizo qué cambio y cuándo, para poder rastrear modificaciones.',
            'niveles' => [
            0 => 'No existe ningún registro de quién modifica los datos.',
            1 => 'Se registra algo de forma manual o esporádica, sin un mecanismo automático.',
            2 => 'Hay registro automático en algunas tablas o procesos, pero no de forma general.',
            3 => 'Existe una bitácora documentada que cubre la mayoría de las operaciones de modificación.',
            4 => 'El registro cubre todas las modificaciones relevantes y se revisa periódicamente.',
            5 => 'La bitácora se audita, se analiza activamente, y el proceso de logging se mejora continuamente.',
            ],
        ],
        4 => [
            'texto' => '¿Se validan los respaldos con checksums o firmas?',
            'ayuda' => 'Pregunta si, además de hacer respaldos, se verifica que el archivo de respaldo no esté dañado o incompleto (por ejemplo con una suma de verificación).',
            'niveles' => [
            0 => 'No se valida de ninguna forma si los respaldos están íntegros o corruptos.',
            1 => 'Se revisa de forma manual y ocasional, sin un método consistente.',
            2 => 'Existe algún mecanismo de validación, pero no se aplica a todos los respaldos.',
            3 => 'Los respaldos se validan de forma documentada con checksums o firmas en la mayoría de los casos.',
            4 => 'Todos los respaldos se validan automáticamente y queda evidencia de esa validación.',
            5 => 'La validación de respaldos es continua, medida, y se ajusta si se detectan fallos recurrentes.',
            ],
        ],
        5 => [
            'texto' => '¿Hay inventario de qué tablas tienen información crítica?',
            'ayuda' => 'Se refiere a si existe un listado o clasificación de cuáles tablas de la base de datos contienen información sensible o importante para el negocio.',
            'niveles' => [
            0 => 'No existe ningún inventario de tablas críticas.',
            1 => 'Alguien conoce informalmente cuáles tablas son críticas, pero no está documentado.',
            2 => 'Existe un inventario parcial, que cubre solo algunas de las tablas relevantes.',
            3 => 'Hay un inventario documentado que identifica la mayoría de las tablas críticas.',
            4 => 'El inventario está completo, actualizado y se usa para tomar decisiones de protección.',
            5 => 'El inventario se revisa y actualiza continuamente conforme cambia el modelo de datos.',
            ],
        ],
        6 => [
            'texto' => '¿Los privilegios de escritura están restringidos a perfiles autorizados?',
            'ayuda' => 'Pregunta si solo ciertos usuarios/roles autorizados pueden insertar, modificar o borrar datos, en vez de que cualquier usuario tenga esos permisos.',
            'niveles' => [
            0 => 'Cualquier usuario con acceso a la BD puede escribir/modificar datos, sin restricción de perfil.',
            1 => 'Hay alguna restricción, pero se aplica de forma inconsistente o informal.',
            2 => 'Los privilegios de escritura están limitados en algunos perfiles, pero no en todos los que deberían.',
            3 => 'Existe una política documentada que define qué perfiles tienen privilegios de escritura.',
            4 => 'Los privilegios se revisan y supervisan periódicamente para confirmar que sigan siendo correctos.',
            5 => 'La asignación de privilegios de escritura se audita y ajusta de forma continua.',
            ],
        ],
        7 => [
            'texto' => '¿Se exige autenticación individual (no usuarios compartidos)?',
            'ayuda' => 'Se refiere a si cada persona tiene su propio usuario para acceder a la BD, en vez de que varias personas compartan una sola cuenta.',
            'niveles' => [
            0 => 'Se usan cuentas compartidas o genéricas sin identificar a la persona real.',
            1 => 'Existen cuentas individuales para algunos usuarios, pero conviven con cuentas compartidas.',
            2 => 'La mayoría de los usuarios tiene cuenta individual, aunque persisten algunas excepciones.',
            3 => 'Está documentado y aplicado que cada usuario debe tener su propia cuenta.',
            4 => 'Se supervisa activamente que no existan cuentas compartidas, con revisiones periódicas.',
            5 => 'El cumplimiento de autenticación individual se audita y mejora de forma continua.',
            ],
        ],
        8 => [
            'texto' => '¿Hay segregación entre quien programa un cambio y quien lo aprueba?',
            'ayuda' => 'Pregunta si la persona que desarrolla o programa un cambio en la BD es distinta de quien lo revisa y aprueba antes de aplicarlo.',
            'niveles' => [
            0 => 'La misma persona programa y aprueba sus propios cambios, sin ninguna revisión externa.',
            1 => 'Ocasionalmente otra persona revisa, pero no es una práctica establecida.',
            2 => 'Hay revisión por otra persona en algunos cambios, no en todos.',
            3 => 'Está documentado que todo cambio debe ser aprobado por alguien distinto a quien lo programó.',
            4 => 'La segregación se aplica siempre y queda evidencia de quién aprobó cada cambio.',
            5 => 'El cumplimiento de esta segregación se mide y se ajusta continuamente.',
            ],
        ],
        9 => [
            'texto' => '¿El motor garantiza transacciones ACID?',
            'ayuda' => 'Se refiere a si el motor de base de datos asegura que las operaciones se completen de forma atómica, consistente, aislada y duradera (ACID), evitando datos a medio guardar.',
            'niveles' => [
            0 => 'No se usa o no se conoce si el motor garantiza propiedades ACID.',
            1 => 'Se asume que existen, pero no se ha verificado ni configurado explícitamente.',
            2 => 'Se usan transacciones en algunos procesos críticos, pero no de forma generalizada.',
            3 => 'Está documentado y aplicado que las operaciones críticas usan transacciones ACID.',
            4 => 'El uso de transacciones se supervisa y se verifica en revisiones técnicas periódicas.',
            5 => 'El cumplimiento de ACID se audita y se ajusta continuamente conforme evoluciona el sistema.',
            ],
        ],
        10 => [
            'texto' => '¿Se revisa periódicamente la consistencia referencial?',
            'ayuda' => 'Pregunta si, además de tener llaves foráneas, alguien verifica de vez en cuando que los datos realmente sigan siendo consistentes entre tablas relacionadas.',
            'niveles' => [
            0 => 'Nunca se revisa la consistencia referencial una vez que el sistema está en producción.',
            1 => 'Se revisa de forma esporádica, solo cuando surge un problema evidente.',
            2 => 'Existe alguna revisión periódica, pero no cubre todas las relaciones importantes.',
            3 => 'Hay un proceso documentado de revisión periódica de consistencia referencial.',
            4 => 'La revisión se hace de forma programada y supervisada, con registro de resultados.',
            5 => 'El proceso de revisión se mide y mejora continuamente según los hallazgos.',
            ],
        ],
        11 => [
            'texto' => '¿Hay backups programados con pruebas de restauración?',
            'ayuda' => 'Se refiere a si los respaldos se hacen automáticamente en un horario definido, y además si se prueba que realmente se pueden restaurar (no solo que se generan).',
            'niveles' => [
            0 => 'No hay respaldos programados, o nunca se ha probado si realmente funcionan.',
            1 => 'Hay respaldos ocasionales, sin horario fijo ni pruebas de restauración.',
            2 => 'Los respaldos están programados, pero las pruebas de restauración son raras o inexistentes.',
            3 => 'Existen respaldos programados y se han documentado pruebas de restauración exitosas.',
            4 => 'Los respaldos y sus pruebas de restauración se supervisan de forma periódica y sistemática.',
            5 => 'El proceso completo (respaldo y restauración) se audita y mejora continuamente.',
            ],
        ],
        12 => [
            'texto' => '¿Existe redundancia/replicación/alta disponibilidad?',
            'ayuda' => 'Pregunta si hay más de una copia funcionando del motor de base de datos (réplica), de forma que si uno falla, el servicio sigue disponible.',
            'niveles' => [
            0 => 'Existe un solo servidor de base de datos, sin ningún tipo de redundancia.',
            1 => 'Se ha considerado la redundancia pero no está implementada de forma real.',
            2 => 'Existe redundancia parcial (por ejemplo, solo para algunas bases o ambientes).',
            3 => 'Está documentada e implementada una solución de redundancia/replicación para los sistemas críticos.',
            4 => 'La redundancia se prueba y supervisa periódicamente (por ejemplo, simulacros de conmutación).',
            5 => 'El esquema de alta disponibilidad se mide, se ajusta y mejora continuamente.',
            ],
        ],
        13 => [
            'texto' => '¿Se monitorea capacidad (disco, conexiones, rendimiento)?',
            'ayuda' => 'Se refiere a si alguien vigila activamente el uso de recursos del servidor de base de datos (espacio en disco, número de conexiones, tiempos de respuesta) para anticipar problemas.',
            'niveles' => [
            0 => 'No se monitorea ningún indicador de capacidad del servidor de BD.',
            1 => 'Se revisa de forma manual y ocasional, sin ninguna herramienta o rutina definida.',
            2 => 'Existe monitoreo de algunos indicadores, pero no de forma integral.',
            3 => 'Hay un proceso documentado que monitorea los indicadores clave de capacidad.',
            4 => 'El monitoreo es continuo, con alertas configuradas y revisión periódica de tendencias.',
            5 => 'El monitoreo de capacidad se ajusta y mejora continuamente según el crecimiento del sistema.',
            ],
        ],
        14 => [
            'texto' => '¿Hay un plan de recuperación ante desastres (DRP) para la BD?',
            'ayuda' => 'Pregunta si existe un plan documentado que indique qué hacer, paso a paso, si la base de datos se pierde o queda inutilizable por un desastre (falla grave, incendio, ataque, etc.).',
            'niveles' => [
            0 => 'No existe ningún plan de recuperación ante desastres para la base de datos.',
            1 => 'Hay una idea informal de qué hacer, pero no está documentada.',
            2 => 'Existe un plan parcial, que no cubre todos los escenarios relevantes.',
            3 => 'Hay un DRP documentado que cubre los escenarios principales de pérdida de la BD.',
            4 => 'El DRP se prueba periódicamente (simulacros) y se supervisa su vigencia.',
            5 => 'El DRP se audita, se mide su efectividad, y se mejora continuamente tras cada prueba o incidente.',
            ],
        ],
        15 => [
            'texto' => '¿Se hace mantenimiento preventivo (reindexar, actualizar estadísticas)?',
            'ayuda' => 'Se refiere a si se realizan tareas periódicas de mantenimiento técnico (como reconstruir índices o actualizar estadísticas) para que el motor siga funcionando de forma eficiente.',
            'niveles' => [
            0 => 'No se realiza ningún mantenimiento preventivo sobre la base de datos.',
            1 => 'Se hace mantenimiento de forma esporádica, solo cuando ya hay problemas de rendimiento.',
            2 => 'Existen algunas tareas de mantenimiento, pero no cubren todos los componentes críticos.',
            3 => 'Hay un plan documentado de mantenimiento preventivo que se ejecuta regularmente.',
            4 => 'El mantenimiento se supervisa y se verifica su efectividad después de cada ejecución.',
            5 => 'El plan de mantenimiento se ajusta y mejora continuamente según el comportamiento del sistema.',
            ],
        ],
        16 => [
            'texto' => '¿Hay alertas automáticas ante caídas del servicio?',
            'ayuda' => 'Pregunta si el sistema avisa automáticamente (correo, mensaje, etc.) cuando el servicio de base de datos deja de responder, en vez de que alguien tenga que notarlo por casualidad.',
            'niveles' => [
            0 => 'No existen alertas automáticas; una caída del servicio se detecta solo si alguien la reporta.',
            1 => 'Hay algún tipo de aviso informal, no automatizado ni confiable.',
            2 => 'Existen alertas automáticas para algunos casos, pero no cubren todos los escenarios de caída.',
            3 => 'Está documentado e implementado un sistema de alertas automáticas ante caídas del servicio.',
            4 => 'Las alertas se supervisan activamente y se verifica que efectivamente lleguen a tiempo.',
            5 => 'El sistema de alertas se ajusta y mejora continuamente para reducir falsos positivos/negativos.',
            ],
        ],
        17 => [
            'texto' => '¿El servidor está segmentado de redes no confiables?',
            'ayuda' => 'Se refiere a si el servidor de base de datos está aislado en una red separada y protegida, en vez de estar expuesto directamente a redes públicas o poco confiables.',
            'niveles' => [
            0 => 'El servidor de base de datos es accesible directamente desde redes no confiables (incluyendo Internet).',
            1 => 'Hay alguna restricción básica, pero la segmentación no es real ni consistente.',
            2 => 'Existe segmentación parcial, cubriendo algunos accesos pero no todos.',
            3 => 'El servidor está documentadamente segmentado detrás de firewall/VLAN respecto a redes no confiables.',
            4 => 'La segmentación se supervisa y se verifica periódicamente que siga vigente.',
            5 => 'La segmentación de red se audita y se ajusta continuamente frente a nuevas amenazas.',
            ],
        ],
        18 => [
            'texto' => '¿Hay un SLA de tiempo de actividad definido?',
            'ayuda' => 'Pregunta si existe un acuerdo formal (con un proveedor o internamente) que defina qué tan disponible debe estar el servicio de base de datos (por ejemplo, 99.9% del tiempo).',
            'niveles' => [
            0 => 'No existe ningún acuerdo o compromiso de tiempo de actividad para el servicio.',
            1 => 'Hay una expectativa informal de disponibilidad, sin documentarse en ningún acuerdo.',
            2 => 'Existe un SLA parcial o no formalizado del todo.',
            3 => 'Hay un SLA documentado que define el tiempo de actividad esperado del servicio.',
            4 => 'El cumplimiento del SLA se supervisa y se mide de forma periódica.',
            5 => 'El SLA se revisa y ajusta continuamente según el desempeño real del servicio.',
            ],
        ],
        19 => [
            'texto' => '¿El acceso es por roles con privilegio mínimo (RBAC)?',
            'ayuda' => 'Se refiere a si los permisos se asignan por rol (por ejemplo \'analista\', \'DBA\') dándole a cada uno solo lo que necesita, en vez de dar acceso amplio a todos por igual.',
            'niveles' => [
            0 => 'No existe un esquema de roles; los permisos se otorgan de forma libre o pareja para todos.',
            1 => 'Hay algunos roles definidos, pero no se aplica el principio de privilegio mínimo.',
            2 => 'Existen roles con privilegio mínimo en algunas áreas, no en toda la base de datos.',
            3 => 'Está documentado e implementado un esquema de RBAC con privilegio mínimo en la mayoría de los casos.',
            4 => 'El esquema de roles se supervisa y se revisa periódicamente para detectar privilegios excesivos.',
            5 => 'El modelo de RBAC se audita y ajusta de forma continua conforme cambian las necesidades del negocio.',
            ],
        ],
        20 => [
            'texto' => '¿Los datos sensibles se cifran en reposo y tránsito?',
            'ayuda' => 'Pregunta si la información sensible está cifrada tanto cuando está guardada en el disco (en reposo) como cuando viaja por la red (en tránsito).',
            'niveles' => [
            0 => 'Los datos sensibles no están cifrados, ni almacenados ni en tránsito.',
            1 => 'Se cifra algo de forma puntual, sin una política clara de qué y cómo cifrar.',
            2 => 'Se cifra en reposo o en tránsito, pero no en ambos casos de forma consistente.',
            3 => 'Está documentado e implementado el cifrado de datos sensibles tanto en reposo como en tránsito.',
            4 => 'El uso de cifrado se supervisa y se verifica periódicamente (por ejemplo, revisando configuraciones TLS).',
            5 => 'La estrategia de cifrado se audita y se actualiza continuamente conforme a nuevos estándares.',
            ],
        ],
        21 => [
            'texto' => '¿Los ambientes de prueba usan datos enmascarados?',
            'ayuda' => 'Se refiere a si, al copiar datos reales a un ambiente de desarrollo o pruebas, la información sensible se disfraza/anonimiza en vez de usarse tal cual.',
            'niveles' => [
            0 => 'Los ambientes de prueba usan datos reales sin ningún tipo de enmascaramiento.',
            1 => 'Se enmascara algo de forma manual y ocasional, sin un proceso definido.',
            2 => 'Existe enmascaramiento parcial, cubriendo algunos campos pero no todos los sensibles.',
            3 => 'Hay un proceso documentado de enmascaramiento aplicado a los ambientes de prueba.',
            4 => 'El enmascaramiento se supervisa y se verifica que se aplique en cada copia de datos.',
            5 => 'El proceso de enmascaramiento se audita y mejora continuamente frente a nuevos tipos de datos sensibles.',
            ],
        ],
        22 => [
            'texto' => '¿Hay clasificación de la información (pública/interna/confidencial)?',
            'ayuda' => 'Pregunta si los datos de la base de datos están etiquetados según su nivel de sensibilidad (por ejemplo pública, interna, confidencial), para saber cómo protegerlos.',
            'niveles' => [
            0 => 'No existe ninguna clasificación de la información almacenada.',
            1 => 'Alguien tiene una idea informal de qué es sensible, pero no está documentado ni etiquetado.',
            2 => 'Existe clasificación parcial, cubriendo solo algunas tablas o campos.',
            3 => 'Hay un esquema de clasificación documentado y aplicado a la mayoría de la información.',
            4 => 'La clasificación se supervisa y se revisa periódicamente para mantenerse actualizada.',
            5 => 'El esquema de clasificación se audita y ajusta continuamente conforme cambia la información manejada.',
            ],
        ],
        23 => [
            'texto' => '¿Están segregadas las funciones de DBA/dev/usuario final?',
            'ayuda' => 'Se refiere a si las personas que administran la base de datos, las que desarrollan software, y las que solo la usan tienen roles y permisos claramente distintos.',
            'niveles' => [
            0 => 'No hay separación de funciones; las mismas personas administran, desarrollan y usan la BD sin distinción.',
            1 => 'Existe alguna separación informal, pero no está definida ni se respeta siempre.',
            2 => 'Hay separación parcial de funciones, aplicada en algunos casos pero no de forma general.',
            3 => 'Está documentado y aplicado que DBA, desarrollo y usuario final tienen roles y permisos distintos.',
            4 => 'La segregación de funciones se supervisa y se revisa periódicamente para detectar mezclas indebidas.',
            5 => 'El modelo de segregación se audita y ajusta continuamente conforme crece el equipo o cambia la operación.',
            ],
        ],
        24 => [
            'texto' => '¿Se exige autenticación multifactor para datos sensibles?',
            'ayuda' => 'Pregunta si, para acceder a información sensible, además de la contraseña se pide un segundo factor (como un código temporal o una app de autenticación).',
            'niveles' => [
            0 => 'El acceso a datos sensibles solo requiere usuario y contraseña, sin segundo factor.',
            1 => 'MFA existe pero es opcional o se usa de forma inconsistente entre usuarios.',
            2 => 'MFA está habilitado para algunos usuarios o accesos, no para todos los que manejan datos sensibles.',
            3 => 'Está documentado y aplicado que el acceso a datos sensibles requiere MFA.',
            4 => 'El cumplimiento de MFA se supervisa y se revisa periódicamente para detectar excepciones.',
            5 => 'La estrategia de autenticación multifactor se audita y se mejora continuamente.',
            ],
        ],
        25 => [
            'texto' => '¿Los terceros con acceso firman acuerdo de confidencialidad?',
            'ayuda' => 'Se refiere a si cualquier proveedor o persona externa que tenga acceso a la base de datos firma un compromiso legal de no divulgar la información que vea.',
            'niveles' => [
            0 => 'Terceros acceden a la base de datos sin ningún acuerdo de confidencialidad firmado.',
            1 => 'Se firma un acuerdo de forma ocasional, sin ser una práctica establecida.',
            2 => 'Existe el acuerdo para algunos proveedores, pero no para todos los que tienen acceso.',
            3 => 'Está documentado y aplicado que todo tercero con acceso firma un acuerdo de confidencialidad.',
            4 => 'El cumplimiento de esta política se supervisa y se verifica periódicamente.',
            5 => 'La gestión de acuerdos con terceros se audita y mejora continuamente.',
            ],
        ],
        26 => [
            'texto' => '¿Hay controles anti fuga de datos (DLP) en exportaciones masivas?',
            'ayuda' => 'Pregunta si existen mecanismos que detecten o bloqueen que alguien extraiga grandes cantidades de información sensible de la base de datos sin autorización.',
            'niveles' => [
            0 => 'No existe ningún control que detecte o limite exportaciones masivas de datos.',
            1 => 'Hay algún control informal (por ejemplo, revisión manual ocasional), sin herramienta específica.',
            2 => 'Existen controles DLP para algunos escenarios, no para todas las formas de exportación.',
            3 => 'Está documentado e implementado un control DLP que cubre las exportaciones masivas relevantes.',
            4 => 'El funcionamiento de los controles DLP se supervisa y se revisa periódicamente su efectividad.',
            5 => 'La estrategia de prevención de fuga de datos se audita y mejora continuamente frente a nuevas técnicas de exfiltración.',
            ],
        ],
    ],
    'opciones_respuesta' => [
        'si' => 'Sí',
        'no' => 'No',
        'na' => 'No aplica',
    ],
    'niveles_madurez' => [
        0 => 'No existe',
        1 => 'Informal',
        2 => 'Parcial',
        3 => 'Documentado',
        4 => 'Implementado y supervisado',
        5 => 'Mejora continua',
    ],
    'recomendaciones' => [
        'D' => 'Se recomienda fortalecer mecanismos de respaldo, recuperación ante desastres, monitoreo y continuidad operativa.',
        'C' => 'Se recomienda fortalecer controles de acceso, autenticación, cifrado y protección de información sensible.',
        'I' => 'Se recomienda mejorar controles de cambios, auditoría, validación de datos y segregación de funciones.',
    ],
];

$textos_en = [
    'grupos' => [
        'I' => 'Integrity',
        'D' => 'Availability',
        'C' => 'Confidentiality',
    ],
    'dominios' => [
        'ORG' => 'Organizational',
        'TEC' => 'Technological',
    ],
    'controles' => [
        1  => ['nombre' => 'Access control', 'objetivo' => 'Ensure authorized access and prevent unauthorized access to the database.'],
        2  => ['nombre' => 'Secure authentication', 'objetivo' => 'Ensure secure authentication procedures in line with the access control policy.'],
        3  => ['nombre' => 'Segregation of duties', 'objetivo' => 'Separate conflicting duties and areas of responsibility to reduce the risk of unauthorized changes.'],
        4  => ['nombre' => 'Change management', 'objetivo' => 'Subject changes to processing facilities to formal change control procedures.'],
        5  => ['nombre' => 'Configuration management', 'objetivo' => 'Establish, document, implement and monitor security configurations.'],
        6  => ['nombre' => 'Event logging', 'objetivo' => 'Produce, store, protect and analyze event logs.'],
        7  => ['nombre' => 'Activity monitoring', 'objetivo' => 'Monitor networks, systems and applications to detect anomalous behavior.'],
        8  => ['nombre' => 'Information backup', 'objetivo' => 'Maintain tested backup copies of information, software and systems.'],
        9  => ['nombre' => 'Redundancy', 'objetivo' => 'Implement sufficient redundancy to meet availability requirements.'],
        10 => ['nombre' => 'IT continuity', 'objetivo' => 'Plan, implement, maintain and test IT continuity in the face of disruptions.'],
        11 => ['nombre' => 'Network security', 'objetivo' => 'Secure and manage networks to protect information in systems and applications.'],
        12 => ['nombre' => 'Use of cryptography', 'objetivo' => 'Define and apply rules for the effective use of cryptography.'],
        13 => ['nombre' => 'Data masking', 'objetivo' => 'Use data masking to limit exposure of sensitive information.'],
        14 => ['nombre' => 'Data leakage prevention', 'objetivo' => 'Apply data leakage prevention (DLP) measures to systems that process sensitive information.'],
        15 => ['nombre' => 'Information classification', 'objetivo' => 'Classify information according to confidentiality, integrity and availability requirements.'],
        16 => ['nombre' => 'Asset inventory', 'objetivo' => 'Develop and maintain an inventory of information assets.'],
        17 => ['nombre' => 'Security in supplier agreements', 'objetivo' => 'Establish and agree on security requirements with suppliers according to the type of relationship.'],
        18 => ['nombre' => 'Capacity management', 'objetivo' => 'Monitor and adjust resource usage based on future capacity projections.'],
        19 => ['nombre' => 'Supplier service management', 'objetivo' => 'Regularly monitor and review services provided by suppliers according to defined agreements.'],
    ],
    'preguntas' => [
        1 => [
            'texto' => 'Is there a formal change control process before modifying database tables/indexes?',
            'ayuda' => 'This asks whether there is a defined process (request, review, approval) before altering the database structure, instead of making direct, spontaneous changes.',
            'niveles' => [
            0 => 'No process exists; anyone can modify the DB structure without notice or approval.',
            1 => 'A change is occasionally announced beforehand, but it depends on the person, with no defined steps.',
            2 => 'Some prior-notice practices exist, but they are not applied consistently to every change.',
            3 => 'A documented request-and-approval procedure exists and is applied to most changes.',
            4 => 'The process is always applied, is supervised, and every change leaves an approval log as evidence.',
            5 => 'The change-control process is measured, periodically audited, and continuously improved.',
            ],
        ],
        2 => [
            'texto' => 'Does the database have referential integrity (foreign keys, constraints, triggers)?',
            'ayuda' => 'This asks whether the database uses technical mechanisms (foreign keys, constraints, triggers) to prevent inconsistent or orphaned data from being stored.',
            'niveles' => [
            0 => 'No referential integrity mechanism is used; data can become inconsistent without the engine preventing it.',
            1 => 'Some constraints exist in isolated spots, with no clear criteria for when to apply them.',
            2 => 'Several tables have foreign keys or constraints, but it is not applied evenly across the whole model.',
            3 => 'The data model documents and applies referential integrity across most table relationships.',
            4 => 'Referential integrity is implemented across the whole model and is reviewed whenever the schema changes.',
            5 => 'It is continuously audited and adjusted as the model evolves, with no unjustified exceptions.',
            ],
        ],
        3 => [
            'texto' => 'Is it logged who modifies the data?',
            'ayuda' => 'This asks whether the system keeps a log of which user made which change and when, so modifications can be traced.',
            'niveles' => [
            0 => 'No log of who modifies the data exists.',
            1 => 'Something is logged manually or sporadically, with no automatic mechanism.',
            2 => 'Automatic logging exists for some tables or processes, but not generally.',
            3 => 'A documented log exists that covers most modification operations.',
            4 => 'Logging covers all relevant modifications and is reviewed periodically.',
            5 => 'The log is audited, actively analyzed, and the logging process is continuously improved.',
            ],
        ],
        4 => [
            'texto' => 'Are backups validated with checksums or signatures?',
            'ayuda' => 'This asks whether, beyond just taking backups, the backup file is verified to be intact and complete (e.g. with a checksum).',
            'niveles' => [
            0 => 'Backups are never validated for integrity or corruption.',
            1 => 'It is checked manually and occasionally, with no consistent method.',
            2 => 'Some validation mechanism exists, but it is not applied to every backup.',
            3 => 'Backups are validated with checksums or signatures, documented for most cases.',
            4 => 'All backups are validated automatically and that validation leaves evidence.',
            5 => 'Backup validation is continuous, measured, and adjusted whenever recurring failures are detected.',
            ],
        ],
        5 => [
            'texto' => 'Is there an inventory of which tables hold critical information?',
            'ayuda' => 'This asks whether a list or classification exists identifying which database tables hold sensitive or business-critical information.',
            'niveles' => [
            0 => 'No inventory of critical tables exists.',
            1 => 'Someone informally knows which tables are critical, but it is not documented.',
            2 => 'A partial inventory exists, covering only some of the relevant tables.',
            3 => 'A documented inventory exists that identifies most of the critical tables.',
            4 => 'The inventory is complete, kept up to date, and used to make protection decisions.',
            5 => 'The inventory is continuously reviewed and updated as the data model changes.',
            ],
        ],
        6 => [
            'texto' => 'Are write privileges restricted to authorized profiles?',
            'ayuda' => 'This asks whether only certain authorized users/roles can insert, modify, or delete data, rather than every user having those permissions.',
            'niveles' => [
            0 => 'Any user with DB access can write/modify data, with no profile restriction.',
            1 => 'Some restriction exists, but it is applied inconsistently or informally.',
            2 => 'Write privileges are limited for some profiles, but not for all that should be.',
            3 => 'A documented policy exists defining which profiles have write privileges.',
            4 => 'Privileges are reviewed and supervised periodically to confirm they remain correct.',
            5 => 'The assignment of write privileges is continuously audited and adjusted.',
            ],
        ],
        7 => [
            'texto' => 'Is individual authentication required (no shared users)?',
            'ayuda' => 'This asks whether each person has their own user account to access the DB, instead of several people sharing a single account.',
            'niveles' => [
            0 => 'Shared or generic accounts are used, with no identification of the actual person.',
            1 => 'Individual accounts exist for some users, but coexist with shared accounts.',
            2 => 'Most users have an individual account, though some exceptions remain.',
            3 => 'It is documented and enforced that every user must have their own account.',
            4 => 'It is actively supervised, with periodic reviews, that no shared accounts exist.',
            5 => 'Compliance with individual authentication is continuously audited and improved.',
            ],
        ],
        8 => [
            'texto' => 'Is there segregation between who codes a change and who approves it?',
            'ayuda' => 'This asks whether the person who develops or codes a DB change is different from the person who reviews and approves it before it is applied.',
            'niveles' => [
            0 => 'The same person codes and approves their own changes, with no external review.',
            1 => 'Another person occasionally reviews, but it is not an established practice.',
            2 => 'Another person reviews some changes, but not all of them.',
            3 => 'It is documented that every change must be approved by someone other than who coded it.',
            4 => 'Segregation is always applied and evidence of who approved each change is kept.',
            5 => 'Compliance with this segregation is measured and continuously adjusted.',
            ],
        ],
        9 => [
            'texto' => 'Does the engine guarantee ACID transactions?',
            'ayuda' => 'This asks whether the database engine ensures operations complete atomically, consistently, isolated, and durably (ACID), avoiding half-saved data.',
            'niveles' => [
            0 => 'It is not used, or it is unknown whether the engine guarantees ACID properties.',
            1 => 'It is assumed to exist, but has not been explicitly verified or configured.',
            2 => 'Transactions are used in some critical processes, but not broadly.',
            3 => 'It is documented and enforced that critical operations use ACID transactions.',
            4 => 'Transaction usage is supervised and verified in periodic technical reviews.',
            5 => 'ACID compliance is audited and continuously adjusted as the system evolves.',
            ],
        ],
        10 => [
            'texto' => 'Is referential consistency reviewed periodically?',
            'ayuda' => 'This asks whether, beyond having foreign keys, someone occasionally verifies that data actually remains consistent across related tables.',
            'niveles' => [
            0 => 'Referential consistency is never reviewed once the system is in production.',
            1 => 'It is reviewed sporadically, only when an obvious problem arises.',
            2 => 'Some periodic review exists, but it does not cover all important relationships.',
            3 => 'A documented process for periodic referential-consistency review exists.',
            4 => 'The review is scheduled and supervised, with results recorded.',
            5 => 'The review process is measured and continuously improved based on findings.',
            ],
        ],
        11 => [
            'texto' => 'Are backups scheduled with restore testing?',
            'ayuda' => 'This asks whether backups run automatically on a defined schedule, and whether it is also tested that they can actually be restored (not just that they are generated).',
            'niveles' => [
            0 => 'No scheduled backups exist, or it has never been tested whether they actually work.',
            1 => 'Occasional backups exist, with no fixed schedule and no restore testing.',
            2 => 'Backups are scheduled, but restore tests are rare or nonexistent.',
            3 => 'Scheduled backups exist and successful restore tests have been documented.',
            4 => 'Backups and their restore tests are supervised periodically and systematically.',
            5 => 'The full process (backup and restore) is audited and continuously improved.',
            ],
        ],
        12 => [
            'texto' => 'Is there redundancy/replication/high availability?',
            'ayuda' => 'This asks whether more than one working copy of the database engine exists (replica), so that if one fails, the service stays available.',
            'niveles' => [
            0 => 'A single database server exists, with no redundancy of any kind.',
            1 => 'Redundancy has been considered but is not actually implemented.',
            2 => 'Partial redundancy exists (e.g., only for some databases or environments).',
            3 => 'A redundancy/replication solution is documented and implemented for critical systems.',
            4 => 'Redundancy is tested and supervised periodically (e.g., failover drills).',
            5 => 'The high-availability scheme is measured, adjusted, and continuously improved.',
            ],
        ],
        13 => [
            'texto' => 'Is capacity monitored (disk, connections, performance)?',
            'ayuda' => 'This asks whether someone actively watches the DB server\'s resource usage (disk space, connection count, response times) to anticipate problems.',
            'niveles' => [
            0 => 'No capacity indicator of the DB server is monitored.',
            1 => 'It is checked manually and occasionally, with no defined tool or routine.',
            2 => 'Some indicators are monitored, but not comprehensively.',
            3 => 'A documented process monitors the key capacity indicators.',
            4 => 'Monitoring is continuous, with configured alerts and periodic trend review.',
            5 => 'Capacity monitoring is continuously adjusted and improved as the system grows.',
            ],
        ],
        14 => [
            'texto' => 'Is there a disaster recovery plan (DRP) for the database?',
            'ayuda' => 'This asks whether a documented plan exists that states, step by step, what to do if the database is lost or unusable due to a disaster (major failure, fire, attack, etc.).',
            'niveles' => [
            0 => 'No disaster recovery plan exists for the database.',
            1 => 'An informal idea of what to do exists, but it is not documented.',
            2 => 'A partial plan exists that does not cover all relevant scenarios.',
            3 => 'A documented DRP exists covering the main database-loss scenarios.',
            4 => 'The DRP is tested periodically (drills) and its currency is supervised.',
            5 => 'The DRP is audited, its effectiveness measured, and continuously improved after each test or incident.',
            ],
        ],
        15 => [
            'texto' => 'Is preventive maintenance performed (reindexing, updating statistics)?',
            'ayuda' => 'This asks whether periodic technical maintenance tasks (like rebuilding indexes or updating statistics) are performed so the engine keeps running efficiently.',
            'niveles' => [
            0 => 'No preventive maintenance is performed on the database.',
            1 => 'Maintenance is done sporadically, only once performance problems already exist.',
            2 => 'Some maintenance tasks exist, but they don\'t cover all critical components.',
            3 => 'A documented preventive-maintenance plan exists and runs regularly.',
            4 => 'Maintenance is supervised and its effectiveness verified after each run.',
            5 => 'The maintenance plan is continuously adjusted and improved based on system behavior.',
            ],
        ],
        16 => [
            'texto' => 'Are there automatic alerts for service outages?',
            'ayuda' => 'This asks whether the system automatically notifies (email, message, etc.) when the database service stops responding, instead of someone noticing by chance.',
            'niveles' => [
            0 => 'No automatic alerts exist; an outage is only detected if someone reports it.',
            1 => 'Some informal notice exists, neither automated nor reliable.',
            2 => 'Automatic alerts exist for some cases, but don\'t cover every outage scenario.',
            3 => 'An automatic alerting system for service outages is documented and implemented.',
            4 => 'Alerts are actively supervised and it is verified that they actually arrive on time.',
            5 => 'The alerting system is continuously adjusted and improved to reduce false positives/negatives.',
            ],
        ],
        17 => [
            'texto' => 'Is the server segmented from untrusted networks?',
            'ayuda' => 'This asks whether the database server is isolated on a separate, protected network, instead of being directly exposed to public or untrusted networks.',
            'niveles' => [
            0 => 'The database server is directly reachable from untrusted networks (including the Internet).',
            1 => 'Some basic restriction exists, but segmentation is not real or consistent.',
            2 => 'Partial segmentation exists, covering some access paths but not all.',
            3 => 'The server is documented as segmented behind a firewall/VLAN from untrusted networks.',
            4 => 'Segmentation is supervised and periodically verified to remain in effect.',
            5 => 'Network segmentation is continuously audited and adjusted against new threats.',
            ],
        ],
        18 => [
            'texto' => 'Is there a defined uptime SLA?',
            'ayuda' => 'This asks whether a formal agreement (with a vendor or internal) exists defining how available the database service must be (e.g., 99.9% of the time).',
            'niveles' => [
            0 => 'No uptime agreement or commitment exists for the service.',
            1 => 'An informal expectation of availability exists, not documented in any agreement.',
            2 => 'A partial or not-fully-formalized SLA exists.',
            3 => 'A documented SLA exists defining the expected uptime of the service.',
            4 => 'SLA compliance is supervised and measured periodically.',
            5 => 'The SLA is continuously reviewed and adjusted based on actual service performance.',
            ],
        ],
        19 => [
            'texto' => 'Is access based on least-privilege roles (RBAC)?',
            'ayuda' => 'This asks whether permissions are assigned by role (e.g. \'analyst\', \'DBA\'), giving each one only what it needs, instead of granting broad access to everyone equally.',
            'niveles' => [
            0 => 'No role scheme exists; permissions are granted freely or equally to everyone.',
            1 => 'Some roles are defined, but the least-privilege principle is not applied.',
            2 => 'Least-privilege roles exist in some areas, not across the whole database.',
            3 => 'An RBAC scheme with least privilege is documented and implemented for most cases.',
            4 => 'The role scheme is supervised and periodically reviewed to detect excessive privileges.',
            5 => 'The RBAC model is continuously audited and adjusted as business needs change.',
            ],
        ],
        20 => [
            'texto' => 'Is sensitive data encrypted at rest and in transit?',
            'ayuda' => 'This asks whether sensitive information is encrypted both when stored on disk (at rest) and when traveling over the network (in transit).',
            'niveles' => [
            0 => 'Sensitive data is not encrypted, neither at rest nor in transit.',
            1 => 'Something is encrypted on an ad hoc basis, with no clear policy of what and how to encrypt.',
            2 => 'Encryption exists at rest or in transit, but not consistently in both cases.',
            3 => 'Encryption of sensitive data at rest and in transit is documented and implemented.',
            4 => 'Encryption use is supervised and periodically verified (e.g. reviewing TLS configurations).',
            5 => 'The encryption strategy is audited and continuously updated against new standards.',
            ],
        ],
        21 => [
            'texto' => 'Do test environments use masked data?',
            'ayuda' => 'This asks whether, when copying real data into a development or test environment, sensitive information is disguised/anonymized instead of used as-is.',
            'niveles' => [
            0 => 'Test environments use real data with no masking at all.',
            1 => 'Something is masked manually and occasionally, with no defined process.',
            2 => 'Partial masking exists, covering some fields but not all sensitive ones.',
            3 => 'A documented masking process is applied to test environments.',
            4 => 'Masking is supervised and it is verified to be applied on every data copy.',
            5 => 'The masking process is continuously audited and improved against new types of sensitive data.',
            ],
        ],
        22 => [
            'texto' => 'Is information classified (public/internal/confidential)?',
            'ayuda' => 'This asks whether the database\'s data is labeled by sensitivity level (e.g. public, internal, confidential), to know how to protect it.',
            'niveles' => [
            0 => 'No classification of the stored information exists.',
            1 => 'Someone has an informal idea of what is sensitive, but it is not documented or labeled.',
            2 => 'Partial classification exists, covering only some tables or fields.',
            3 => 'A classification scheme is documented and applied to most of the information.',
            4 => 'Classification is supervised and periodically reviewed to stay up to date.',
            5 => 'The classification scheme is continuously audited and adjusted as the handled information changes.',
            ],
        ],
        23 => [
            'texto' => 'Are DBA/dev/end-user functions segregated?',
            'ayuda' => 'This asks whether the people who administer the database, those who develop software, and those who merely use it have clearly distinct roles and permissions.',
            'niveles' => [
            0 => 'No separation of duties exists; the same people administer, develop, and use the DB without distinction.',
            1 => 'Some informal separation exists, but it is not defined or always respected.',
            2 => 'Partial separation of duties exists, applied in some cases but not generally.',
            3 => 'It is documented and enforced that DBA, development, and end-user have distinct roles and permissions.',
            4 => 'Segregation of duties is supervised and periodically reviewed to detect improper mixing.',
            5 => 'The segregation model is continuously audited and adjusted as the team grows or operations change.',
            ],
        ],
        24 => [
            'texto' => 'Is multi-factor authentication required for sensitive data?',
            'ayuda' => 'This asks whether, to access sensitive information, a second factor (like a temporary code or an authenticator app) is required in addition to the password.',
            'niveles' => [
            0 => 'Access to sensitive data only requires a username and password, no second factor.',
            1 => 'MFA exists but is optional or used inconsistently among users.',
            2 => 'MFA is enabled for some users or access paths, not for everyone handling sensitive data.',
            3 => 'It is documented and enforced that access to sensitive data requires MFA.',
            4 => 'MFA compliance is supervised and periodically reviewed to detect exceptions.',
            5 => 'The multi-factor authentication strategy is continuously audited and improved.',
            ],
        ],
        25 => [
            'texto' => 'Do third parties with access sign a confidentiality agreement?',
            'ayuda' => 'This asks whether any vendor or external person with database access signs a legal commitment not to disclose the information they see.',
            'niveles' => [
            0 => 'Third parties access the database with no signed confidentiality agreement.',
            1 => 'An agreement is signed occasionally, without being an established practice.',
            2 => 'The agreement exists for some vendors, but not for everyone with access.',
            3 => 'It is documented and enforced that every third party with access signs a confidentiality agreement.',
            4 => 'Compliance with this policy is supervised and periodically verified.',
            5 => 'Third-party agreement management is continuously audited and improved.',
            ],
        ],
        26 => [
            'texto' => 'Are there anti data-leakage (DLP) controls on bulk exports?',
            'ayuda' => 'This asks whether mechanisms exist to detect or block someone from extracting large amounts of sensitive information from the database without authorization.',
            'niveles' => [
            0 => 'No control exists to detect or limit bulk data exports.',
            1 => 'Some informal control exists (e.g. occasional manual review), with no specific tool.',
            2 => 'DLP controls exist for some scenarios, not for every export method.',
            3 => 'A DLP control covering the relevant bulk exports is documented and implemented.',
            4 => 'DLP control operation is supervised and its effectiveness periodically reviewed.',
            5 => 'The data-leakage prevention strategy is continuously audited and improved against new exfiltration techniques.',
            ],
        ],
    ],
    'opciones_respuesta' => [
        'si' => 'Yes',
        'no' => 'No',
        'na' => 'N/A',
    ],
    'niveles_madurez' => [
        0 => 'Nonexistent',
        1 => 'Informal',
        2 => 'Partial',
        3 => 'Documented',
        4 => 'Implemented and monitored',
        5 => 'Continuous improvement',
    ],
    'recomendaciones' => [
        'D' => 'It is recommended to strengthen backup, disaster recovery, monitoring and operational continuity mechanisms.',
        'C' => 'It is recommended to strengthen access control, authentication, encryption and protection of sensitive information.',
        'I' => 'It is recommended to improve change control, auditing, data validation and segregation of duties.',
    ],
];

$textos = ($LANG ?? 'en') === 'es' ? $textos_es : $textos_en;

$grupos = $textos['grupos'];
$dominios = $textos['dominios'];
$opciones_respuesta = $textos['opciones_respuesta'];
$niveles_madurez = $textos['niveles_madurez'];
$recomendaciones = $textos['recomendaciones'];

$controles_estructura = [
    [ 'id' => 1,  'codigo' => '5.15', 'dominio' => 'ORG', 'grupo' => 'C', 'peso' => 3,
      'peso_c' => 2.5, 'peso_i' => 2.5, 'peso_d' => 0.5,
      'preguntas' => [19, 6] ],
    [ 'id' => 2,  'codigo' => '8.5', 'dominio' => 'TEC', 'grupo' => 'C', 'peso' => 3,
      'peso_c' => 3, 'peso_i' => 1, 'peso_d' => 0,
      'preguntas' => [7, 24] ],
    [ 'id' => 3,  'codigo' => '5.3', 'dominio' => 'ORG', 'grupo' => 'I', 'peso' => 2,
      'peso_c' => 1.5, 'peso_i' => 2.5, 'peso_d' => 0,
      'preguntas' => [8, 23] ],
    [ 'id' => 4,  'codigo' => '8.32', 'dominio' => 'TEC', 'grupo' => 'I', 'peso' => 3,
      'peso_c' => 1, 'peso_i' => 3, 'peso_d' => 1,
      'preguntas' => [1] ],
    [ 'id' => 5,  'codigo' => '8.9', 'dominio' => 'TEC', 'grupo' => 'I', 'peso' => 2,
      'peso_c' => 0, 'peso_i' => 3, 'peso_d' => 1,
      'preguntas' => [2, 9] ],
    [ 'id' => 6,  'codigo' => '8.15', 'dominio' => 'TEC', 'grupo' => 'I', 'peso' => 2,
      'peso_c' => 2, 'peso_i' => 3, 'peso_d' => 0,
      'preguntas' => [3] ],
    [ 'id' => 7,  'codigo' => '8.16', 'dominio' => 'TEC', 'grupo' => 'D', 'peso' => 2,
      'peso_c' => 0, 'peso_i' => 1, 'peso_d' => 2.3,
      'preguntas' => [10, 13, 16] ],
    [ 'id' => 8,  'codigo' => '8.13', 'dominio' => 'TEC', 'grupo' => 'D', 'peso' => 3,
      'peso_c' => 0, 'peso_i' => 2, 'peso_d' => 2.5,
      'preguntas' => [4, 11] ],
    [ 'id' => 9,  'codigo' => '8.14', 'dominio' => 'TEC', 'grupo' => 'D', 'peso' => 2,
      'peso_c' => 0, 'peso_i' => 1, 'peso_d' => 3,
      'preguntas' => [12] ],
    [ 'id' => 10, 'codigo' => '5.30', 'dominio' => 'ORG', 'grupo' => 'D', 'peso' => 3,
      'peso_c' => 0, 'peso_i' => 1, 'peso_d' => 3,
      'preguntas' => [14] ],
    [ 'id' => 11, 'codigo' => '8.20', 'dominio' => 'TEC', 'grupo' => 'C', 'peso' => 2,
      'peso_c' => 3, 'peso_i' => 0, 'peso_d' => 2,
      'preguntas' => [17] ],
    [ 'id' => 12, 'codigo' => '8.24', 'dominio' => 'TEC', 'grupo' => 'C', 'peso' => 3,
      'peso_c' => 3, 'peso_i' => 1, 'peso_d' => 0,
      'preguntas' => [20] ],
    [ 'id' => 13, 'codigo' => '8.11', 'dominio' => 'TEC', 'grupo' => 'C', 'peso' => 2,
      'peso_c' => 3, 'peso_i' => 0, 'peso_d' => 0,
      'preguntas' => [21] ],
    [ 'id' => 14, 'codigo' => '8.12', 'dominio' => 'TEC', 'grupo' => 'C', 'peso' => 2,
      'peso_c' => 3, 'peso_i' => 1, 'peso_d' => 0,
      'preguntas' => [26] ],
    [ 'id' => 15, 'codigo' => '5.12', 'dominio' => 'ORG', 'grupo' => 'C', 'peso' => 2,
      'peso_c' => 3, 'peso_i' => 1, 'peso_d' => 0,
      'preguntas' => [22] ],
    [ 'id' => 16, 'codigo' => '5.9', 'dominio' => 'ORG', 'grupo' => 'C', 'peso' => 1,
      'peso_c' => 2, 'peso_i' => 2, 'peso_d' => 1,
      'preguntas' => [5] ],
    [ 'id' => 17, 'codigo' => '5.20', 'dominio' => 'ORG', 'grupo' => 'C', 'peso' => 1,
      'peso_c' => 2, 'peso_i' => 0, 'peso_d' => 0,
      'preguntas' => [25] ],
    [ 'id' => 18, 'codigo' => '8.6', 'dominio' => 'TEC', 'grupo' => 'D', 'peso' => 2,
      'peso_c' => 0, 'peso_i' => 2, 'peso_d' => 2,
      'preguntas' => [15] ],
    [ 'id' => 19, 'codigo' => '5.22', 'dominio' => 'ORG', 'grupo' => 'D', 'peso' => 2,
      'peso_c' => 0, 'peso_i' => 0, 'peso_d' => 3,
      'preguntas' => [18] ],
];

$controles = array_map(function ($c) use ($textos) {
    $c['nombre']   = $textos['controles'][$c['id']]['nombre'];
    $c['objetivo'] = $textos['controles'][$c['id']]['objetivo'];
    $c['preguntas'] = array_map(function ($pid) use ($textos) {
        return [
            'id'      => $pid,
            'texto'   => $textos['preguntas'][$pid]['texto'],
            'ayuda'   => $textos['preguntas'][$pid]['ayuda'],
            'niveles' => $textos['preguntas'][$pid]['niveles'],
        ];
    }, $c['preguntas']);
    return $c;
}, $controles_estructura);