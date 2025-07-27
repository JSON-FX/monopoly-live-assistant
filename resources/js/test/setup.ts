import '@testing-library/jest-dom';

// Mock global route function for Inertia.js
(global as Record<string, unknown>).route = (name: string) => {
  const routes: Record<string, string> = {
    'register': '/register',
    'login': '/login',
    'dashboard': '/dashboard',
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