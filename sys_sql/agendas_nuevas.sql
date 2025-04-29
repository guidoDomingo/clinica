-- Archivo: agendas_nuevas.sql
-- Descripción: Script para crear las tablas necesarias para el módulo de agendas médicas con la nueva estructura

-- Tabla de consultorios médicos
CREATE TABLE IF NOT EXISTS sch_medical_offices (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado SMALLINT DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de agendas médicas
CREATE TABLE IF NOT EXISTS sch_medical_hs (
    id SERIAL PRIMARY KEY,
    medico_id INTEGER NOT NULL,
    dias VARCHAR(20) NOT NULL, -- Formato: '1,2,3,4,5' (donde 1=lunes, 2=martes, etc.)
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    duracion_turno INTEGER NOT NULL, -- Duración en minutos
    consultorio_id INTEGER NOT NULL,
    estado SMALLINT DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medico_id) REFERENCES rh_person(person_id),
    FOREIGN KEY (consultorio_id) REFERENCES sch_medical_offices(id)
);

-- Tabla de bloqueos de agenda
CREATE TABLE IF NOT EXISTS medico_bloqueos (
    id SERIAL PRIMARY KEY,
    medico_id INTEGER NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    motivo TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medico_id) REFERENCES rh_person(person_id)
);

-- Tabla de turnos (citas)
CREATE TABLE IF NOT EXISTS sch_shifts (
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
    FOREIGN KEY (paciente_id) REFERENCES rh_person(person_id),
    FOREIGN KEY (agenda_id) REFERENCES sch_medical_hs(id)
);

-- Insertar algunos consultorios de ejemplo
INSERT INTO sch_medical_offices (nombre, descripcion) VALUES
('Consultorio 101', 'Consultorio principal planta baja'),
('Consultorio 102', 'Consultorio secundario planta baja'),
('Consultorio 201', 'Consultorio principal primer piso'),
('Consultorio 202', 'Consultorio secundario primer piso')
ON CONFLICT (id) DO NOTHING;

-- Migración de datos (si existen las tablas anteriores)
-- Migrar consultorios
INSERT INTO sch_medical_offices (id, nombre, descripcion, estado, fecha_creacion)
SELECT id, nombre, descripcion, estado, fecha_creacion
FROM consultorios
ON CONFLICT (id) DO NOTHING;

-- Migrar agendas
INSERT INTO sch_medical_hs (id, medico_id, dias, hora_inicio, hora_fin, duracion_turno, consultorio_id, estado, fecha_creacion)
SELECT id, medico_id, dias, hora_inicio, hora_fin, duracion_turno, consultorio_id, estado, fecha_creacion
FROM agendas
ON CONFLICT (id) DO NOTHING;

-- Migrar bloqueos
INSERT INTO medico_bloqueos (id, medico_id, fecha_inicio, fecha_fin, motivo, fecha_creacion)
SELECT id, medico_id, fecha_inicio, fecha_fin, motivo, fecha_creacion
FROM bloqueos_agenda
ON CONFLICT (id) DO NOTHING;

-- Migrar citas/turnos
INSERT INTO sch_shifts (id, paciente_id, agenda_id, fecha, hora_inicio, hora_fin, estado, motivo, observaciones, fecha_creacion)
SELECT id, paciente_id, agenda_id, fecha, hora_inicio, hora_fin, estado, motivo, observaciones, fecha_creacion
FROM citas
ON CONFLICT (id) DO NOTHING;