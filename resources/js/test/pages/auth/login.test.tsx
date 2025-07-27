import { render, screen, fireEvent } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';

// Create mock functions that will be hoisted
const mockUseForm = vi.fn();
const mockPost = vi.fn();
const mockReset = vi.fn();
const mockSetData = vi.fn();

// Mock Inertia.js completely
vi.mock('@inertiajs/react', () => ({
    useForm: () => mockUseForm(),
    Head: ({ children }: { children?: React.ReactNode }) => <div data-testid="head">{children}</div>,
    Link: ({ href, children, ...props }: any) => <a href={href} {...props}>{children}</a>,
    router: {
        visit: vi.fn(),
    },
}));

// Import after mocking
import Login from '@/pages/auth/login';

describe('Login Component', () => {
    const defaultProps = {
        status: undefined,
        canResetPassword: true,
    };

    beforeEach(() => {
        vi.clearAllMocks();
        // Setup default mock return values
        mockUseForm.mockReturnValue({
            data: { email: '', password: '', remember: false },
            setData: mockSetData,
            post: mockPost,
            processing: false,
            errors: {},
            reset: mockReset,
        });
    });

    it('renders login form with all required fields', () => {
        render(<Login {...defaultProps} />);

        // Check form fields are present
        expect(screen.getByLabelText(/email address/i)).toBeInTheDocument();
        expect(screen.getByLabelText(/^password$/i)).toBeInTheDocument();
        expect(screen.getByLabelText(/remember me/i)).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /log in/i })).toBeInTheDocument();
    });

    it('displays proper input types and attributes', () => {
        render(<Login {...defaultProps} />);

        const emailInput = screen.getByLabelText(/email address/i);
        const passwordInput = screen.getByLabelText(/^password$/i);

        expect(emailInput).toHaveAttribute('type', 'email');
        expect(emailInput).toHaveAttribute('required');
        expect(emailInput).toHaveAttribute('autocomplete', 'email');
        expect(emailInput).toHaveAttribute('placeholder', 'email@example.com');

        expect(passwordInput).toHaveAttribute('type', 'password');
        expect(passwordInput).toHaveAttribute('required');
        expect(passwordInput).toHaveAttribute('autocomplete', 'current-password');
        expect(passwordInput).toHaveAttribute('placeholder', 'Password');
    });

    it('shows forgot password link when canResetPassword is true', () => {
        render(<Login {...defaultProps} canResetPassword={true} />);

        expect(screen.getByText(/forgot password\?/i)).toBeInTheDocument();
        expect(screen.getByText(/forgot password\?/i)).toHaveAttribute('href', '/forgot-password');
    });

    it('hides forgot password link when canResetPassword is false', () => {
        render(<Login {...defaultProps} canResetPassword={false} />);

        expect(screen.queryByText(/forgot password\?/i)).not.toBeInTheDocument();
    });

    it('shows sign up link', () => {
        render(<Login {...defaultProps} />);

        expect(screen.getByText(/don't have an account\?/i)).toBeInTheDocument();
        expect(screen.getByText(/sign up/i)).toHaveAttribute('href', '/register');
    });

    it('handles form input changes', () => {
        render(<Login {...defaultProps} />);

        const emailInput = screen.getByLabelText(/email address/i);
        const passwordInput = screen.getByLabelText(/^password$/i);
        const rememberCheckbox = screen.getByLabelText(/remember me/i);

        fireEvent.change(emailInput, { target: { value: 'test@example.com' } });
        fireEvent.change(passwordInput, { target: { value: 'password123' } });
        fireEvent.click(rememberCheckbox);

        expect(mockSetData).toHaveBeenCalledWith('email', 'test@example.com');
        expect(mockSetData).toHaveBeenCalledWith('password', 'password123');
        expect(mockSetData).toHaveBeenCalledWith('remember', true);
    });

    it('handles form submission', () => {
        render(<Login {...defaultProps} />);

        const form = screen.getByRole('button', { name: /log in/i }).closest('form');
        fireEvent.submit(form!);

        expect(mockPost).toHaveBeenCalledWith(expect.any(String), {
            onFinish: expect.any(Function),
        });
    });

    it('displays validation errors', () => {
        mockUseForm.mockReturnValue({
            data: { email: '', password: '', remember: false },
            setData: mockSetData,
            post: mockPost,
            processing: false,
            errors: {
                email: 'The email field is required.',
                password: 'The password field is required.',
            },
            reset: mockReset,
        });

        render(<Login {...defaultProps} />);

        expect(screen.getByText('The email field is required.')).toBeInTheDocument();
        expect(screen.getByText('The password field is required.')).toBeInTheDocument();
    });

    it('displays authentication failure error', () => {
        mockUseForm.mockReturnValue({
            data: { email: 'test@example.com', password: 'wrongpassword', remember: false },
            setData: mockSetData,
            post: mockPost,
            processing: false,
            errors: {
                email: 'These credentials do not match our records.',
            },
            reset: mockReset,
        });

        render(<Login {...defaultProps} />);

        expect(screen.getByText('These credentials do not match our records.')).toBeInTheDocument();
    });

    it('shows loading state during form submission', () => {
        mockUseForm.mockReturnValue({
            data: { email: 'test@example.com', password: 'password123', remember: false },
            setData: mockSetData,
            post: mockPost,
            processing: true,
            errors: {},
            reset: mockReset,
        });

        render(<Login {...defaultProps} />);

        const submitButton = screen.getByRole('button', { name: /log in/i });
        expect(submitButton).toBeDisabled();
        expect(screen.getByTestId('loader-icon')).toBeInTheDocument();
    });

    it('displays status message when provided', () => {
        render(<Login {...defaultProps} status="You have been logged out." />);

        expect(screen.getByText('You have been logged out.')).toBeInTheDocument();
        expect(screen.getByText('You have been logged out.')).toHaveClass('text-green-600');
    });

    it('has proper accessibility attributes', () => {
        render(<Login {...defaultProps} />);

        const emailInput = screen.getByLabelText(/email address/i);
        const passwordInput = screen.getByLabelText(/^password$/i);
        const rememberCheckbox = screen.getByLabelText(/remember me/i);
        const submitButton = screen.getByRole('button', { name: /log in/i });

        // Check labels are properly associated
        expect(emailInput).toHaveAttribute('id', 'email');
        expect(passwordInput).toHaveAttribute('id', 'password');
        expect(rememberCheckbox).toHaveAttribute('id', 'remember');

        // Check tab order
        expect(emailInput).toHaveAttribute('tabindex', '1');
        expect(passwordInput).toHaveAttribute('tabindex', '2');
        expect(rememberCheckbox).toHaveAttribute('tabindex', '3');
        expect(submitButton).toHaveAttribute('tabindex', '4');
    });

    it('handles remember me checkbox toggle', () => {
        render(<Login {...defaultProps} />);

        const rememberCheckbox = screen.getByLabelText(/remember me/i);
        
        // Test checking the checkbox
        fireEvent.click(rememberCheckbox);
        expect(mockSetData).toHaveBeenCalledWith('remember', true);
    });

    it('prevents multiple form submissions when processing', () => {
        mockUseForm.mockReturnValue({
            data: { email: 'test@example.com', password: 'password123', remember: false },
            setData: mockSetData,
            post: mockPost,
            processing: true,
            errors: {},
            reset: mockReset,
        });

        render(<Login {...defaultProps} />);

        const submitButton = screen.getByRole('button', { name: /log in/i });
        expect(submitButton).toBeDisabled();
    });
}); 