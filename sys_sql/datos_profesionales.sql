-- Tabla para almacenar los datos profesionales de las personas
CREATE TABLE IF NOT EXISTS person_professional (
    professional_id SERIAL PRIMARY KEY,
    person_id INT NOT NULL,
    profesion VARCHAR(100),
    direccion_corporativa VARCHAR(255),
    email_profesional VARCHAR(100),
    denominacion_corporativa VARCHAR(255),
    ruc VARCHAR(50),
    whatsapp VARCHAR(50),
    plan VARCHAR(50),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES rh_person(person_id) ON DELETE CASCADE
);

-- Índice para búsquedas rápidas por person_id
CREATE INDEX IF NOT EXISTS idx_person_professional_person_id ON person_professional(person_id);