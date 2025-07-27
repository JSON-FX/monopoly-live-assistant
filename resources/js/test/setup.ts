import '@testing-library/jest-dom';

// Mock ResizeObserver
global.ResizeObserver = class ResizeObserver {
  constructor() {}
  observe() {}
  unobserve() {}
  disconnect() {}
};

// Mock global route function for Inertia.js
(global as Record<string, unknown>).route = (name: string) => {
  const routes: Record<string, string> = {
    'register': '/register',
    'login': '/login',
    'dashboard': '/dashboard',
    'password.request': '/forgot-password',
  };
  return routes[name] || `/${name}`;
};

// Mock window.Laravel object
Object.defineProperty(window, 'Laravel', {
  value: {
    csrfToken: 'mock-csrf-token',
  },
  writable: true,
}); 