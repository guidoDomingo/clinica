-- Archivo: agendas.sql
-- Descripción: Script para crear las tablas necesarias para el módulo de agendas médicas

-- Tabla de consultorios
CREATE TABLE IF NOT EXISTS consultorios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado SMALLINT DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de agendas médicas
CREATE TABLE IF NOT EXISTS agendas (
    id SERIAL PRIMARY KEY,
    medico_id INTEGER NOT NULL,
    dias VARCHAR(20) NOT NULL, -- Formato: '1,2,3,4,5' (donde 1=lunes, 2=martes, etc.)
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    duracion_turno INTEGER NOT NULL, -- Duración en minutos
    consultorio_id INTEGER NOT NULL,
    estado SMALLINT DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medico_id) REFERENCES personas(id),
    FOREIGN KEY (consultorio_id) REFERENCES consultorios(id)
);

-- Tabla de bloqueos de agenda
CREATE TABLE IF NOT EXISTS bloqueos_agenda (
    id SERIAL PRIMARY KEY,
    medico_id INTEGER NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    motivo TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medico_id) REFERENCES personas(id)
);

-- Tabla de citas (referenciada por el modelo de agendas)
CREATE TABLE IF NOT EXISTS citas (
    id SERIAL PRIMARY KEY,
    paciente_id INTEGER NOT NULL,
    agenda_id INTEGER NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    estado SMALLINT DEFAULT 1, -- 1: Pendiente, 2: Confirmada, 3: Cancelada, 4: Completada
    motivo TEXT,
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES personas(id),
    FOREIGN KEY (agenda_id) REFERENCES agendas(id)
);

-- Insertar algunos consultorios de ejemplo
INSERT INTO consultorios (nombre, descripcion) VALUES
('Consultorio 101', 'Consultorio principal planta baja'),
('Consultorio 102', 'Consultorio secundario planta baja'),
('Consultorio 201', 'Consultorio principal primer piso'),
('Consultorio 202', 'Consultorio secundario primer piso')
ON CONFLICT (id) DO NOTHING;