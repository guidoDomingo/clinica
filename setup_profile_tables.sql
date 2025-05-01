-- Script para crear las tablas necesarias para el módulo de perfil
-- Este script verifica si las tablas existen antes de crearlas

-- Tabla rh_person si no existe
CREATE TABLE IF NOT EXISTS rh_person (
    person_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    document_number VARCHAR(20),
    document_type VARCHAR(20) DEFAULT 'CI',
    birth_date DATE,
    gender CHAR(1),
    phone_number VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de relación entre rh_person y sys_users (si no existe)
CREATE TABLE IF NOT EXISTS person_system_user (
    person_id INT NOT NULL,
    system_user_id INT NOT NULL,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (person_id, system_user_id),
    CONSTRAINT fk_person_id FOREIGN KEY (person_id) REFERENCES rh_person(person_id)
    -- No añadimos la restricción a sys_users ya que no conocemos su estructura exacta
);

-- Añadir campo de foto de perfil a sys_users si no existe
-- Nota: Esto es un procedimiento almacenado para verificar si la columna existe antes de crearla
DELIMITER //
CREATE PROCEDURE AddProfilePhotoColumn()
BEGIN
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = 'sys_users' 
        AND COLUMN_NAME = 'profile_photo'
    ) THEN
        ALTER TABLE sys_users ADD COLUMN profile_photo VARCHAR(255) DEFAULT NULL;
    END IF;
END //
DELIMITER ;

CALL AddProfilePhotoColumn();
DROP PROCEDURE AddProfilePhotoColumn;