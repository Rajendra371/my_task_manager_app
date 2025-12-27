<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Task Manager Dashboard</title>
    @vite(['resources/js/app.css'])
</head>
<body class="dashboard-layout">
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>All Users</h1>
            <p>Manage all users in the system</p>
        </div>
        
        <nav class="dashboard-nav">
            <a href="{{ url('/dashboard') }}">Dashboard</a>
            <a href="{{ url('/dashboard/tasks') }}">Tasks</a>
            <a href="{{ url('/dashboard/users') }}" class="active">Users</a>
            <a href="{{ url('/dashboard/permissions') }}">Permissions</a>
        </nav>
        
        <div class="dashboard-card">
            <h2>All Users</h2>
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Tasks Count</th>
                        <th>Joined</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="dashboard-badge {{ $user->role }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>{{ $user->tasks_count }}</td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                        <td>{{ $user->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="laravel-pagination">
                {{ $users->links('custom-pagination') }}
            </div>
        </div>
    </div>
</body>
</html>