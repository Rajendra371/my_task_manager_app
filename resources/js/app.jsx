import React from 'react';
import { createRoot } from 'react-dom/client';
import './app.css';

const API_BASE = 'http://127.0.0.1:8000/api';

const TaskManager = () => {
  const [user, setUser] = React.useState(null);
  const [token, setToken] = React.useState(null);
  const [tasks, setTasks] = React.useState([]);
  const [loading, setLoading] = React.useState(false);
  const [error, setError] = React.useState('');
  const [isLogin, setIsLogin] = React.useState(true);
  const [isInitializing, setIsInitializing] = React.useState(true);
  
  const [formData, setFormData] = React.useState({
    name: '', 
    email: '', 
    password: '', 
    role: 'user'
  });
  
  const [taskForm, setTaskForm] = React.useState({ 
    title: '', 
    description: '' 
  });
  
  const [editingTask, setEditingTask] = React.useState(null);

  React.useEffect(() => {
    const savedToken = localStorage.getItem('token');
    if (savedToken) {
      fetch(`${API_BASE}/user`, {
        headers: { 'Authorization': `Bearer ${savedToken}` }
      }).then(response => {
        if (response.ok) {
          return response.json();
        }
        throw new Error('Invalid token');
      }).then(data => {
        setToken(savedToken);
        setUser(data);
      }).catch(() => {
        localStorage.clear();
      });
    }
    setIsInitializing(false);
  }, []);

  React.useEffect(() => {
    if (token) {
      fetchTasks();
    }
  }, [token]);

  const fetchTasks = async () => {
    if (!token) return;
    
    try {
      setLoading(true);
      const response = await fetch(`${API_BASE}/tasks`, {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      
      if (response.status === 401) {
        handleLogout();
        setError('Session expired. Please login again.');
        return;
      }
      
      const data = await response.json();
      setTasks(data.data || data);
    } catch (err) {
      setError('Failed to fetch tasks');
    } finally {
      setLoading(false);
    }
  };

  const handleAuth = async (e) => {
    e.preventDefault();
    try {
      setLoading(true);
      setError('');
      
      const endpoint = isLogin ? '/login' : '/register';
      const payload = isLogin 
        ? { email: formData.email, password: formData.password }
        : formData;
      
      const response = await fetch(`${API_BASE}${endpoint}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      
      const data = await response.json();
      
      if (!response.ok) {
        throw new Error(data.message || 'Authentication failed');
      }
      
      setToken(data.token);
      setUser(data.user);
      localStorage.setItem('token', data.token);
      setFormData({ name: '', email: '', password: '', role: 'user' });
      
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = () => {
    setToken(null);
    setUser(null);
    setTasks([]);
    localStorage.clear();
    setError('');
  };

  if (isInitializing) {
    return React.createElement('div', { 
      style: { display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: '100vh' } 
    }, React.createElement('div', null, 'Loading...'));
  }

  if (!token) {
    return React.createElement('div', { className: 'auth-container' },
      React.createElement('div', { className: 'auth-form' },
        React.createElement('h2', null, isLogin ? 'Login' : 'Register'),
        error && React.createElement('div', { className: 'error' }, error),
        React.createElement('form', { onSubmit: handleAuth },
          !isLogin && React.createElement('input', {
            type: 'text',
            placeholder: 'Name',
            value: formData.name,
            onChange: (e) => setFormData({...formData, name: e.target.value}),
            required: true
          }),
          React.createElement('input', {
            type: 'email',
            placeholder: 'Email',
            value: formData.email,
            onChange: (e) => setFormData({...formData, email: e.target.value}),
            required: true
          }),
          React.createElement('input', {
            type: 'password',
            placeholder: 'Password',
            value: formData.password,
            onChange: (e) => setFormData({...formData, password: e.target.value}),
            required: true
          }),
          React.createElement('button', { type: 'submit', disabled: loading },
            loading ? 'Loading...' : (isLogin ? 'Login' : 'Register')
          )
        ),
        React.createElement('p', null,
          isLogin ? "Don't have an account? " : "Already have an account? ",
          React.createElement('button', { 
            onClick: () => setIsLogin(!isLogin) 
          }, isLogin ? 'Register' : 'Login')
        )
      )
    );
  }

  return React.createElement('div', { className: 'app' },
    React.createElement('header', null,
      React.createElement('h1', null, 'Task Manager'),
      React.createElement('div', null,
        React.createElement('span', null, `Welcome, ${user?.name}`),
        React.createElement('button', { onClick: handleLogout }, 'Logout')
      )
    ),
    error && React.createElement('div', { className: 'error' }, error),
    React.createElement('div', { className: 'tasks-section' },
      React.createElement('h3', null, 'Tasks'),
      loading ? React.createElement('div', null, 'Loading tasks...') :
      React.createElement('div', { className: 'tasks-list' },
        tasks.map(task => 
          React.createElement('div', { 
            key: task.id, 
            className: `task ${task.completed ? 'completed' : ''}` 
          },
            React.createElement('div', { className: 'task-content' },
              React.createElement('h4', null, task.title),
              React.createElement('p', null, task.description)
            )
          )
        )
      )
    )
  );
};

const container = document.getElementById('react-app');
if (container) {
  const root = createRoot(container);
  root.render(React.createElement(TaskManager));
}