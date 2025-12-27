import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import './app.css';

const API_BASE = 'http://127.0.0.1:8000/api';

const PermissionRow = ({ user, onUpdate }) => {
  const [permissions, setPermissions] = useState(user.permissions);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState('');

  const handlePermissionChange = (permission, value) => {
    setPermissions(prev => ({ ...prev, [permission]: value }));
  };

  const handleSave = async () => {
    setSaving(true);
    setMessage('');
    const success = await onUpdate(user.id, permissions);
    if (success) {
      setMessage('✓ Saved');
      setTimeout(() => setMessage(''), 2000);
    } else {
      setMessage('✗ Failed');
      setTimeout(() => setMessage(''), 3000);
    }
    setSaving(false);
  };

  return (
    <tr>
      <td>{user.name}</td>
      <td>{user.email}</td>
      <td><span className={`role-badge ${user.role}`}>{user.role}</span></td>
      <td>
        <input 
          type="checkbox" 
          checked={permissions.can_create} 
          onChange={(e) => handlePermissionChange('can_create', e.target.checked)}
        />
      </td>
      <td>
        <input 
          type="checkbox" 
          checked={permissions.can_view} 
          onChange={(e) => handlePermissionChange('can_view', e.target.checked)}
        />
      </td>
      <td>
        <input 
          type="checkbox" 
          checked={permissions.can_edit} 
          onChange={(e) => handlePermissionChange('can_edit', e.target.checked)}
        />
      </td>
      <td>
        <input 
          type="checkbox" 
          checked={permissions.can_update} 
          onChange={(e) => handlePermissionChange('can_update', e.target.checked)}
        />
      </td>
      <td>
        <input 
          type="checkbox" 
          checked={permissions.can_delete} 
          onChange={(e) => handlePermissionChange('can_delete', e.target.checked)}
        />
      </td>
      <td>
        <button 
          onClick={handleSave} 
          disabled={saving}
          className="btn-save"
        >
          {saving ? 'Saving...' : 'Save'}
        </button>
        {message && <div style={{fontSize: '12px', marginTop: '5px'}}>{message}</div>}
      </td>
    </tr>
  );
};

const TaskApp = () => {
  // Auth state
  const [user, setUser] = useState(null);
  const [userPermissions, setUserPermissions] = useState(null);
  const [token, setToken] = useState(null);
  const [isLogin, setIsLogin] = useState(true);
  const [authLoading, setAuthLoading] = useState(false);
  const [authErrors, setAuthErrors] = useState({});
  
  // Current view state
  const [currentView, setCurrentView] = useState('tasks'); // 'tasks' or 'permissions'
  
  // Tasks state
  const [tasks, setTasks] = useState([]);
  const [tasksLoading, setTasksLoading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [totalTasks, setTotalTasks] = useState(0);
  const [perPage, setPerPage] = useState(10);
  
  // Task form state
  const [taskForm, setTaskForm] = useState({ title: '', description: '' });
  const [editingTask, setEditingTask] = useState(null);
  const [taskLoading, setTaskLoading] = useState(false);
  const [taskErrors, setTaskErrors] = useState({});
  
  // Admin filter state
  const [userFilter, setUserFilter] = useState('');
  const [allUsers, setAllUsers] = useState([]);
  
  // Permissions state
  const [permissionsUsers, setPermissionsUsers] = useState([]);
  const [permissionsLoading, setPermissionsLoading] = useState(false);
  
  // Auth form state
  const [authForm, setAuthForm] = useState({
    name: '', email: '', password: '', role: 'user'
  });

  // Initialize app
  useEffect(() => {
    const savedToken = localStorage.getItem('token');
    if (savedToken) {
      setToken(savedToken);
      validateToken(savedToken);
    }
  }, []);

  // Fetch tasks when token or page changes
  useEffect(() => {
    if (token) {
      fetchTasks();
      if (user?.role === 'admin') {
        fetchUsers();
      }
    }
  }, [token, currentPage, userFilter, perPage]);

  const validateToken = async (tokenToValidate) => {
    try {
      const response = await fetch(`${API_BASE}/user`, {
        headers: { 'Authorization': `Bearer ${tokenToValidate}` }
      });
      
      if (response.ok) {
        const userData = await response.json();
        setUser(userData);
        
        // Fetch user permissions
        const permResponse = await fetch(`${API_BASE}/permissions`, {
          headers: { 'Authorization': `Bearer ${tokenToValidate}` }
        });
        
        if (permResponse.ok) {
          const permText = await permResponse.text();
          const cleanPermText = permText.startsWith('d{') ? permText.substring(1) : permText;
          const permData = JSON.parse(cleanPermText);
          const currentUserPerms = permData.find(u => u.id === userData.id);
          
          // Set permissions with proper defaults
          const permissions = currentUserPerms?.permissions || {
            can_create: true,
            can_view: true,
            can_edit: true,
            can_update: true,
            can_delete: false
          };
          
          // Ensure all permission properties exist
          setUserPermissions({
            can_create: permissions.can_create !== false,
            can_view: permissions.can_view !== false,
            can_edit: permissions.can_edit !== false,
            can_update: permissions.can_update !== false,
            can_delete: permissions.can_delete === true
          });
        } else {
          // Default permissions if API call fails
          setUserPermissions({
            can_create: true,
            can_view: true,
            can_edit: true,
            can_update: true,
            can_delete: false
          });
        }
      } else {
        localStorage.removeItem('token');
        setToken(null);
      }
    } catch (err) {
      localStorage.removeItem('token');
      setToken(null);
    }
  };

  const fetchTasks = async () => {
    try {
      setTasksLoading(true);
      let url = `${API_BASE}/tasks?page=${currentPage}&per_page=${perPage}`;
      if (userFilter && user?.role === 'admin') {
        url += `&user_id=${userFilter}`;
      }
      
      const response = await fetch(url, {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      
      if (response.ok) {
        const data = await response.json();
        setTasks(data.data || []);
        setCurrentPage(data.current_page || 1);
        setLastPage(data.last_page || 1);
        setTotalTasks(data.total || 0);
      }
    } catch (err) {
      console.error('Failed to fetch tasks:', err);
    } finally {
      setTasksLoading(false);
    }
  };

  const fetchUsers = async () => {
    try {
      const response = await fetch(`${API_BASE}/users`, {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      
      if (response.ok) {
        const users = await response.json();
        setAllUsers(users);
      }
    } catch (err) {
      console.error('Failed to fetch users:', err);
    }
  };

  const handleAuth = async (e) => {
    e.preventDefault();
    setAuthLoading(true);
    setAuthErrors({});
    
    try {
      const endpoint = isLogin ? '/login' : '/register';
      const payload = isLogin 
        ? { email: authForm.email, password: authForm.password }
        : authForm;
      
      const response = await fetch(`${API_BASE}${endpoint}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      
      const text = await response.text();
      const cleanText = text;
      const data = JSON.parse(cleanText);
      
      if (response.ok) {
        setToken(data.token);
        setUser(data.user);
        localStorage.setItem('token', data.token);
        setAuthForm({ name: '', email: '', password: '', role: 'user' });
      } else {
        if (data.errors) {
          setAuthErrors(data.errors);
        } else {
          setAuthErrors({ general: data.message || 'Authentication failed' });
        }
      }
    } catch (err) {
      console.error('Auth error:', err);
      setAuthErrors({ general: 'Network error. Please try again.' });
    } finally {
      setAuthLoading(false);
    }
  };

  const handleLogout = () => {
    setToken(null);
    setUser(null);
    setUserPermissions(null);
    setTasks([]);
    localStorage.removeItem('token');
    setCurrentPage(1);
    setUserFilter('');
  };

  const handleTaskSubmit = async (e) => {
    e.preventDefault();
    setTaskLoading(true);
    setTaskErrors({});
    
    try {
      const method = editingTask ? 'PUT' : 'POST';
      const url = editingTask 
        ? `${API_BASE}/tasks/${editingTask.id}` 
        : `${API_BASE}/tasks`;
      
      const response = await fetch(url, {
        method,
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(taskForm)
      });
      
      const data = await response.json();
      
      if (response.ok) {
        setTaskForm({ title: '', description: '' });
        setEditingTask(null);
        fetchTasks();
      } else {
        if (data.errors) {
          setTaskErrors(data.errors);
        } else {
          setTaskErrors({ general: data.message || 'Task operation failed' });
        }
      }
    } catch (err) {
      setTaskErrors({ general: 'Network error. Please try again.' });
    } finally {
      setTaskLoading(false);
    }
  };

  const handleTaskDelete = async (taskId) => {
    if (!confirm('Are you sure you want to delete this task?')) return;
    
    try {
      const response = await fetch(`${API_BASE}/tasks/${taskId}`, {
        method: 'DELETE',
        headers: { 'Authorization': `Bearer ${token}` }
      });
      
      if (response.ok) {
        fetchTasks();
      }
    } catch (err) {
      console.error('Failed to delete task:', err);
    }
  };

  const handleTaskToggle = async (task) => {
    try {
      const response = await fetch(`${API_BASE}/tasks/${task.id}`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ ...task, completed: !task.completed })
      });
      
      if (response.ok) {
        fetchTasks();
      }
    } catch (err) {
      console.error('Failed to toggle task:', err);
    }
  };

  const startEdit = (task) => {
    setEditingTask(task);
    setTaskForm({ title: task.title, description: task.description });
    setTaskErrors({});
  };

  const cancelEdit = () => {
    setEditingTask(null);
    setTaskForm({ title: '', description: '' });
    setTaskErrors({});
  };

  const fetchPermissions = async () => {
    try {
      setPermissionsLoading(true);
      const response = await fetch(`${API_BASE}/permissions`, {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      
      if (response.ok) {
        const text = await response.text();
        const cleanText = text.startsWith('d{') ? text.substring(1) : text;
        const data = JSON.parse(cleanText);
        setPermissionsUsers(data);
      }
    } catch (err) {
      console.error('Failed to fetch permissions:', err);
    } finally {
      setPermissionsLoading(false);
    }
  };

  const updatePermissions = async (userId, permissions) => {
    try {
      const response = await fetch(`${API_BASE}/permissions/${userId}`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(permissions)
      });
      
      if (response.ok) {
        const text = await response.text();
        const cleanText = text.startsWith('d{') ? text.substring(1) : text;
        JSON.parse(cleanText); // Validate JSON
        fetchPermissions(); // Refresh permissions
        return true;
      } else {
        console.error('Update failed:', response.status, await response.text());
        return false;
      }
    } catch (err) {
      console.error('Failed to update permissions:', err);
      return false;
    }
  };

  // Render pagination
  const renderPagination = () => {
    if (lastPage <= 1) return null;
    
    const pages = [];
    const maxVisible = 5;
    let start = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let end = Math.min(lastPage, start + maxVisible - 1);
    
    if (end - start + 1 < maxVisible) {
      start = Math.max(1, end - maxVisible + 1);
    }
    
    return (
      <div className="pagination">
        <button 
          onClick={() => setCurrentPage(1)}
          disabled={currentPage === 1}
          className="pagination-btn"
        >
          First
        </button>
        
        <button 
          onClick={() => setCurrentPage(currentPage - 1)}
          disabled={currentPage === 1}
          className="pagination-btn"
        >
          Previous
        </button>
        
        {Array.from({ length: end - start + 1 }, (_, i) => start + i).map(page => (
          <button
            key={page}
            onClick={() => setCurrentPage(page)}
            className={`pagination-btn ${currentPage === page ? 'active' : ''}`}
          >
            {page}
          </button>
        ))}
        
        <button 
          onClick={() => setCurrentPage(currentPage + 1)}
          disabled={currentPage === lastPage}
          className="pagination-btn"
        >
          Next
        </button>
        
        <button 
          onClick={() => setCurrentPage(lastPage)}
          disabled={currentPage === lastPage}
          className="pagination-btn"
        >
          Last
        </button>
      </div>
    );
  };

  // Auth form
  if (!token) {
    return (
      <div className="auth-container">
        <div className="auth-form">
          <h2>{isLogin ? 'Login' : 'Register'}</h2>
          
          {authErrors.general && (
            <div className="error-message">{authErrors.general}</div>
          )}
          
          <form onSubmit={handleAuth}>
            {!isLogin && (
              <div className="form-group">
                <input
                  type="text"
                  placeholder="Full Name"
                  value={authForm.name}
                  onChange={(e) => setAuthForm({...authForm, name: e.target.value})}
                  className={authErrors.name ? 'error' : ''}
                  required
                />
                {authErrors.name && (
                  <div className="field-error">{authErrors.name[0]}</div>
                )}
              </div>
            )}
            
            <div className="form-group">
              <input
                type="email"
                placeholder="Email Address"
                value={authForm.email}
                onChange={(e) => setAuthForm({...authForm, email: e.target.value})}
                className={authErrors.email ? 'error' : ''}
                required
              />
              {authErrors.email && (
                <div className="field-error">{authErrors.email[0]}</div>
              )}
            </div>
            
            <div className="form-group">
              <input
                type="password"
                placeholder="Password"
                value={authForm.password}
                onChange={(e) => setAuthForm({...authForm, password: e.target.value})}
                className={authErrors.password ? 'error' : ''}
                required
              />
              {authErrors.password && (
                <div className="field-error">{authErrors.password[0]}</div>
              )}
            </div>
            
            {!isLogin && (
              <div className="form-group">
                <select
                  value={authForm.role}
                  onChange={(e) => setAuthForm({...authForm, role: e.target.value})}
                >
                  <option value="user">User</option>
                  <option value="admin">Admin</option>
                </select>
              </div>
            )}
            
            <button type="submit" disabled={authLoading} className="btn-primary">
              {authLoading ? 'Loading...' : (isLogin ? 'Login' : 'Register')}
            </button>
          </form>
          
          <div className="auth-switch">
            {isLogin ? "Don't have an account? " : "Already have an account? "}
            <button 
              type="button" 
              onClick={() => {
                setIsLogin(!isLogin);
                setAuthErrors({});
                setAuthForm({ name: '', email: '', password: '', role: 'user' });
              }}
              className="link-btn"
            >
              {isLogin ? 'Register' : 'Login'}
            </button>
          </div>
          
          <div className="back-link">
            <a href="/">← Back to Home</a>
          </div>
        </div>
      </div>
    );
  }

  // Main dashboard
  return (
    <div className="dashboard">
      <header className="dashboard-header">
        <div className="header-content">
          <h1>Task Manager</h1>
          <div className="user-info">
            <span>Welcome, {user?.name}</span>
            <span className={`role-badge ${user?.role}`}>{user?.role}</span>
            <button onClick={handleLogout} className="btn-logout">Logout</button>
          </div>
        </div>
        
        {user?.role === 'admin' && (
          <div className="nav-tabs">
            <button 
              onClick={() => setCurrentView('tasks')} 
              className={currentView === 'tasks' ? 'active' : ''}
            >
              Tasks
            </button>
            <button 
              onClick={() => {
                setCurrentView('permissions');
                fetchPermissions();
              }} 
              className={currentView === 'permissions' ? 'active' : ''}
            >
              Permissions
            </button>
          </div>
        )}
      </header>

      <div className="dashboard-content">
        {currentView === 'tasks' ? (
          <>
            {/* Task Form */}
            {userPermissions?.can_create ? (
              <div className="task-form-section">
                <h3>{editingTask ? 'Edit Task' : 'Create New Task'}</h3>
                
                {taskErrors.general && (
                  <div className="error-message">{taskErrors.general}</div>
                )}
                
                <form onSubmit={handleTaskSubmit} className="task-form">
                  <div className="form-group">
                    <input
                      type="text"
                      placeholder="Task title (minimum 3 characters)"
                      value={taskForm.title}
                      onChange={(e) => setTaskForm({...taskForm, title: e.target.value})}
                      className={taskErrors.title ? 'error' : ''}
                      required
                    />
                    {taskErrors.title && (
                      <div className="field-error">{taskErrors.title[0]}</div>
                    )}
                  </div>
                  
                  <div className="form-group">
                    <textarea
                      placeholder="Task description (optional, max 500 characters)"
                      value={taskForm.description}
                      onChange={(e) => setTaskForm({...taskForm, description: e.target.value})}
                      className={taskErrors.description ? 'error' : ''}
                      maxLength={500}
                      rows={3}
                    />
                    {taskErrors.description && (
                      <div className="field-error">{taskErrors.description[0]}</div>
                    )}
                  </div>
                  
                  <div className="form-actions">
                    <button type="submit" disabled={taskLoading} className="btn-primary">
                      {taskLoading ? 'Saving...' : (editingTask ? 'Update Task' : 'Create Task')}
                    </button>
                    
                    {editingTask && (
                      <button type="button" onClick={cancelEdit} className="btn-secondary">
                        Cancel
                      </button>
                    )}
                  </div>
                </form>
              </div>
            ) : (
              <div className="permission-denied">
                <h3>Permission Denied</h3>
                <p>You don't have permission to create tasks. Contact your administrator.</p>
              </div>
            )}

            {/* Admin Filters */}
            {user?.role === 'admin' && (
              <div className="admin-filters">
                <h3>Admin Filters</h3>
                <div className="filter-group">
                  <select
                    value={userFilter}
                    onChange={(e) => {
                      setUserFilter(e.target.value);
                      setCurrentPage(1);
                    }}
                  >
                    <option value="">All Users</option>
                    {allUsers.map(u => (
                      <option key={u.id} value={u.id}>{u.name} ({u.email})</option>
                    ))}
                  </select>
                </div>
              </div>
            )}

            {/* Tasks List */}
            <div className="tasks-section">
              <div className="tasks-header">
                <h3>
                  {user?.role === 'admin' ? 'All Tasks' : 'My Tasks'} 
                  ({totalTasks} total)
                </h3>
                <div className="pagination-controls">
                  <select 
                    value={perPage} 
                    onChange={(e) => {
                      setPerPage(Number(e.target.value));
                      setCurrentPage(1);
                    }}
                    className="per-page-select"
                  >
                    <option value={5}>5 per page</option>
                    <option value={10}>10 per page</option>
                    <option value={25}>25 per page</option>
                    <option value={50}>50 per page</option>
                  </select>
                </div>
              </div>
              
              {tasksLoading ? (
                <div className="loading">Loading tasks...</div>
              ) : tasks.length === 0 ? (
                <div className="empty-state">
                  <p>No tasks found. Create your first task above!</p>
                </div>
              ) : (
                <>
                  <div className="tasks-grid">
                    {tasks.map(task => (
                      <div key={task.id} className={`task-card ${task.completed ? 'completed' : ''}`}>
                        <div className="task-header">
                          <h4>{task.title}</h4>
                          <div className="task-status">
                            <span className={`status-badge ${task.completed ? 'completed' : 'pending'}`}>
                              {task.completed ? 'Completed' : 'Pending'}
                            </span>
                          </div>
                        </div>
                        
                        {task.description && (
                          <p className="task-description">{task.description}</p>
                        )}
                        
                        {user?.role === 'admin' && task.user && (
                          <div className="task-owner">
                            <small>Owner: {task.user.name}</small>
                          </div>
                        )}
                        
                        <div className="task-actions">
                          {userPermissions?.can_update && (
                            <button
                              onClick={() => handleTaskToggle(task)}
                              className={`btn-toggle ${task.completed ? 'btn-incomplete' : 'btn-complete'}`}
                            >
                              {task.completed ? 'Mark Incomplete' : 'Mark Complete'}
                            </button>
                          )}
                          
                          {userPermissions?.can_edit && (
                            <button
                              onClick={() => startEdit(task)}
                              className="btn-edit"
                            >
                              Edit
                            </button>
                          )}
                          
                          {userPermissions?.can_delete && (
                            <button
                              onClick={() => handleTaskDelete(task.id)}
                              className="btn-delete"
                            >
                              Delete
                            </button>
                          )}
                        </div>
                      </div>
                    ))}
                  </div>
                  
                  {renderPagination()}
                </>
              )}
            </div>
          </>
        ) : (
          /* Permissions View */
          <div className="permissions-section">
            <h3>User Permissions</h3>
            {permissionsLoading ? (
              <div className="loading">Loading permissions...</div>
            ) : (
              <div className="permissions-table-container">
                <table className="permissions-table">
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
                  <tbody>
                    {permissionsUsers.map(user => (
                      <PermissionRow key={user.id} user={user} onUpdate={updatePermissions} />
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
};

const container = document.getElementById('react-app');
if (container) {
  const root = createRoot(container);
  root.render(React.createElement(TaskApp));
}