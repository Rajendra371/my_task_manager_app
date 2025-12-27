import React, { useState, useEffect } from 'react';
import axios from 'axios';
import './App.css';

const API_BASE = 'http://127.0.0.1:8000/api';

interface User {
  id: number;
  name: string;
  email: string;
  role: 'user' | 'admin';
}

interface Task {
  id: number;
  title: string;
  description: string;
  completed: boolean;
  user_id: number;
}

interface PaginatedTasks {
  data: Task[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

function App() {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(localStorage.getItem('token'));
  const [tasks, setTasks] = useState<Task[]>([]);
  const [currentPage, setCurrentPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  // Auth forms
  const [isLogin, setIsLogin] = useState(true);
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    role: 'user' as 'user' | 'admin'
  });

  // Task form
  const [taskForm, setTaskForm] = useState({
    title: '',
    description: ''
  });
  const [editingTask, setEditingTask] = useState<Task | null>(null);

  useEffect(() => {
    if (token) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      fetchTasks();
    }
  }, [token, currentPage]);

  const fetchTasks = async () => {
    try {
      setLoading(true);
      const response = await axios.get<PaginatedTasks>(`${API_BASE}/tasks?page=${currentPage}`);
      setTasks(response.data.data);
      setCurrentPage(response.data.current_page);
      setLastPage(response.data.last_page);
    } catch (err) {
      setError('Failed to fetch tasks');
    } finally {
      setLoading(false);
    }
  };

  const handleAuth = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      setLoading(true);
      setError('');
      
      const endpoint = isLogin ? '/login' : '/register';
      const payload = isLogin 
        ? { email: formData.email, password: formData.password }
        : formData;
      
      const response = await axios.post(`${API_BASE}${endpoint}`, payload);
      
      setToken(response.data.token);
      setUser(response.data.user);
      localStorage.setItem('token', response.data.token);
      axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
      
      setFormData({ name: '', email: '', password: '', role: 'user' });
    } catch (err: any) {
      setError(err.response?.data?.message || 'Authentication failed');
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = () => {
    setToken(null);
    setUser(null);
    localStorage.removeItem('token');
    delete axios.defaults.headers.common['Authorization'];
  };

  const handleTaskSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      setLoading(true);
      setError('');
      
      if (editingTask) {
        await axios.put(`${API_BASE}/tasks/${editingTask.id}`, taskForm);
        setEditingTask(null);
      } else {
        await axios.post(`${API_BASE}/tasks`, taskForm);
      }
      
      setTaskForm({ title: '', description: '' });
      fetchTasks();
    } catch (err: any) {
      setError(err.response?.data?.message || 'Task operation failed');
    } finally {
      setLoading(false);
    }
  };

  const handleTaskDelete = async (id: number) => {
    try {
      await axios.delete(`${API_BASE}/tasks/${id}`);
      fetchTasks();
    } catch (err) {
      setError('Failed to delete task');
    }
  };

  const handleTaskToggle = async (task: Task) => {
    try {
      await axios.put(`${API_BASE}/tasks/${task.id}`, {
        ...task,
        completed: !task.completed
      });
      fetchTasks();
    } catch (err) {
      setError('Failed to update task');
    }
  };

  if (!token) {
    return (
      <div className="auth-container">
        <div className="auth-form">
          <h2>{isLogin ? 'Login' : 'Register'}</h2>
          {error && <div className="error">{error}</div>}
          
          <form onSubmit={handleAuth}>
            {!isLogin && (
              <input
                type="text"
                placeholder="Name"
                value={formData.name}
                onChange={(e) => setFormData({...formData, name: e.target.value})}
                required
              />
            )}
            
            <input
              type="email"
              placeholder="Email"
              value={formData.email}
              onChange={(e) => setFormData({...formData, email: e.target.value})}
              required
            />
            
            <input
              type="password"
              placeholder="Password"
              value={formData.password}
              onChange={(e) => setFormData({...formData, password: e.target.value})}
              required
            />
            
            {!isLogin && (
              <select
                value={formData.role}
                onChange={(e) => setFormData({...formData, role: e.target.value as 'user' | 'admin'})}
              >
                <option value="user">User</option>
                <option value="admin">Admin</option>
              </select>
            )}
            
            <button type="submit" disabled={loading}>
              {loading ? 'Loading...' : (isLogin ? 'Login' : 'Register')}
            </button>
          </form>
          
          <p>
            {isLogin ? "Don't have an account? " : "Already have an account? "}
            <button onClick={() => setIsLogin(!isLogin)}>
              {isLogin ? 'Register' : 'Login'}
            </button>
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="app">
      <header>
        <h1>Task Manager</h1>
        <div>
          <span>Welcome, {user?.name} ({user?.role})</span>
          <button onClick={handleLogout}>Logout</button>
        </div>
      </header>

      {error && <div className="error">{error}</div>}

      <div className="task-form">
        <h3>{editingTask ? 'Edit Task' : 'Create Task'}</h3>
        <form onSubmit={handleTaskSubmit}>
          <input
            type="text"
            placeholder="Task title (min 3 chars)"
            value={taskForm.title}
            onChange={(e) => setTaskForm({...taskForm, title: e.target.value})}
            required
            minLength={3}
          />
          
          <textarea
            placeholder="Description (max 500 chars)"
            value={taskForm.description}
            onChange={(e) => setTaskForm({...taskForm, description: e.target.value})}
            maxLength={500}
          />
          
          <div>
            <button type="submit" disabled={loading}>
              {loading ? 'Loading...' : (editingTask ? 'Update' : 'Create')}
            </button>
            {editingTask && (
              <button type="button" onClick={() => {
                setEditingTask(null);
                setTaskForm({ title: '', description: '' });
              }}>
                Cancel
              </button>
            )}
          </div>
        </form>
      </div>

      <div className="tasks-section">
        <h3>Tasks</h3>
        {loading ? (
          <div>Loading tasks...</div>
        ) : (
          <>
            <div className="tasks-list">
              {tasks.map(task => (
                <div key={task.id} className={`task ${task.completed ? 'completed' : ''}`}>
                  <div className="task-content">
                    <h4>{task.title}</h4>
                    <p>{task.description}</p>
                    {user?.role === 'admin' && <small>User ID: {task.user_id}</small>}
                  </div>
                  
                  <div className="task-actions">
                    <button onClick={() => handleTaskToggle(task)}>
                      {task.completed ? 'Mark Incomplete' : 'Mark Complete'}
                    </button>
                    
                    <button onClick={() => {
                      setEditingTask(task);
                      setTaskForm({ title: task.title, description: task.description });
                    }}>
                      Edit
                    </button>
                    
                    <button onClick={() => handleTaskDelete(task.id)}>
                      Delete
                    </button>
                  </div>
                </div>
              ))}
            </div>

            <div className="pagination">
              <button 
                onClick={() => setCurrentPage(prev => Math.max(1, prev - 1))}
                disabled={currentPage === 1}
              >
                Previous
              </button>
              
              <span>Page {currentPage} of {lastPage}</span>
              
              <button 
                onClick={() => setCurrentPage(prev => Math.min(lastPage, prev + 1))}
                disabled={currentPage === lastPage}
              >
                Next
              </button>
            </div>
          </>
        )}
      </div>
    </div>
  );
}

export default App;