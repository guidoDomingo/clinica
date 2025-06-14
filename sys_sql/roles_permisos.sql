-- Tablas para sistema de roles y permisos ya existen:
-- sys_roles, sys_permissions, sys_role_permissions, sys_user_roles

-- Las tablas de sistema ya existen, pero podemos insertar datos básicos:

-- Comentado porque las tablas ya existen
/*
CREATE TABLE IF NOT EXISTS sys_roles (
  role_id serial4 NOT NULL,
  role_name varchar(100) NOT NULL,
  role_description text NULL,
  CONSTRAINT sys_roles_pkey PRIMARY KEY (role_id),
  CONSTRAINT sys_roles_role_name_key UNIQUE (role_name)
);

CREATE TABLE IF NOT EXISTS sys_permissions (
  perm_id serial4 NOT NULL,
  perm_name varchar(100) NOT NULL,
  perm_description text NULL,
  CONSTRAINT sys_permissions_perm_name_key UNIQUE (perm_name),
  CONSTRAINT sys_permissions_pkey PRIMARY KEY (perm_id)
);

CREATE TABLE IF NOT EXISTS sys_role_permissions (
  role_id int4 NOT NULL,
  perm_id int4 NOT NULL,
  CONSTRAINT sys_role_permissions_pkey PRIMARY KEY (role_id, perm_id),
  CONSTRAINT sys_role_permissions_perm_id_fkey FOREIGN KEY (perm_id) REFERENCES sys_permissions(perm_id) ON DELETE CASCADE,
  CONSTRAINT sys_role_permissions_role_id_fkey FOREIGN KEY (role_id) REFERENCES sys_roles(role_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS sys_user_roles (
  user_id int4 NOT NULL,
  role_id int4 NOT NULL,
  CONSTRAINT sys_user_roles_pkey PRIMARY KEY (user_id, role_id),
  CONSTRAINT sys_user_roles_role_id_fkey FOREIGN KEY (role_id) REFERENCES sys_roles(role_id) ON DELETE CASCADE,
  CONSTRAINT sys_user_roles_user_id_fkey FOREIGN KEY (user_id) REFERENCES sys_users(user_id) ON DELETE CASCADE
);
*/

-- Insertar roles básicos
INSERT INTO sys_roles (role_name, role_description)
SELECT 'admin', 'Administrador con acceso completo'
WHERE NOT EXISTS (
    SELECT 1 FROM sys_roles WHERE role_name = 'admin'
);

INSERT INTO sys_roles (role_name, role_description)
SELECT 'medico', 'Médico o profesional de salud'
WHERE NOT EXISTS (
    SELECT 1 FROM sys_roles WHERE role_name = 'medico'
);

INSERT INTO sys_roles (role_name, role_description)
SELECT 'recepcionista', 'Personal de recepción'
WHERE NOT EXISTS (
    SELECT 1 FROM sys_roles WHERE role_name = 'recepcionista'
);

INSERT INTO sys_roles (role_name, role_description)
SELECT 'asistente', 'Asistente médico'
WHERE NOT EXISTS (
    SELECT 1 FROM sys_roles WHERE role_name = 'asistente'
);

-- Insertar permisos básicos
INSERT INTO sys_permissions (perm_name, perm_description)
SELECT * FROM (SELECT 'administrar_roles', 'Gestionar roles y permisos') AS tmp
WHERE NOT EXISTS (
    SELECT perm_name FROM sys_permissions WHERE perm_name = 'administrar_roles'
) LIMIT 1;

INSERT INTO sys_permissions (perm_name, perm_description)
SELECT * FROM (SELECT 'administrar_usuarios', 'Gestionar usuarios del sistema') AS tmp
WHERE NOT EXISTS (
    SELECT perm_name FROM sys_permissions WHERE perm_name = 'administrar_usuarios'
) LIMIT 1;

INSERT INTO sys_permissions (perm_name, perm_description)
SELECT * FROM (SELECT 'ver_consultas', 'Ver consultas médicas') AS tmp
WHERE NOT EXISTS (
    SELECT perm_name FROM sys_permissions WHERE perm_name = 'ver_consultas'
) LIMIT 1;

INSERT INTO sys_permissions (perm_name, perm_description)
SELECT * FROM (SELECT 'crear_consultas', 'Crear nuevas consultas médicas') AS tmp
WHERE NOT EXISTS (
    SELECT perm_name FROM sys_permissions WHERE perm_name = 'crear_consultas'
) LIMIT 1;

INSERT INTO sys_permissions (perm_name, perm_description)
SELECT * FROM (SELECT 'editar_consultas', 'Modificar consultas médicas existentes') AS tmp
WHERE NOT EXISTS (
    SELECT perm_name FROM sys_permissions WHERE perm_name = 'editar_consultas'
) LIMIT 1;

INSERT INTO sys_permissions (perm_name, perm_description)
SELECT * FROM (SELECT 'eliminar_consultas', 'Eliminar consultas médicas') AS tmp
WHERE NOT EXISTS (
    SELECT perm_name FROM sys_permissions WHERE perm_name = 'eliminar_consultas'
) LIMIT 1;

INSERT INTO sys_permissions (perm_name, perm_description)
SELECT * FROM (SELECT 'ver_pacientes', 'Ver información de pacientes') AS tmp
WHERE NOT EXISTS (
    SELECT perm_name FROM sys_permissions WHERE perm_name = 'ver_pacientes'
) LIMIT 1;

INSERT INTO sys_permissions (perm_name, perm_description)
SELECT * FROM (SELECT 'crear_pacientes', 'Registrar nuevos pacientes') AS tmp
WHERE NOT EXISTS (
    SELECT perm_name FROM sys_permissions WHERE perm_name = 'crear_pacientes'
) LIMIT 1;

INSERT INTO sys_permissions (perm_name, perm_description)
SELECT * FROM (SELECT 'editar_pacientes', 'Modificar información de pacientes') AS tmp
WHERE NOT EXISTS (
    SELECT perm_name FROM sys_permissions WHERE perm_name = 'editar_pacientes'
) LIMIT 1;

INSERT INTO sys_permissions (perm_name, perm_description)
SELECT * FROM (SELECT 'eliminar_pacientes', 'Eliminar pacientes del sistema') AS tmp
WHERE NOT EXISTS (
    SELECT perm_name FROM sys_permissions WHERE perm_name = 'eliminar_pacientes'
) LIMIT 1;

INSERT INTO sys_permissions (perm_name, perm_description)
SELECT * FROM (SELECT 'ver_agenda', 'Ver agenda y citas') AS tmp
WHERE NOT EXISTS (
    SELECT perm_name FROM sys_permissions WHERE perm_name = 'ver_agenda'
) LIMIT 1;

INSERT INTO sys_permissions (perm_name, perm_description)
SELECT * FROM (SELECT 'crear_citas', 'Programar citas') AS tmp
WHERE NOT EXISTS (
    SELECT perm_name FROM sys_permissions WHERE perm_name = 'crear_citas'
) LIMIT 1;

INSERT INTO sys_permissions (perm_name, perm_description)
SELECT * FROM (SELECT 'editar_citas', 'Modificar citas existentes') AS tmp
WHERE NOT EXISTS (
    SELECT perm_name FROM sys_permissions WHERE perm_name = 'editar_citas'
) LIMIT 1;

INSERT INTO sys_permissions (perm_name, perm_description)
SELECT * FROM (SELECT 'eliminar_citas', 'Cancelar citas') AS tmp
WHERE NOT EXISTS (
    SELECT perm_name FROM sys_permissions WHERE perm_name = 'eliminar_citas'
) LIMIT 1;

-- Asignar permisos al rol admin
INSERT INTO sys_role_permissions (role_id, perm_id)
SELECT r.role_id, p.perm_id FROM sys_roles r
CROSS JOIN sys_permissions p
WHERE r.role_name = 'admin'
AND NOT EXISTS (
    SELECT 1 FROM sys_role_permissions 
    WHERE role_id = r.role_id AND perm_id = p.perm_id
);

-- Asignar primer usuario como admin si existe
INSERT INTO sys_user_roles (user_id, role_id)
SELECT (SELECT MIN(user_id) FROM sys_users), r.role_id
FROM sys_roles r
WHERE r.role_name = 'admin'
AND NOT EXISTS (
    SELECT 1 FROM sys_user_roles 
    WHERE user_id = (SELECT MIN(user_id) FROM sys_users) AND role_id = r.role_id
);
