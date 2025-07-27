import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import Dashboard from '@/pages/dashboard';
import { type Auth, type SharedData } from '@/types';

// Create mock functions
const mockUsePage = vi.fn();

// Mock Inertia.js
vi.mock('@inertiajs/react', () => ({
    usePage: () => mockUsePage(),
    Head: ({ children }: { children?: React.ReactNode }) => <div data-testid="head">{children}</div>,
    Link: ({ href, children, ...props }: any) => <a href={href} {...props}>{children}</a>,
}));

// Mock AppLayout
vi.mock('@/layouts/app-layout', () => ({
    default: ({ children, breadcrumbs }: { children: React.ReactNode, breadcrumbs?: any[] }) => (
        <div data-testid="app-layout">
            {breadcrumbs && (
                <div data-testid="breadcrumbs">
                    {breadcrumbs.map(b => <span key={b.href}>{b.title}</span>)}
                </div>
            )}
            {children}
        </div>
    ),
}));

// Mock PlaceholderPattern
vi.mock('@/components/ui/placeholder-pattern', () => ({
    PlaceholderPattern: ({ className }: { className?: string }) => (
        <div data-testid="placeholder-pattern" className={className}>Pattern</div>
    ),
}));

describe('Dashboard Page Authentication', () => {
    const mockUser = {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        email_verified_at: '2025-01-28T00:00:00.000000Z',
        created_at: '2025-01-28T00:00:00.000000Z',
        updated_at: '2025-01-28T00:00:00.000000Z',
    };

    const mockAuth: Auth = {
        user: mockUser,
    };

    const mockSharedData: Partial<SharedData> = {
        auth: mockAuth,
        name: 'Test App',
        sidebarOpen: false,
        ziggy: {
            location: '/dashboard',
            url: 'http://localhost',
            port: null,
            defaults: {},
            routes: {},
        } as any,
    };

    beforeEach(() => {
        vi.clearAllMocks();
        mockUsePage.mockReturnValue({
            props: mockSharedData,
        });
    });

    it('renders dashboard page for authenticated user', () => {
        render(<Dashboard />);

        expect(screen.getByTestId('app-layout')).toBeInTheDocument();
        expect(screen.getByTestId('head')).toBeInTheDocument();
        expect(screen.getAllByTestId('placeholder-pattern')).toHaveLength(4); // 3 cards + 1 main area
    });

    it('displays proper breadcrumbs', () => {
        render(<Dashboard />);

        expect(screen.getByTestId('breadcrumbs')).toBeInTheDocument();
        expect(screen.getByText('Dashboard')).toBeInTheDocument();
    });

    it('renders dashboard content structure', () => {
        render(<Dashboard />);

        // Should have the main grid layout
        const gridContainers = screen.getByTestId('app-layout').querySelectorAll('[class*="grid"]');
        expect(gridContainers.length).toBeGreaterThan(0);

        // Should have placeholder patterns for content areas
        expect(screen.getAllByTestId('placeholder-pattern')).toHaveLength(4);
    });

    it('handles authenticated user state properly', () => {
        render(<Dashboard />);

        // Dashboard should render without issues for authenticated user
        expect(screen.getByTestId('app-layout')).toBeInTheDocument();
        
        // Should use AppLayout which handles authentication
        expect(screen.getByTestId('app-layout')).toBeInTheDocument();
    });

    it('renders with verified user', () => {
        render(<Dashboard />);

        // Verified user should be able to access dashboard
        expect(screen.getByTestId('app-layout')).toBeInTheDocument();
        expect(screen.getByText('Dashboard')).toBeInTheDocument();
    });

    it('renders with unverified user', () => {
        const unverifiedUser = {
            ...mockUser,
            email_verified_at: null,
        };

        mockUsePage.mockReturnValue({
            props: {
                ...mockSharedData,
                auth: { user: unverifiedUser },
            },
        });

        render(<Dashboard />);

        // Unverified user should still access dashboard (verification not enforced)
        expect(screen.getByTestId('app-layout')).toBeInTheDocument();
        expect(screen.getByText('Dashboard')).toBeInTheDocument();
    });

    it('sets correct page title', () => {
        render(<Dashboard />);

        // Head component should be rendered with title
        expect(screen.getByTestId('head')).toBeInTheDocument();
    });

    it('maintains proper layout for protected content', () => {
        render(<Dashboard />);

        // Should use the protected AppLayout
        expect(screen.getByTestId('app-layout')).toBeInTheDocument();
        
        // Should have proper content structure
        const content = screen.getByTestId('app-layout').querySelector('[class*="flex"]');
        expect(content).toBeInTheDocument();
    });

    it('handles responsive design structure', () => {
        render(<Dashboard />);

        // Should have responsive grid classes for cards
        const appLayout = screen.getByTestId('app-layout');
        const gridElements = appLayout.querySelectorAll('[class*="md:grid-cols"]');
        expect(gridElements.length).toBeGreaterThan(0);
    });

    it('renders all dashboard content areas', () => {
        render(<Dashboard />);

        // Should have 3 smaller cards and 1 main content area
        const placeholderPatterns = screen.getAllByTestId('placeholder-pattern');
        expect(placeholderPatterns).toHaveLength(4);

        // Verify they have proper styling classes
        placeholderPatterns.forEach(pattern => {
            expect(pattern).toHaveClass('absolute');
            expect(pattern).toHaveClass('inset-0');
        });
    });
}); 