--SELECT id_persona, documento, nro_ficha, nombres, apellidos, fecha_registro 	FROM public.personas;
--DELETE FROM public.consultas
--DELETE FROM ARCHIVOS
--DELETE FROM 
--SELECT * FROM CONSULTAS
--DROP TABLE personas CASCADE;
	CREATE TABLE personas (
    id_persona SERIAL PRIMARY KEY,
    documento VARCHAR(200) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    nombres VARCHAR(255) NOT NULL,
    apellidos VARCHAR(255) NOT NULL,
    telefono VARCHAR(30),
    sexo VARCHAR(1),
    nro_ficha VARCHAR(50),
    direccion VARCHAR(255),
    email VARCHAR(100),
    departamento INT,
    ciudad INT,
    menor BOOLEAN,
    tutor VARCHAR(255),
    documento_tutor VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_user INT,
    modificado_por INT,
    fecha_ultima_modificacion TIMESTAMP DEFAULT null,
	fecha_ultima_consulta TIMESTAMP DEFAULT null
);