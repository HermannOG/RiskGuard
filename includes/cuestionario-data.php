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
        1  => '¿Existe control de cambios formal antes de modificar tablas/índices de la BD?',
        2  => '¿La BD tiene integridad referencial (llaves foráneas, constraints, triggers)?',
        3  => '¿Se registra en bitácora quién modifica los datos?',
        4  => '¿Se validan los respaldos con checksums o firmas?',
        5  => '¿Hay inventario de qué tablas tienen información crítica?',
        6  => '¿Los privilegios de escritura están restringidos a perfiles autorizados?',
        7  => '¿Se exige autenticación individual (no usuarios compartidos)?',
        8  => '¿Hay segregación entre quien programa un cambio y quien lo aprueba?',
        9  => '¿El motor garantiza transacciones ACID?',
        10 => '¿Se revisa periódicamente la consistencia referencial?',
        11 => '¿Hay backups programados con pruebas de restauración?',
        12 => '¿Existe redundancia/replicación/alta disponibilidad?',
        13 => '¿Se monitorea capacidad (disco, conexiones, rendimiento)?',
        14 => '¿Hay un plan de recuperación ante desastres (DRP) para la BD?',
        15 => '¿Se hace mantenimiento preventivo (reindexar, actualizar estadísticas)?',
        16 => '¿Hay alertas automáticas ante caídas del servicio?',
        17 => '¿El servidor está segmentado de redes no confiables?',
        18 => '¿Hay un SLA de tiempo de actividad definido?',
        19 => '¿El acceso es por roles con privilegio mínimo (RBAC)?',
        20 => '¿Los datos sensibles se cifran en reposo y tránsito?',
        21 => '¿Los ambientes de prueba usan datos enmascarados?',
        22 => '¿Hay clasificación de la información (pública/interna/confidencial)?',
        23 => '¿Están segregadas las funciones de DBA/dev/usuario final?',
        24 => '¿Se exige autenticación multifactor para datos sensibles?',
        25 => '¿Los terceros con acceso firman acuerdo de confidencialidad?',
        26 => '¿Hay controles anti fuga de datos (DLP) en exportaciones masivas?',
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
        1  => 'Is there a formal change control process before modifying database tables/indexes?',
        2  => 'Does the database have referential integrity (foreign keys, constraints, triggers)?',
        3  => 'Is it logged who modifies the data?',
        4  => 'Are backups validated with checksums or signatures?',
        5  => 'Is there an inventory of which tables hold critical information?',
        6  => 'Are write privileges restricted to authorized profiles?',
        7  => 'Is individual authentication required (no shared users)?',
        8  => 'Is there segregation between who codes a change and who approves it?',
        9  => 'Does the engine guarantee ACID transactions?',
        10 => 'Is referential consistency reviewed periodically?',
        11 => 'Are backups scheduled with restore testing?',
        12 => 'Is there redundancy/replication/high availability?',
        13 => 'Is capacity monitored (disk, connections, performance)?',
        14 => 'Is there a disaster recovery plan (DRP) for the database?',
        15 => 'Is preventive maintenance performed (reindexing, updating statistics)?',
        16 => 'Are there automatic alerts for service outages?',
        17 => 'Is the server segmented from untrusted networks?',
        18 => 'Is there a defined uptime SLA?',
        19 => 'Is access based on least-privilege roles (RBAC)?',
        20 => 'Is sensitive data encrypted at rest and in transit?',
        21 => 'Do test environments use masked data?',
        22 => 'Is information classified (public/internal/confidential)?',
        23 => 'Are DBA/dev/end-user functions segregated?',
        24 => 'Is multi-factor authentication required for sensitive data?',
        25 => 'Do third parties with access sign a confidentiality agreement?',
        26 => 'Are there anti data-leakage (DLP) controls on bulk exports?',
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
        return ['id' => $pid, 'texto' => $textos['preguntas'][$pid]];
    }, $c['preguntas']);
    return $c;
}, $controles_estructura);
