-- =====================================================================
-- RiskGuard - Esquema de base de datos (MySQL / MariaDB)
-- Compatible con InfinityFree. Ejecutar completo en phpMyAdmin
-- (pestaña "Importar"), en una base de datos vacía.
-- =====================================================================

-- ================= CATÁLOGO (controles ISO/IEC 27002) =================

CREATE TABLE IF NOT EXISTS dominios (
    id      VARCHAR(10) PRIMARY KEY,
    nombre  VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS controles (
    id          INT PRIMARY KEY,
    codigo_iso  VARCHAR(10) NOT NULL,
    nombre      VARCHAR(150) NOT NULL,
    dominio_id  VARCHAR(10) NOT NULL,
    grupo       CHAR(1) NOT NULL,
    peso        TINYINT NOT NULL,
    peso_c      DECIMAL(3,1) NOT NULL,
    peso_i      DECIMAL(3,1) NOT NULL,
    peso_d      DECIMAL(3,1) NOT NULL,
    objetivo    VARCHAR(255) NOT NULL,
    CONSTRAINT fk_controles_dominio FOREIGN KEY (dominio_id) REFERENCES dominios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS preguntas (
    id          INT PRIMARY KEY,
    control_id  INT NOT NULL,
    texto       VARCHAR(255) NOT NULL,
    CONSTRAINT fk_preguntas_control FOREIGN KEY (control_id) REFERENCES controles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================= DATOS TRANSACCIONALES =================

CREATE TABLE IF NOT EXISTS empresas (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nombre              VARCHAR(150) NOT NULL,
    nombre_normalizado  VARCHAR(150) NOT NULL,
    creado_en           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_empresas_nombre_normalizado (nombre_normalizado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS usuarios (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id      INT NULL,
    nombre_usuario  VARCHAR(100) NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    rol             ENUM('empresa','admin') NOT NULL DEFAULT 'empresa',
    creado_en       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuarios_nombre_usuario (nombre_usuario),
    CONSTRAINT fk_usuarios_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS evaluaciones (
    id                     INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id             INT NOT NULL,
    usuario_id             INT NULL,
    evaluador              VARCHAR(150) NOT NULL,
    area_evaluada          VARCHAR(150) NULL,
    dba                    VARCHAR(150) NULL,
    fecha_evaluacion       DATE NOT NULL,
    pct_confidencialidad   DECIMAL(5,2) NOT NULL,
    pct_integridad         DECIMAL(5,2) NOT NULL,
    pct_disponibilidad     DECIMAL(5,2) NOT NULL,
    pct_global             DECIMAL(5,2) NOT NULL,
    creado_en              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_evaluaciones_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    CONSTRAINT fk_evaluaciones_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    KEY idx_evaluaciones_empresa_id (empresa_id),
    KEY idx_evaluaciones_fecha (fecha_evaluacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS evaluacion_respuestas (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    evaluacion_id  INT NOT NULL,
    pregunta_id    INT NOT NULL,
    respuesta      ENUM('si','no','na') NOT NULL,
    nivel_madurez  TINYINT NULL,
    comentario     TEXT NULL,
    CONSTRAINT fk_respuestas_evaluacion FOREIGN KEY (evaluacion_id) REFERENCES evaluaciones(id) ON DELETE CASCADE,
    CONSTRAINT fk_respuestas_pregunta FOREIGN KEY (pregunta_id) REFERENCES preguntas(id),
    KEY idx_respuestas_evaluacion_id (evaluacion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS evaluacion_control_resultado (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    evaluacion_id      INT NOT NULL,
    control_id         INT NOT NULL,
    pct_cumplimiento   DECIMAL(5,2) NOT NULL,
    nivel_madurez      TINYINT NOT NULL,
    CONSTRAINT fk_resultado_evaluacion FOREIGN KEY (evaluacion_id) REFERENCES evaluaciones(id) ON DELETE CASCADE,
    CONSTRAINT fk_resultado_control FOREIGN KEY (control_id) REFERENCES controles(id),
    KEY idx_resultado_evaluacion_id (evaluacion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS evaluacion_borradores (
                                                     id              INT AUTO_INCREMENT PRIMARY KEY,
                                                     empresa_id      INT NOT NULL,
                                                     usuario_id      INT NULL,
                                                     organizacion    VARCHAR(150),
    evaluador       VARCHAR(150),
    fecha           DATE,
    respuestas_json LONGTEXT NOT NULL,
    actualizado_en  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_empresa (empresa_id),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- SEED DEL CATÁLOGO (debe coincidir con includes/cuestionario-data.php)
-- =====================================================================

INSERT INTO dominios (id, nombre) VALUES
 ('ORG', 'Organizacional'),
 ('TEC', 'Tecnológico')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT INTO controles (id, codigo_iso, nombre, dominio_id, grupo, peso, peso_c, peso_i, peso_d, objetivo) VALUES
 (1,  '5.15', 'Control de acceso', 'ORG', 'C', 3, 2.5, 2.5, 0.5, 'Asegurar el acceso autorizado y prevenir el acceso no autorizado a la base de datos.'),
 (2,  '8.5',  'Autenticación segura', 'TEC', 'C', 3, 3, 1, 0, 'Garantizar procedimientos de autenticación seguros de acuerdo con la política de control de acceso.'),
 (3,  '5.3',  'Segregación de funciones', 'ORG', 'I', 2, 1.5, 2.5, 0, 'Separar funciones y áreas de responsabilidad en conflicto para reducir el riesgo de modificaciones no autorizadas.'),
 (4,  '8.32', 'Gestión de cambios', 'TEC', 'I', 3, 1, 3, 1, 'Someter los cambios a las instalaciones de procesamiento a procedimientos de control de cambios.'),
 (5,  '8.9',  'Gestión de configuración', 'TEC', 'I', 2, 0, 3, 1, 'Establecer, documentar, implementar y monitorear las configuraciones de seguridad.'),
 (6,  '8.15', 'Registro de eventos (logging)', 'TEC', 'I', 2, 2, 3, 0, 'Producir, almacenar, proteger y analizar registros de eventos.'),
 (7,  '8.16', 'Monitoreo de actividades', 'TEC', 'D', 2, 0, 1, 2.3, 'Monitorear redes, sistemas y aplicaciones para detectar comportamiento anómalo.'),
 (8,  '8.13', 'Respaldo de información', 'TEC', 'D', 3, 0, 2, 2.5, 'Mantener copias de respaldo probadas de la información, software y sistemas.'),
 (9,  '8.14', 'Redundancia', 'TEC', 'D', 2, 0, 1, 3, 'Implementar redundancia suficiente para cumplir los requisitos de disponibilidad.'),
 (10, '5.30', 'Continuidad de TI', 'ORG', 'D', 3, 0, 1, 3, 'Planificar, implementar, mantener y probar la continuidad de TI ante disrupciones.'),
 (11, '8.20', 'Seguridad de redes', 'TEC', 'C', 2, 3, 0, 2, 'Asegurar y gestionar las redes para proteger la información en sistemas y aplicaciones.'),
 (12, '8.24', 'Uso de criptografía', 'TEC', 'C', 3, 3, 1, 0, 'Definir y aplicar reglas para el uso efectivo de la criptografía.'),
 (13, '8.11', 'Enmascaramiento de datos', 'TEC', 'C', 2, 3, 0, 0, 'Usar enmascaramiento de datos para limitar la exposición de información sensible.'),
 (14, '8.12', 'Prevención de fuga de datos', 'TEC', 'C', 2, 3, 1, 0, 'Aplicar medidas de prevención de fuga de datos (DLP) a sistemas que procesan información sensible.'),
 (15, '5.12', 'Clasificación de la información', 'ORG', 'C', 2, 3, 1, 0, 'Clasificar la información según los requisitos de confidencialidad, integridad y disponibilidad.'),
 (16, '5.9',  'Inventario de activos', 'ORG', 'C', 1, 2, 2, 1, 'Desarrollar y mantener un inventario de activos de información.'),
 (17, '5.20', 'Seguridad en acuerdos con proveedores', 'ORG', 'C', 1, 2, 0, 0, 'Establecer y acordar requisitos de seguridad con proveedores según el tipo de relación.'),
 (18, '8.6',  'Gestión de capacidad', 'TEC', 'D', 2, 0, 2, 2, 'Monitorear y ajustar el uso de recursos según proyecciones de capacidad futura.'),
 (19, '5.22', 'Gestión de servicios de proveedores', 'ORG', 'D', 2, 0, 0, 3, 'Monitorear y revisar regularmente los servicios prestados por proveedores según acuerdos definidos.')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT INTO preguntas (id, control_id, texto) VALUES
 (19, 1,  '¿El acceso es por roles con privilegio mínimo (RBAC)?'),
 (6,  1,  '¿Los privilegios de escritura están restringidos a perfiles autorizados?'),
 (7,  2,  '¿Se exige autenticación individual (no usuarios compartidos)?'),
 (24, 2,  '¿Se exige autenticación multifactor para datos sensibles?'),
 (8,  3,  '¿Hay segregación entre quien programa un cambio y quien lo aprueba?'),
 (23, 3,  '¿Están segregadas las funciones de DBA/dev/usuario final?'),
 (1,  4,  '¿Existe control de cambios formal antes de modificar tablas/índices de la BD?'),
 (2,  5,  '¿La BD tiene integridad referencial (llaves foráneas, constraints, triggers)?'),
 (9,  5,  '¿El motor garantiza transacciones ACID?'),
 (3,  6,  '¿Se registra en bitácora quién modifica los datos?'),
 (10, 7,  '¿Se revisa periódicamente la consistencia referencial?'),
 (13, 7,  '¿Se monitorea capacidad (disco, conexiones, rendimiento)?'),
 (16, 7,  '¿Hay alertas automáticas ante caídas del servicio?'),
 (4,  8,  '¿Se validan los respaldos con checksums o firmas?'),
 (11, 8,  '¿Hay backups programados con pruebas de restauración?'),
 (12, 9,  '¿Existe redundancia/replicación/alta disponibilidad?'),
 (14, 10, '¿Hay un plan de recuperación ante desastres (DRP) para la BD?'),
 (17, 11, '¿El servidor está segmentado de redes no confiables?'),
 (20, 12, '¿Los datos sensibles se cifran en reposo y tránsito?'),
 (21, 13, '¿Los ambientes de prueba usan datos enmascarados?'),
 (26, 14, '¿Hay controles anti fuga de datos (DLP) en exportaciones masivas?'),
 (22, 15, '¿Hay clasificación de la información (pública/interna/confidencial)?'),
 (5,  16, '¿Hay inventario de qué tablas tienen información crítica?'),
 (25, 17, '¿Los terceros con acceso firman acuerdo de confidencialidad?'),
 (15, 18, '¿Se hace mantenimiento preventivo (reindexar, actualizar estadísticas)?'),
 (18, 19, '¿Hay un SLA de tiempo de actividad definido?')
ON DUPLICATE KEY UPDATE texto = VALUES(texto);
