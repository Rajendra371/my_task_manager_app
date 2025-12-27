<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager - Welcome</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }
        
        .welcome-container {
            background: white;
            padding: 60px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 600px;
            width: 90%;
        }
        
        .welcome-container h1 {
            font-size: 48px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .welcome-container p {
            font-size: 18px;
            color: #666;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        
        .links {
            display: flex;
            gap: 30px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .link-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-decoration: none;
            transition: all 0.3s;
            min-width: 200px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }
        
        .link-card h3 {
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .link-card p {
            color: rgba(255,255,255,0.9);
            margin: 0;
        }
        
        .features {
            margin-top: 50px;
            text-align: left;
        }
        
        .features h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .feature {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .feature h4 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .feature p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .welcome-container {
                padding: 40px 20px;
            }
            
            .welcome-container h1 {
                font-size: 36px;
            }
            
            .links {
                flex-direction: column;
                align-items: center;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        
        <div class="links">
            <a href="/app" class="link-card">
                <h3>Task App</h3>
                <p> User interface for managing your tasks</p>
            </a>
            
            <a href="/dashboard" class="link-card">
                <h3>Admin Dashboard</h3>
                <p> Admin panel for managing users and tasks</p>
            </a>
        </div>
        
    </div>
</body>
</html>