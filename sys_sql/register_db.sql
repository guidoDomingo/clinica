CREATE TABLE usuario_registrados (
    id SERIAL PRIMARY KEY, -- Identificador único del usuario
    nombres VARCHAR(100) NOT NULL, -- Nombre(s) del usuario
    email VARCHAR(100) UNIQUE NOT NULL, -- Correo electrónico del usuario (único)
    telefono VARCHAR(15), -- Número de teléfono del usuario
    pass_user VARCHAR(254) NOT NULL, -- Contraseña del usuario (en texto plano, no recomendado)
    passsecure VARCHAR(254) NOT NULL, -- Contraseña segura (hash o cifrada)
    register TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Fecha de registro del usuario
    email_send BOOLEAN DEFAULT FALSE, -- Indica si el correo de confirmación fue enviado exitosamente
    estado VARCHAR(20) DEFAULT 'activo', -- Estado del usuario (activo, inactivo, suspendido, etc.)
    ultimo_ingreso TIMESTAMP default null-- Fecha y hora del último ingreso del usuario
);