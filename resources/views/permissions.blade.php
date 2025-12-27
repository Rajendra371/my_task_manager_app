<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Permissions - Task Manager</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .nav {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .nav a {
            background: white;
            color: #667eea;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .nav a:hover, .nav a.active {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
        
        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .permissions-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .permissions-table th, .permissions-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }
        
        .permissions-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .permission-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .save-btn {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .save-btn:hover {
            background: #5a67d8;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>User Permissions</h1>
            <p>Manage user permissions for task operations</p>
        </div>
        
        <nav class="nav">
            <a href="/dashboard">Dashboard</a>
            <a href="/dashboard/tasks">Tasks</a>
            <a href="/dashboard/users">Users</a>
            <a href="/dashboard/permissions" class="active">Permissions</a>
        </nav>
        
        <div class="card">
            <div class="success-message" id="successMessage">Permissions updated successfully!</div>
            <div class="error-message" id="errorMessage">Failed to update permissions. Please try again.</div>
            
            <h2>User Permissions</h2>
            <table class="permissions-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Create</th>
                        <th>View</th>
                        <th>Edit</th>
                        <th>Update</th>
                        <th>Delete</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="permissionsTable">
                    <!-- Permissions will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const API_BASE = '/dashboard/api';
        let users = [];

        async function loadPermissions() {
            try {
                const response = await fetch(`${API_BASE}/permissions`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                if (response.ok) {
                    users = await response.json();
                    renderPermissionsTable();
                } else {
                    const errorText = await response.text();
                    showError(`Failed to load permissions: ${response.status}`);
                }
            } catch (error) {
                showError(`Network error loading permissions: ${error.message}`);
            }
        }

        function renderPermissionsTable() {
            const tbody = document.getElementById('permissionsTable');
            tbody.innerHTML = users.map(user => `
                <tr>
                    <td>${user.name}</td>
                    <td>${user.email}</td>
                    <td><span class="badge ${user.role}">${user.role}</span></td>
                    <td><input type="checkbox" class="permission-checkbox" data-user="${user.id}" data-permission="can_create" ${user.permissions.can_create ? 'checked' : ''}></td>
                    <td><input type="checkbox" class="permission-checkbox" data-user="${user.id}" data-permission="can_view" ${user.permissions.can_view ? 'checked' : ''}></td>
                    <td><input type="checkbox" class="permission-checkbox" data-user="${user.id}" data-permission="can_edit" ${user.permissions.can_edit ? 'checked' : ''}></td>
                    <td><input type="checkbox" class="permission-checkbox" data-user="${user.id}" data-permission="can_update" ${user.permissions.can_update ? 'checked' : ''}></td>
                    <td><input type="checkbox" class="permission-checkbox" data-user="${user.id}" data-permission="can_delete" ${user.permissions.can_delete ? 'checked' : ''}></td>
                    <td><button class="save-btn" onclick="savePermissions(${user.id})">Save</button></td>
                </tr>
            `).join('');
        }

        async function savePermissions(userId) {
            const checkboxes = document.querySelectorAll(`[data-user="${userId}"]`);
            const permissions = {};
            
            checkboxes.forEach(checkbox => {
                permissions[checkbox.dataset.permission] = checkbox.checked;
            });

            try {
                const response = await fetch(`${API_BASE}/permissions/${userId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(permissions)
                });

                if (response.ok) {
                    showSuccess('Permissions updated successfully!');
                } else {
                    showError('Failed to update permissions');
                }
            } catch (error) {
                showError('Network error updating permissions');
            }
        }

        function showSuccess(message) {
            const successDiv = document.getElementById('successMessage');
            successDiv.textContent = message;
            successDiv.style.display = 'block';
            document.getElementById('errorMessage').style.display = 'none';
            setTimeout(() => successDiv.style.display = 'none', 3000);
        }

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            document.getElementById('successMessage').style.display = 'none';
            setTimeout(() => errorDiv.style.display = 'none', 3000);
        }

        // Load permissions on page load
        loadPermissions();
    </script>
</body>
</html>