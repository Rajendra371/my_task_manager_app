# Task Manager - Laravel + React Application

A full-stack task management application built with Laravel backend and React frontend, featuring role-based authentication, CRUD operations, and admin dashboard.

## Features

- **Dual Authentication**: API tokens for React app, session-based for admin dashboard
- **Role-Based Access**: Admin and User roles with different permissions
- **Task Management**: Create, read, update, delete tasks with inline editing
- **Permission System**: Granular user permissions (create, view, edit, update, delete)
- **Admin Dashboard**: Manage all tasks and users with filtering capabilities
- **Responsive Design**: Mobile-friendly interface
- **Real-time Validation**: Inline error display during editing

## Tech Stack

- **Backend**: Laravel 10, PHP 8.1+
- **Frontend**: React 18, TypeScript
- **Database**: SQLite (default) / MySQL (configurable)
- **Authentication**: Laravel Sanctum
- **Styling**: Custom CSS with Bootstrap Icons
- **Build Tool**: Vite

## Quick Start (SQLite - Default)


2. **Clone Repository**
   ```bash
   git clone https://github.com/Rajendra371/my_task_manager_app.git
   cd my_task_manager_app
   ```

3. **Add to Herd**
   - Open Herd application
   - Click "Add Site" or drag project folder to Herd
   - Herd will automatically detect Laravel project

4. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

5. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

6. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. **Build Frontend**
   ```bash
   npm run build
   ```

8. **Access Application**
   - React App: https://my-task-manager-app.test/app
   - Admin Dashboard: https://my-task-manager-app.test/dashboard
   - Admin Login: admin@example.com / password

### Option B: Traditional Setup

1. **Clone Repository**
   bash
   git clone <repository-url>
   cd my-task-manager
   

2. **Install Dependencies**
   bash
   composer install
   npm install
   

3. **Environment Setup**
   bash
   cp .env.example .env
   php artisan key:generate
   

4. **Database Setup**
   bash
   php artisan migrate
   php artisan db:seed
   

5. **Start Development Servers**
   bash
   # Terminal 1 - Laravel Backend
   php artisan serve
   
   # Terminal 2 - React Frontend
   npm run dev
   

6. **Access Application**
   - React App: http://localhost:5173/app
   - Admin Dashboard: http://localhost:8000/dashboard
   - Admin Login: admin@example.com / password




### Setup Instructions

#### For Standalone MySQL:
1. **Install MySQL Server**
2. **Create Database:**
   ```sql
   CREATE DATABASE task_manager;
   CREATE USER 'task_user'@'localhost' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON task_manager.* TO 'task_user'@'localhost';
   FLUSH PRIVILEGES;
   ```



### Update Environment Configuration
Edit `.env` file:

```env
# Change from SQLite to MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=task_user
DB_PASSWORD=your_password

# Remove SQLite configuration
# DB_DATABASE=database/database.sqlite
```

### Step 3: Install MySQL PHP Extension
Ensure PHP MySQL extension is enabled:

**For XAMPP:**
- Open `php.ini` file
- Uncomment: `extension=pdo_mysql`
- Restart Apache

**For Ubuntu/Linux:**
```bash
sudo apt-get install php-mysql
sudo systemctl restart apache2
```

**For Windows (Standalone PHP):**
- Enable `extension=pdo_mysql` in php.ini
- Restart web server

### Step 4: Run Database Migrations
bash
# Clear config cache
php artisan config:clear

# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed


### Step 5: Verify Connection
bash
php artisan tinker

php
// Test database connection
DB::connection()->getPdo();
// Should return PDO instance without errors




## Default Users

After seeding, you can login with:

**Admin User:**
- Email: admin@example.com
- Password: password
- Access: Full admin dashboard + React app

**Regular User:**
- Email: user@example.com  
- Password: password
- Access: React app only (own tasks)

## API Endpoints

### Authentication
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout

### Tasks
- `GET /api/tasks` - List tasks (filtered by user role)
- `POST /api/tasks` - Create task
- `GET /api/tasks/{id}` - Get task details
- `PUT /api/tasks/{id}` - Update task
- `DELETE /api/tasks/{id}` - Delete task

### Admin Only
- `GET /api/users` - List all users
- `GET /api/permissions` - Get user permissions
- `PUT /api/permissions/{user}` - Update user permissions

## Project Structure


my-task-manager/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/           # API controllers
│   │   └── DashboardController.php
│   ├── Models/
│   │   ├── Task.php
│   │   ├── User.php
│   │   └── UserPermission.php
│   └── Policies/
│       └── TaskPolicy.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── js/
│   │   ├── app.tsx        # React application
│   │   └── app.css        # Styles
│   └── views/             # Laravel Blade templates
├── routes/
│   ├── api.php           # API routes
│   └── web.php           # Web routes
└── README.md
```

## Development Commands

```bash
# Laravel Commands
php artisan serve              # Start Laravel server
php artisan migrate           # Run migrations
php artisan db:seed          # Seed database
php artisan config:clear     # Clear config cache
php artisan route:list       # List all routes

# Frontend Commands
npm run dev                  # Start Vite dev server
npm run build               # Build for production
npm run preview             # Preview production build

# Database Commands
php artisan migrate:fresh --seed    # Fresh migration with seeding
php artisan tinker                  # Laravel REPL
```

## Troubleshooting

### Common Issues

**1. Database Connection Error**
```bash
# Clear config cache
php artisan config:clear



**2. Permission Denied Errors**
bash
# Fix storage permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache



**3. Vite Build Issues**
bash
# Clear node modules and reinstall
rm -rf node_modules package-lock.json
npm install

**4. CORS Issues**
- Ensure `APP_URL` in `.env` matches your frontend URL
- Check Laravel Sanctum configuration in `config/sanctum.php`

### Performance Optimization

**Database Indexing:**
- Tasks table has index on `user_id` for faster queries
- Consider adding indexes on frequently queried columns

**Caching:**
bash
# Enable caching for production
php artisan config:cache
php artisan route:cache
php artisan view:cache


## Production Deployment

1. **Environment Setup**
   bash
   APP_ENV=production
   APP_DEBUG=false
   

2. **Optimize Application**
   bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   

3. **Build Frontend**
   bash
   npm run build
   

4. **Set Permissions**
   bash
   chmod -R 775 storage bootstrap/cache


## Support

For issues and questions:
1. Check this README for common solutions
2. Review Laravel and React documentation
3. Check application logs in `storage/logs/`