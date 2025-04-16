-- Estructura de tabla para almacenar archivos
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

-- Índices para mejorar el rendimiento
CREATE INDEX idx_archivos_persona ON archivos(id_persona);
CREATE INDEX idx_archivos_usuario ON archivos(id_usuario);

-- Comentario en la tabla
COMMENT ON TABLE archivos IS 'Almacena información de archivos subidos al sistema';