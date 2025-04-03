-- Initialize sys_permissions table
INSERT INTO sys_permissions (perm_name, perm_description) VALUES
('view_patients', 'Can view patient records'),
('edit_patients', 'Can edit patient records'),
('delete_patients', 'Can delete patient records'),
('view_appointments', 'Can view appointments'),
('manage_appointments', 'Can manage appointments'),
('view_users', 'Can view system users'),
('manage_users', 'Can manage system users'),
('view_roles', 'Can view roles and permissions'),
('manage_roles', 'Can manage roles and permissions')
ON CONFLICT (perm_name) DO NOTHING;

-- Initialize sys_roles table
INSERT INTO sys_roles (role_name, role_description) VALUES
('admin', 'System Administrator'),
('doctor', 'Medical Doctor'),
('receptionist', 'Front Desk Staff'),
('nurse', 'Nursing Staff')
ON CONFLICT (role_name) DO NOTHING;

-- Assign permissions to roles
-- Admin role gets all permissions
INSERT INTO sys_role_permissions (role_id, perm_id)
SELECT r.role_id, p.perm_id
FROM sys_roles r
CROSS JOIN sys_permissions p
WHERE r.role_name = 'admin'
ON CONFLICT (role_id, perm_id) DO NOTHING;

-- Doctor role permissions
INSERT INTO sys_role_permissions (role_id, perm_id)
SELECT r.role_id, p.perm_id
FROM sys_roles r
CROSS JOIN sys_permissions p
WHERE r.role_name = 'doctor'
AND p.perm_name IN ('view_patients', 'edit_patients', 'view_appointments', 'manage_appointments')
ON CONFLICT (role_id, perm_id) DO NOTHING;

-- Receptionist role permissions
INSERT INTO sys_role_permissions (role_id, perm_id)
SELECT r.role_id, p.perm_id
FROM sys_roles r
CROSS JOIN sys_permissions p
WHERE r.role_name = 'receptionist'
AND p.perm_name IN ('view_patients', 'view_appointments', 'manage_appointments')
ON CONFLICT (role_id, perm_id) DO NOTHING;

-- Nurse role permissions
INSERT INTO sys_role_permissions (role_id, perm_id)
SELECT r.role_id, p.perm_id
FROM sys_roles r
CROSS JOIN sys_permissions p
WHERE r.role_name = 'nurse'
AND p.perm_name IN ('view_patients', 'edit_patients', 'view_appointments')
ON CONFLICT (role_id, perm_id) DO NOTHING;