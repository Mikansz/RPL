-- Fix Department Permissions SQL Script
-- Run these queries in your MySQL database

-- 1. Ensure department permissions exist
INSERT IGNORE INTO permissions (name, display_name, module, description, created_at, updated_at) VALUES
('departments.view', 'View Departments', 'departments', 'Permission to view departments', NOW(), NOW()),
('departments.create', 'Create Departments', 'departments', 'Permission to create departments', NOW(), NOW()),
('departments.edit', 'Edit Departments', 'departments', 'Permission to edit departments', NOW(), NOW()),
('departments.delete', 'Delete Departments', 'departments', 'Permission to delete departments', NOW(), NOW());

-- 2. Ensure admin role exists
INSERT IGNORE INTO roles (name, display_name, description, is_active, created_at, updated_at) VALUES
('admin', 'Administrator', 'Full system access', 1, NOW(), NOW());

-- 3. Ensure CEO role exists  
INSERT IGNORE INTO roles (name, display_name, description, is_active, created_at, updated_at) VALUES
('ceo', 'CEO', 'Chief Executive Officer', 1, NOW(), NOW());

-- 4. Assign all department permissions to admin role
INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at, updated_at)
SELECT r.id, p.id, NOW(), NOW() 
FROM roles r, permissions p 
WHERE r.name = 'admin' AND p.module = 'departments';

-- 5. Assign all department permissions to CEO role
INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at, updated_at)
SELECT r.id, p.id, NOW(), NOW() 
FROM roles r, permissions p 
WHERE r.name = 'ceo' AND p.module = 'departments';

-- 6. Check current users and their roles
SELECT 
    u.id,
    u.username,
    u.first_name,
    u.last_name,
    u.employee_id,
    u.status,
    GROUP_CONCAT(r.name) as roles
FROM users u
LEFT JOIN user_roles ur ON u.id = ur.user_id AND ur.is_active = 1
LEFT JOIN roles r ON ur.role_id = r.id
GROUP BY u.id;

-- 7. If you need to assign admin role to a specific user, replace 'YOUR_USERNAME' with your actual username
-- Uncomment and modify the line below:
-- INSERT IGNORE INTO user_roles (user_id, role_id, assigned_at, is_active, created_at, updated_at)
-- SELECT u.id, r.id, NOW(), 1, NOW(), NOW()
-- FROM users u, roles r 
-- WHERE u.username = 'YOUR_USERNAME' AND r.name = 'admin';

-- 8. Verify permissions are correctly assigned
SELECT 
    u.username,
    u.first_name,
    u.last_name,
    r.name as role_name,
    p.name as permission_name,
    p.display_name
FROM users u
JOIN user_roles ur ON u.id = ur.user_id AND ur.is_active = 1
JOIN roles r ON ur.role_id = r.id
JOIN role_permissions rp ON r.id = rp.role_id
JOIN permissions p ON rp.permission_id = p.id
WHERE p.module = 'departments'
ORDER BY u.username, p.name;
