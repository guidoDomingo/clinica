-- Estructura de tablas para el sistema de consultas médicas

-- Tabla para almacenar archivos (debe crearse antes de archivos_consulta)
CREATE TABLE IF NOT EXISTS archivos (
    id_archivo SERIAL PRIMARY KEY,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    id_usuario INT,
    id_persona INT,
    origen VARCHAR(100),
    observaciones TEXT,
    tamano_archivo BIGINT,
    tipo_archivo VARCHAR(100),
    checksum VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_persona_archivo FOREIGN KEY (id_persona) REFERENCES rh_person(person_id) ON DELETE CASCADE
);

-- Índices para mejorar el rendimiento de archivos
CREATE INDEX idx_archivos_persona ON archivos(id_persona);
CREATE INDEX idx_archivos_usuario ON archivos(id_usuario);

-- Tabla para almacenar los motivos comunes de consulta
CREATE TABLE IF NOT EXISTS motivos_comunes (
    id_motivo SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    creado_por INT
);

-- Tabla para almacenar los preformatos de consulta
CREATE TABLE IF NOT EXISTS preformatos (
    id_preformato SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    tipo VARCHAR(50) NOT NULL, -- 'consulta' o 'receta'
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    creado_por INT
);

-- Tabla principal de consultas (si no existe)
CREATE TABLE IF NOT EXISTS consultas (
    id_consulta SERIAL PRIMARY KEY,
    id_persona INT NOT NULL,
    motivoscomunes VARCHAR(255),
    txtmotivo VARCHAR(255),
    visionod VARCHAR(50),
    visionoi VARCHAR(50),
    tensionod VARCHAR(50),
    tensionoi VARCHAR(50),
    consulta_textarea TEXT,
    receta_textarea TEXT,
    txtnota TEXT,
    proximaconsulta DATE,
    whatsapptxt VARCHAR(50),
    email VARCHAR(100),
    id_user INT,
    id_reserva INT DEFAULT 0,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_modificacion TIMESTAMP,
    CONSTRAINT fk_persona FOREIGN KEY (id_persona) REFERENCES rh_person(person_id) ON DELETE CASCADE
);

-- Tabla para archivos adjuntos a consultas
CREATE TABLE IF NOT EXISTS archivos_consulta (
    id_archivo_consulta SERIAL PRIMARY KEY,
    id_consulta INT NOT NULL,
    id_archivo INT NOT NULL,
    fecha_adjunto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_consulta FOREIGN KEY (id_consulta) REFERENCES consultas(id_consulta) ON DELETE CASCADE,
    CONSTRAINT fk_archivo FOREIGN KEY (id_archivo) REFERENCES archivos(id_archivo) ON DELETE CASCADE
);

-- Índices para mejorar el rendimiento
CREATE INDEX idx_consultas_persona ON consultas(id_persona);
CREATE INDEX idx_archivos_consulta ON archivos_consulta(id_consulta);

-- Comentarios en las tablas
COMMENT ON TABLE motivos_comunes IS 'Catálogo de motivos comunes de consulta';
COMMENT ON TABLE preformatos IS 'Plantillas predefinidas para consultas y recetas';
COMMENT ON TABLE consultas IS 'Registro de consultas médicas';
COMMENT ON TABLE archivos IS 'Almacena información de archivos subidos al sistema';
COMMENT ON TABLE archivos_consulta IS 'Relación entre consultas y archivos adjuntos';