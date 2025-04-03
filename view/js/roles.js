$(document).ready(function() {
    // Load roles for user role assignment
    function loadRolesForAssignment() {
        $.get('api/roles', function(response) {
            const roles = response.data;
            let checkboxes = '';
            roles.forEach(function(role) {
                checkboxes += `
                    <div class="custom-control custom-checkbox mr-3 mb-2">
                        <input type="checkbox" class="custom-control-input" id="role_${role.role_id}" value="${role.role_id}">
                        <label class="custom-control-label" for="role_${role.role_id}">${role.role_name}</label>
                    </div>
                `;
            });
            $('#roleCheckboxes').html(checkboxes);
        });
    }

    // Initialize User Roles DataTable
    const userRolesTable = $('#userRolesTable').DataTable({
        ajax: {
            url: 'api/users',
            dataSrc: function(response) {
                if (response && response.data) {
                    const users = response.data.data || [];
                    console.log('Users with roles:', users);
                    return users;
                }
                return [];
            },
            error: function(xhr, error, thrown) {
                console.error('Error loading users:', error, thrown);
                Swal.fire('Error', 'Error loading users data', 'error');
            }
        },
        columns: [
            { data: 'user_id' },
            { 
                data: null,
                render: function(data, type, row) {
                    return row.reg_name + ' ' + row.reg_lastname;
                }
            },
            { data: 'user_email' },
            {
                data: null,
                render: function(data, type, row) {
                    return row.roles ? row.roles.map(role => role.role_name).join(', ') : '';
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-primary btn-sm assignRole" data-id="${row.user_id}">
                            <i class="fas fa-user-tag"></i> Asignar Roles
                        </button>
                    `;
                }
            }
        ]
    });

    // Initialize DataTables
    const rolesTable = $('#rolesTable').DataTable({
        ajax: {
            url: 'api/roles',
            dataSrc: function(response) {
                return response.data || [];
            },
            error: function(xhr, error, thrown) {
                console.error('Error loading roles:', error, thrown);
                Swal.fire('Error', 'Error loading roles data', 'error');
            }
        },
        columns: [
            { data: 'role_id' },
            { data: 'role_name' },
            { data: 'role_description' },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-warning btn-sm editRole" data-id="${row.role_id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm deleteRole" data-id="${row.role_id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });

    const permissionsTable = $('#permissionsTable').DataTable({
        ajax: {
            url: 'api/permissions',
            dataSrc: 'data',
            error: function(xhr, error, thrown) {
                console.error('Error loading permissions:', error, thrown);
                Swal.fire('Error', 'Error loading permissions data', 'error');
            }
        },
        columns: [
            { data: 'perm_id' },
            { data: 'perm_name' },
            { data: 'perm_description' },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-warning btn-sm editPermission" data-id="${row.perm_id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm deletePermission" data-id="${row.perm_id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });

    // Load permissions for role forms
    // Add debug logging for permissions loading
    function loadPermissions() {
        console.log('Loading permissions...');
        $.get('api/permissions', function(response) {
            console.log('Permissions loaded:', response);
            const permissions = response.data;
            let checkboxes = '';
            permissions.forEach(function(permission) {
                checkboxes += `
                    <div class="custom-control custom-checkbox mr-3 mb-2">
                        <input type="checkbox" class="custom-control-input" id="perm_${permission.perm_id}" value="${permission.perm_id}">
                        <label class="custom-control-label" for="perm_${permission.perm_id}">${permission.perm_name}</label>
                    </div>
                `;
            });
            $('#permissionCheckboxes, #editPermissionCheckboxes').html(checkboxes);
        });
    }

    // Add Role
    $('#formAddRole').on('submit', function(e) {
        e.preventDefault();
        const permissions = [];
        const roleName = $('#roleName').val().trim();
        const roleDescription = $('#roleDescription').val().trim();
        
        if (!roleName) {
            Swal.fire('Error', 'Role name is required', 'error');
            return;
        }
        
        $('#permissionCheckboxes input:checked').each(function() {
            permissions.push($(this).val());
        });
        
        console.log('Submitting role:', { roleName, roleDescription, permissions });
        
        $.ajax({
            url: 'api/roles',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                role_name: roleName,
                role_description: roleDescription,
                permissions: permissions
            }),
            success: function(response) {
                console.log('Role created - Full response:', response);
                console.log('Role data:', response.data);
                $('#modalAddRole').modal('hide');
                rolesTable.ajax.reload();
                Swal.fire('¡Éxito!', 'Rol creado correctamente', 'success');
                $('#formAddRole')[0].reset();
            },
            error: function(xhr, status, error) {
                console.error('Error creating role:', error, xhr.responseText);
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al crear el rol', 'error');
            }
        });
    });

    // Add Permission
    $('#formAddPermission').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'api/permissions',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                perm_name: $('#permissionName').val(),
                perm_description: $('#permissionDescription').val()
            }),
            success: function(response) {
                console.log('Permission created - Full response:', response);
                console.log('Permission data:', response.data);
                $('#modalAddPermission').modal('hide');
                permissionsTable.ajax.reload();
                loadPermissions();
                Swal.fire('¡Éxito!', 'Permiso creado correctamente', 'success');
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON.message || 'Error al crear el permiso', 'error');
            }
        });
    });

    // Edit Role
    $(document).on('click', '.editRole', function() {
        const roleId = $(this).data('id');
        $.get(`api/roles/show?id=${roleId}`, function(response) {
            const role = response.data;
            $('#editRoleId').val(role.role_id);
            $('#editRoleName').val(role.role_name);
            $('#editRoleDescription').val(role.role_description);
            
            // Check permissions
            $('#editPermissionCheckboxes input').prop('checked', false);
            role.permission_ids.forEach(function(permId) {
                $(`#editPermissionCheckboxes #perm_${permId}`).prop('checked', true);
            });
            
            $('#modalEditRole').modal('show');
        });
    });

    // Update Role
    $('#formEditRole').on('submit', function(e) {
        e.preventDefault();
        const roleId = $('#editRoleId').val();
        const permissions = [];
        $('#editPermissionCheckboxes input:checked').each(function() {
            permissions.push($(this).val());
        });

        $.ajax({
            url: `api/roles?id=${roleId}`,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({
                role_name: $('#editRoleName').val(),
                role_description: $('#editRoleDescription').val(),
                permissions: permissions
            }),
            success: function(response) {
                console.log('Role updated - Full response:', response);
                console.log('Updated role data:', response.data);
                $('#modalEditRole').modal('hide');
                rolesTable.ajax.reload();
                Swal.fire('¡Éxito!', 'Rol actualizado correctamente', 'success');
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON.message || 'Error al actualizar el rol', 'error');
            }
        });
    });

    // Handle Assign Role Click
    $(document).on('click', '.assignRole', function() {
        const userId = $(this).data('id');
        $('#assignUserId').val(userId);
        
        // Load user's current roles
        $.get(`api/users/${userId}/roles`, function(response) {
            const userRoles = response.data;
            
            // Reset all checkboxes
            $('#roleCheckboxes input').prop('checked', false);
            
            // Check the roles that user has
            userRoles.forEach(function(role) {
                $(`#roleCheckboxes #role_${role.role_id}`).prop('checked', true);
            });
            
            $('#modalAssignRole').modal('show');
        });
    });

    // Handle Role Assignment Form Submit
    $('#formAssignRole').on('submit', function(e) {
        e.preventDefault();
        const userId = $('#assignUserId').val();
        const roles = [];
        
        $('#roleCheckboxes input:checked').each(function() {
            roles.push($(this).val());
        });
        
        $.ajax({
            url: `api/users/${userId}/roles`,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({ roles: roles }),
            success: function(response) {
                $('#modalAssignRole').modal('hide');
                userRolesTable.ajax.reload();
                Swal.fire('¡Éxito!', 'Roles asignados correctamente', 'success');
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al asignar roles', 'error');
            }
        });
    });

    // Load roles for assignment modal when it opens
    $('#modalAssignRole').on('show.bs.modal', function() {
        loadRolesForAssignment();
    });

    // Delete Role
    $(document).on('click', '.deleteRole', function() {
        const roleId = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `api/roles?id=${roleId}`,
                    method: 'DELETE',
                    success: function() {
                        rolesTable.ajax.reload();
                        Swal.fire('¡Eliminado!', 'El rol ha sido eliminado', 'success');
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON.message || 'Error al eliminar el rol', 'error');
                    }
                });
            }
        });
    });

    // Delete Permission
    $(document).on('click', '.deletePermission', function() {
        const permId = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `api/permissions?id=${permId}`,
                    method: 'DELETE',
                    success: function() {
                        permissionsTable.ajax.reload();
                        loadPermissions();
                        Swal.fire('¡Eliminado!', 'El permiso ha sido eliminado', 'success');
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON.message || 'Error al eliminar el permiso', 'error');
                    }
                });
            }
        });
    });

    // Load permissions on page load
    loadPermissions();

    // Clear forms on modal close
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });
});