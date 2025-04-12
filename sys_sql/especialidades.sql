-- Tabla para almacenar las especialidades
CREATE TABLE IF NOT EXISTS especialidades (
    especialidad_id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla para la relación muchos a muchos entre personas y especialidades
CREATE TABLE IF NOT EXISTS persona_especialidad (
    persona_id INT NOT NULL,
    especialidad_id INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (persona_id, especialidad_id),
    FOREIGN KEY (persona_id) REFERENCES rh_person(person_id) ON DELETE CASCADE,
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(especialidad_id) ON DELETE CASCADE
);

-- Insertar algunas especialidades comunes
INSERT INTO especialidades (nombre, descripcion) VALUES
('Cirugía Refractiva', 'Especialidad enfocada en corrección de problemas refractivos'),
('Glaucoma', 'Tratamiento y manejo de glaucoma'),
('Cardiología', 'Especialidad médica que se ocupa de las enfermedades del corazón'),
('Dermatología', 'Especialidad médica encargada del estudio de la piel'),
('Neurología', 'Especialidad médica que trata los trastornos del sistema nervioso'),
('Pediatría', 'Especialidad médica que estudia al niño y sus enfermedades'),
('Oftalmología', 'Especialidad médica que estudia las enfermedades del ojo'),
('Traumatología', 'Especialidad médica que se ocupa de las lesiones del aparato locomotor'),
('Ginecología', 'Especialidad médica que trata las enfermedades del sistema reproductor femenino'),
('Urología', 'Especialidad médica que se ocupa del estudio, diagnóstico y tratamiento de las patologías del aparato urinario');