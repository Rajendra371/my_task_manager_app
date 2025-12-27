<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager Dashboard</title>
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
        
        .header h1 {
            color: #333;
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 18px;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .number {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 18px;
            font-weight: 500;
        }
        
        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 24px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge.completed {
            background: #d4edda;
            color: #155724;
        }
        
        .badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge.admin {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .badge.user {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .pagination a, .pagination span {
            padding: 10px 15px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .pagination a:hover {
            background: #5a67d8;
        }
        
        .pagination .current {
            background: #333;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .nav {
                flex-direction: column;
                align-items: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .table {
                font-size: 14px;
            }
            
            .table th, .table td {
                padding: 10px 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Task Manager Dashboard</h1>
            <p>Admin Panel - Manage Users and Tasks</p>
            <div style="margin-top: 15px;">
                <a href="{{ route('admin.logout.get') }}" style="background: #ff4757; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: 600; transition: background 0.3s;" onmouseover="this.style.background='#ff3742'" onmouseout="this.style.background='#ff4757'">Logout</a>
            </div>
        </div>
        
        <nav class="nav">
            <a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ url('/dashboard/tasks') }}" class="{{ request()->is('dashboard/tasks') ? 'active' : '' }}">Tasks</a>
            <a href="{{ url('/dashboard/users') }}" class="{{ request()->is('dashboard/users') ? 'active' : '' }}">Users</a>
            <a href="{{ url('/dashboard/permissions') }}" class="{{ request()->is('dashboard/permissions') ? 'active' : '' }}">Permissions</a>
        </nav>
        
        @if(request()->is('dashboard'))
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="number">{{ $totalUsers }}</div>
                    <div class="label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="number">{{ $totalTasks }}</div>
                    <div class="label">Total Tasks</div>
                </div>
                <div class="stat-card">
                    <div class="number">{{ $completedTasks }}</div>
                    <div class="label">Completed Tasks</div>
                </div>
                <div class="stat-card">
                    <div class="number">{{ $pendingTasks }}</div>
                    <div class="label">Pending Tasks</div>
                </div>
            </div>
            
            <div class="card">
                <h2>Recent Tasks</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTasks as $task)
                        <tr>
                            <td>{{ $task->title }}</td>
                            <td>{{ $task->user->name }}</td>
                            <td>
                                <span class="badge {{ $task->completed ? 'completed' : 'pending' }}">
                                    {{ $task->completed ? 'Completed' : 'Pending' }}
                                </span>
                            </td>
                            <td>{{ $task->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h2>Users Overview</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Tasks Count</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->role }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>{{ $user->tasks_count }}</td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</body>
</html>