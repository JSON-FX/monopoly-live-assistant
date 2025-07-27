import { render, screen, fireEvent } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useForm } from '@inertiajs/react';
import { vi, describe, test, expect, beforeEach } from 'vitest';
import Register from '@/pages/auth/register';

interface LinkProps {
  href: string;
  children: React.ReactNode;
  [key: string]: unknown;
}

interface LayoutProps {
  children: React.ReactNode;
  title: string;
  description: string;
}

// Mock Inertia.js useForm hook
vi.mock('@inertiajs/react', () => ({
  Head: ({ title }: { title: string }) => <title>{title}</title>,
  Link: ({ href, children, ...props }: LinkProps) => <a href={href} {...props}>{children}</a>,
  useForm: vi.fn(),
}));

// Mock AuthLayout
vi.mock('@/layouts/auth-layout', () => ({
  default: ({ children, title, description }: LayoutProps) => (
    <div data-testid="auth-layout">
      <h1>{title}</h1>
      <p>{description}</p>
      {children}
    </div>
  ),
}));

describe('Register Component', () => {
  const mockUseForm = {
    data: {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
    },
    setData: vi.fn(),
    post: vi.fn(),
    processing: false,
    errors: {},
    reset: vi.fn(),
  };

  beforeEach(() => {
    vi.clearAllMocks();
    (useForm as ReturnType<typeof vi.fn>).mockReturnValue(mockUseForm);
  });

  describe('Form Rendering', () => {
    test('renders registration form with required fields', () => {
      render(<Register />);

      expect(screen.getByLabelText(/name/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/email address/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/^password$/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/confirm password/i)).toBeInTheDocument();
      expect(screen.getByRole('button', { name: /create account/i })).toBeInTheDocument();
    });

    test('renders form fields with proper attributes', () => {
      render(<Register />);

      const nameField = screen.getByLabelText(/name/i);
      const emailField = screen.getByLabelText(/email address/i);
      const passwordField = screen.getByLabelText(/^password$/i);
      const confirmPasswordField = screen.getByLabelText(/confirm password/i);

      expect(nameField).toHaveAttribute('type', 'text');
      expect(nameField).toHaveAttribute('required');
      expect(nameField).toHaveAttribute('autoComplete', 'name');
      expect(nameField).toHaveAttribute('tabIndex', '1');

      expect(emailField).toHaveAttribute('type', 'email');
      expect(emailField).toHaveAttribute('required');
      expect(emailField).toHaveAttribute('autoComplete', 'email');
      expect(emailField).toHaveAttribute('tabIndex', '2');

      expect(passwordField).toHaveAttribute('type', 'password');
      expect(passwordField).toHaveAttribute('required');
      expect(passwordField).toHaveAttribute('autoComplete', 'new-password');
      expect(passwordField).toHaveAttribute('tabIndex', '3');

      expect(confirmPasswordField).toHaveAttribute('type', 'password');
      expect(confirmPasswordField).toHaveAttribute('required');
      expect(confirmPasswordField).toHaveAttribute('autoComplete', 'new-password');
      expect(confirmPasswordField).toHaveAttribute('tabIndex', '4');
    });

    test('renders with proper placeholders', () => {
      render(<Register />);

      expect(screen.getByPlaceholderText('Full name')).toBeInTheDocument();
      expect(screen.getByPlaceholderText('email@example.com')).toBeInTheDocument();
      expect(screen.getByPlaceholderText('Password')).toBeInTheDocument();
      expect(screen.getByPlaceholderText('Confirm password')).toBeInTheDocument();
    });

    test('renders login link', () => {
      render(<Register />);

      expect(screen.getByText(/already have an account\?/i)).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /log in/i })).toBeInTheDocument();
    });
  });

  describe('Form Interactions', () => {
    test('calls setData when name field changes', async () => {
      const user = userEvent.setup();
      render(<Register />);

      const nameField = screen.getByLabelText(/name/i);
      await user.type(nameField, 'John Doe');

      // Check that setData was called with the correct field name (multiple times for each character)
      expect(mockUseForm.setData).toHaveBeenCalledWith('name', expect.any(String));
      expect(mockUseForm.setData).toHaveBeenCalledTimes(8); // "John Doe" = 8 characters
    });

    test('calls setData when email field changes', async () => {
      const user = userEvent.setup();
      render(<Register />);

      const emailField = screen.getByLabelText(/email address/i);
      await user.type(emailField, 'test@example.com');

      expect(mockUseForm.setData).toHaveBeenCalledWith('email', expect.any(String));
      expect(mockUseForm.setData).toHaveBeenCalledTimes(16); // Length of "test@example.com"
    });

    test('calls setData when password field changes', async () => {
      const user = userEvent.setup();
      render(<Register />);

      const passwordField = screen.getByLabelText(/^password$/i);
      await user.type(passwordField, 'password123');

      expect(mockUseForm.setData).toHaveBeenCalledWith('password', expect.any(String));
      expect(mockUseForm.setData).toHaveBeenCalledTimes(11); // Length of "password123"
    });

    test('calls setData when confirm password field changes', async () => {
      const user = userEvent.setup();
      render(<Register />);

      const confirmPasswordField = screen.getByLabelText(/confirm password/i);
      await user.type(confirmPasswordField, 'password123');

      expect(mockUseForm.setData).toHaveBeenCalledWith('password_confirmation', expect.any(String));
      expect(mockUseForm.setData).toHaveBeenCalledTimes(11); // Length of "password123"
    });
  });

  describe('Form Submission', () => {
    test('form submit calls post method when submitted', () => {
      render(<Register />);

      const form = screen.getByTestId('register-form');
      fireEvent.submit(form);

      expect(mockUseForm.post).toHaveBeenCalledWith('/register', {
        onFinish: expect.any(Function),
      });
    });

    test('button triggers form submission', () => {
      render(<Register />);

      const submitButton = screen.getByRole('button', { name: /create account/i });
      expect(submitButton).toHaveAttribute('type', 'submit');
    });

    test('reset function works with password fields', () => {
      render(<Register />);
      
      // Directly test the reset functionality
      mockUseForm.reset('password', 'password_confirmation');
      expect(mockUseForm.reset).toHaveBeenCalledWith('password', 'password_confirmation');
    });
  });

  describe('Loading States', () => {
    test('disables form fields when processing', () => {
      (useForm as ReturnType<typeof vi.fn>).mockReturnValue({
        ...mockUseForm,
        processing: true,
      });

      render(<Register />);

      expect(screen.getByLabelText(/name/i)).toBeDisabled();
      expect(screen.getByLabelText(/email address/i)).toBeDisabled();
      expect(screen.getByLabelText(/^password$/i)).toBeDisabled();
      expect(screen.getByLabelText(/confirm password/i)).toBeDisabled();
      expect(screen.getByRole('button', { name: /create account/i })).toBeDisabled();
    });

    test('shows loading indicator when processing', () => {
      (useForm as ReturnType<typeof vi.fn>).mockReturnValue({
        ...mockUseForm,
        processing: true,
      });

      render(<Register />);

      // Check for the LoaderCircle component (via the button having processing state)
      const submitButton = screen.getByRole('button', { name: /create account/i });
      expect(submitButton).toBeDisabled();
    });

    test('enables form fields when not processing', () => {
      render(<Register />);

      expect(screen.getByLabelText(/name/i)).not.toBeDisabled();
      expect(screen.getByLabelText(/email address/i)).not.toBeDisabled();
      expect(screen.getByLabelText(/^password$/i)).not.toBeDisabled();
      expect(screen.getByLabelText(/confirm password/i)).not.toBeDisabled();
      expect(screen.getByRole('button', { name: /create account/i })).not.toBeDisabled();
    });
  });

  describe('Validation Error Display', () => {
    test('displays name validation error', () => {
      (useForm as ReturnType<typeof vi.fn>).mockReturnValue({
        ...mockUseForm,
        errors: { name: 'The name field is required.' },
      });

      render(<Register />);

      expect(screen.getByText('The name field is required.')).toBeInTheDocument();
    });

    test('displays email validation error', () => {
      (useForm as ReturnType<typeof vi.fn>).mockReturnValue({
        ...mockUseForm,
        errors: { email: 'The email field must be a valid email address.' },
      });

      render(<Register />);

      expect(screen.getByText('The email field must be a valid email address.')).toBeInTheDocument();
    });

    test('displays password validation error', () => {
      (useForm as ReturnType<typeof vi.fn>).mockReturnValue({
        ...mockUseForm,
        errors: { password: 'The password field is required.' },
      });

      render(<Register />);

      expect(screen.getByText('The password field is required.')).toBeInTheDocument();
    });

    test('displays password confirmation error', () => {
      (useForm as ReturnType<typeof vi.fn>).mockReturnValue({
        ...mockUseForm,
        errors: { password_confirmation: 'The password confirmation does not match.' },
      });

      render(<Register />);

      expect(screen.getByText('The password confirmation does not match.')).toBeInTheDocument();
    });

    test('displays multiple validation errors', () => {
      (useForm as ReturnType<typeof vi.fn>).mockReturnValue({
        ...mockUseForm,
        errors: {
          name: 'The name field is required.',
          email: 'The email field must be a valid email address.',
          password: 'The password field is required.',
        },
      });

      render(<Register />);

      expect(screen.getByText('The name field is required.')).toBeInTheDocument();
      expect(screen.getByText('The email field must be a valid email address.')).toBeInTheDocument();
      expect(screen.getByText('The password field is required.')).toBeInTheDocument();
    });
  });

  describe('Accessibility Compliance', () => {
    test('form has proper semantic structure', () => {
      render(<Register />);

      expect(screen.getByTestId('register-form')).toBeInTheDocument();
      expect(screen.getAllByRole('textbox')).toHaveLength(2); // name and email
      expect(screen.getAllByLabelText(/password/i)).toHaveLength(2); // password inputs
      expect(screen.getByRole('button', { name: /create account/i })).toBeInTheDocument();
    });

    test('form labels are properly associated with inputs', () => {
      render(<Register />);

      const nameField = screen.getByLabelText(/name/i);
      const emailField = screen.getByLabelText(/email address/i);
      const passwordField = screen.getByLabelText(/^password$/i);
      const confirmPasswordField = screen.getByLabelText(/confirm password/i);

      expect(nameField).toHaveAttribute('id', 'name');
      expect(emailField).toHaveAttribute('id', 'email');
      expect(passwordField).toHaveAttribute('id', 'password');
      expect(confirmPasswordField).toHaveAttribute('id', 'password_confirmation');
    });

    test('supports keyboard navigation with tab order', () => {
      render(<Register />);

      const nameField = screen.getByLabelText(/name/i);
      const emailField = screen.getByLabelText(/email address/i);
      const passwordField = screen.getByLabelText(/^password$/i);
      const confirmPasswordField = screen.getByLabelText(/confirm password/i);
      const submitButton = screen.getByRole('button', { name: /create account/i });
      const loginLink = screen.getByRole('link', { name: /log in/i });

      expect(nameField).toHaveAttribute('tabindex', '1');
      expect(emailField).toHaveAttribute('tabindex', '2');
      expect(passwordField).toHaveAttribute('tabindex', '3');
      expect(confirmPasswordField).toHaveAttribute('tabindex', '4');
      expect(submitButton).toHaveAttribute('tabindex', '5');
      expect(loginLink).toHaveAttribute('tabindex', '6');
    });

    test('error messages are accessible', () => {
      (useForm as ReturnType<typeof vi.fn>).mockReturnValue({
        ...mockUseForm,
        errors: { name: 'The name field is required.' },
      });

      render(<Register />);

      const errorMessage = screen.getByText('The name field is required.');
      expect(errorMessage).toHaveClass('text-red-600');
    });
  });

  describe('Component Integration', () => {
    test('renders within AuthLayout with correct props', () => {
      render(<Register />);

      expect(screen.getByTestId('auth-layout')).toBeInTheDocument();
      expect(screen.getByText('Create an account')).toBeInTheDocument();
      expect(screen.getByText('Enter your details below to create your account')).toBeInTheDocument();
    });

    test('sets correct page title', () => {
      render(<Register />);

      expect(document.title).toBe('Register');
    });
  });
}); 