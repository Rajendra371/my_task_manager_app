<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tasks - Task Manager Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/js/app.css'])
</head>
<body class="dashboard-layout">
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>All Tasks</h1>
            <p>Manage all tasks in the system</p>
        </div>
        
        <nav class="dashboard-nav">
            <a href="{{ url('/dashboard') }}">Dashboard</a>
            <a href="{{ url('/dashboard/tasks') }}" class="active">Tasks</a>
            <a href="{{ url('/dashboard/users') }}">Users</a>
            <a href="{{ url('/dashboard/permissions') }}">Permissions</a>
        </nav>
        
        <div class="dashboard-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>All Tasks</h2>
                <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                    <label>Filter By User:</label>
                    <select name="user_id" onchange="this.form.submit()" style="padding: 8px 12px; border: 2px solid #e1e5e9; border-radius: 6px;">
                       <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>User</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                    <tr id="task-{{ $task->id }}">
                        <td>{{ $task->title }}</td>
                        <td>{{ $task->description ? substr($task->description, 0, 50) . (strlen($task->description) > 50 ? '...' : '') : '' }}</td>
                        <td>{{ $task->user->name }}</td>
                        <td>
                            <span class="dashboard-badge {{ $task->completed ? 'completed' : 'pending' }}">
                                {{ $task->completed ? 'Completed' : 'Pending' }}
                            </span>
                        </td>
                        <td>{{ $task->created_at->format('M d, Y H:i') }}</td>
                        <td style="width: 80px;">
                            <div style="display: flex; align-items: center; gap: 2px;">
                                <button onclick="toggleStatus({{ $task->id }}, {{ $task->completed ? 'false' : 'true' }})" 
                                        class="btn-sm {{ $task->completed ? 'btn-warning' : 'btn-success' }}" 
                                        id="toggle-{{ $task->id }}" title="{{ $task->completed ? 'Mark as Pending' : 'Mark as Complete' }}">
                                    <i class="bi {{ $task->completed ? 'bi-arrow-counterclockwise' : 'bi-check-circle' }}"></i>
                                </button>
                                <button onclick="editTask({{ $task->id }})" 
                                        class="btn-sm btn-primary" 
                                        id="edit-{{ $task->id }}" title="Edit Task">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="deleteTask({{ $task->id }})" 
                                        class="btn-sm btn-danger" 
                                        id="delete-{{ $task->id }}" title="Delete Task">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="laravel-pagination">
                {{ $tasks->appends(request()->query())->links('custom-pagination') }}
            </div>
        </div>
    </div>
    
    <script>
    async function toggleStatus(taskId, completed) {
        const btn = document.getElementById(`toggle-${taskId}`);
        const icon = btn.querySelector('i');
        const originalClass = icon.className;
        icon.className = 'bi bi-arrow-clockwise';
        btn.disabled = true;
        
        try {
            const response = await fetch(`/dashboard/api/tasks/${taskId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ completed })
            });
            
            if (response.ok) {
                location.reload();
            } else {
                throw new Error('Failed to update task');
            }
        } catch (error) {
            alert('Error updating task');
            icon.className = originalClass;
            btn.disabled = false;
        }
    }
    
    async function deleteTask(taskId) {
        if (!confirm('Are you sure you want to delete this task?')) return;
        
        const btn = document.getElementById(`delete-${taskId}`);
        const icon = btn.querySelector('i');
        const row = document.getElementById(`task-${taskId}`);
        icon.className = 'bi bi-hourglass-split';
        btn.disabled = true;
        
        try {
            const response = await fetch(`/dashboard/api/tasks/${taskId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (response.ok) {
                row.style.opacity = '0.5';
                setTimeout(() => location.reload(), 500);
            } else {
                throw new Error('Failed to delete task');
            }
        } catch (error) {
            alert('Error deleting task');
            icon.className = 'bi bi-trash';
            btn.disabled = false;
        }
    }
    
    function editTask(taskId) {
        const row = document.getElementById(`task-${taskId}`);
        const titleCell = row.cells[0];
        const descCell = row.cells[1];
        const currentTitle = titleCell.textContent;
        const currentDesc = descCell.textContent;
        
        
        titleCell.innerHTML = `<input type="text" value="${currentTitle}" id="edit-title-${taskId}" style="width: 100%; padding: 4px;">`;
        descCell.innerHTML = `<input type="text" value="${currentDesc}" id="edit-desc-${taskId}" style="width: 100%; padding: 4px;">`;
        
        
        const editBtn = document.getElementById(`edit-${taskId}`);
        editBtn.innerHTML = '<i class="bi bi-check"></i>';
        editBtn.title = 'Save Changes';
        editBtn.onclick = () => saveTask(taskId, currentTitle, currentDesc);
        
      
        const toggleBtn = document.getElementById(`toggle-${taskId}`);
        toggleBtn.innerHTML = '<i class="bi bi-x"></i>';
        toggleBtn.title = 'Cancel Edit';
        toggleBtn.onclick = () => cancelEdit(taskId, currentTitle, currentDesc);
    }
    
    function cancelEdit(taskId, originalTitle, originalDesc) {
        const row = document.getElementById(`task-${taskId}`);
        row.cells[0].textContent = originalTitle;
        row.cells[1].textContent = originalDesc;
        location.reload(); 
    }
    
    async function saveTask(taskId, originalTitle, originalDesc) {
        const newTitle = document.getElementById(`edit-title-${taskId}`).value;
        const newDesc = document.getElementById(`edit-desc-${taskId}`).value;
        
       
        clearValidationErrors(taskId);
        
        const editBtn = document.getElementById(`edit-${taskId}`);
        editBtn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        editBtn.disabled = true;
        
        try {
            const response = await fetch(`/dashboard/api/tasks/${taskId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ title: newTitle, description: newDesc })
            });
            
            if (response.ok) {
                location.reload();
            } else if (response.status === 422) {
                const errorData = await response.json();
                displayValidationErrors(taskId, errorData.errors);
                editBtn.innerHTML = '<i class="bi bi-check"></i>';
                editBtn.disabled = false;
            } else {
                throw new Error('Failed to update task');
            }
        } catch (error) {
            alert('Error updating task');
            cancelEdit(taskId, originalTitle, originalDesc);
        }
    }
    
    function displayValidationErrors(taskId, errors) {
        if (errors.title) {
            const titleInput = document.getElementById(`edit-title-${taskId}`);
            titleInput.style.borderColor = '#dc3545';
            const errorDiv = document.createElement('div');
            errorDiv.className = 'validation-error';
            errorDiv.textContent = errors.title[0];
            titleInput.parentNode.appendChild(errorDiv);
        }
        
        if (errors.description) {
            const descInput = document.getElementById(`edit-desc-${taskId}`);
            descInput.style.borderColor = '#dc3545';
            const errorDiv = document.createElement('div');
            errorDiv.className = 'validation-error';
            errorDiv.textContent = errors.description[0];
            descInput.parentNode.appendChild(errorDiv);
        }
    }
    
    function clearValidationErrors(taskId) {
        const errors = document.querySelectorAll('.validation-error');
        errors.forEach(error => error.remove());
        
        const titleInput = document.getElementById(`edit-title-${taskId}`);
        const descInput = document.getElementById(`edit-desc-${taskId}`);
        if (titleInput) titleInput.style.borderColor = '';
        if (descInput) descInput.style.borderColor = '';
    }
    </script>
</body>
</html>