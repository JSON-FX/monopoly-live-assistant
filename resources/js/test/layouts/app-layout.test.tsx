import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import AppLayout from '@/layouts/app-layout';
import { type Auth, type SharedData } from '@/types';

// Create mock functions
const mockUseForm = vi.fn();
const mockUsePage = vi.fn();

// Mock Inertia.js
vi.mock('@inertiajs/react', () => ({
    useForm: () => mockUseForm(),
    usePage: () => mockUsePage(),
    Head: ({ children }: { children?: React.ReactNode }) => <div data-testid="head">{children}</div>,
    Link: ({ href, children, ...props }: any) => <a href={href} {...props}>{children}</a>,
    router: {
        visit: vi.fn(),
    },
}));

// Mock sidebar components and hooks
vi.mock('@/components/ui/sidebar', () => ({
    Sidebar: ({ children }: { children: React.ReactNode }) => <div data-testid="sidebar">{children}</div>,
    SidebarContent: ({ children }: { children: React.ReactNode }) => <div data-testid="sidebar-content">{children}</div>,
    SidebarFooter: ({ children }: { children: React.ReactNode }) => <div data-testid="sidebar-footer">{children}</div>,
    SidebarHeader: ({ children }: { children: React.ReactNode }) => <div data-testid="sidebar-header">{children}</div>,
    SidebarMenu: ({ children }: { children: React.ReactNode }) => <div data-testid="sidebar-menu">{children}</div>,
    SidebarMenuButton: ({ children, ...props }: any) => <button {...props} data-testid="sidebar-menu-button">{children}</button>,
    SidebarMenuItem: ({ children }: { children: React.ReactNode }) => <div data-testid="sidebar-menu-item">{children}</div>,
    SidebarProvider: ({ children }: { children: React.ReactNode }) => <div data-testid="sidebar-provider">{children}</div>,
    useSidebar: () => ({ state: 'expanded' }),
}));

vi.mock('@/hooks/use-mobile', () => ({
    useIsMobile: () => false,
}));

vi.mock('@/components/ui/dropdown-menu', () => ({
    DropdownMenu: ({ children }: { children: React.ReactNode }) => <div data-testid="dropdown-menu">{children}</div>,
    DropdownMenuContent: ({ children }: { children: React.ReactNode }) => <div data-testid="dropdown-content">{children}</div>,
    DropdownMenuTrigger: ({ children }: { children: React.ReactNode }) => <div data-testid="dropdown-trigger">{children}</div>,
}));

vi.mock('@/components/app-logo', () => ({
    default: () => <div data-testid="app-logo">Logo</div>,
}));

// Mock other components
vi.mock('@/components/breadcrumbs', () => ({
    Breadcrumbs: ({ breadcrumbs }: { breadcrumbs: any[] }) => (
        <div data-testid="breadcrumbs">
            {breadcrumbs?.map(b => <span key={b.href}>{b.title}</span>)}
        </div>
    ),
}));

vi.mock('@/components/nav-main', () => ({
    NavMain: ({ items }: { items: any[] }) => (
        <div data-testid="nav-main">
            {items?.map(item => <div key={item.href}>{item.title}</div>)}
        </div>
    ),
}));

vi.mock('@/components/nav-footer', () => ({
    NavFooter: ({ items }: { items: any[] }) => (
        <div data-testid="nav-footer">
            {items?.map(item => <div key={item.href}>{item.title}</div>)}
        </div>
    ),
}));

vi.mock('@/components/nav-user', () => ({
    NavUser: () => (
        <div data-testid="nav-user">
            <div>Test User</div>
            <div>test@example.com</div>
        </div>
    ),
}));

vi.mock('@/components/user-menu-content', () => ({
    UserMenuContent: () => (
        <div data-testid="user-menu-content">
            <div>Settings</div>
            <div>Log out</div>
        </div>
    ),
}));

vi.mock('@/components/app-shell', () => ({
    AppShell: ({ children }: { children: React.ReactNode }) => (
        <div data-testid="app-shell">{children}</div>
    ),
}));

vi.mock('@/components/app-content', () => ({
    AppContent: ({ children }: { children: React.ReactNode }) => (
        <div data-testid="app-content">{children}</div>
    ),
}));

vi.mock('@/components/app-sidebar-header', () => ({
    AppSidebarHeader: ({ breadcrumbs }: { breadcrumbs?: any[] }) => (
        <div data-testid="app-sidebar-header">
            {breadcrumbs?.map(b => <span key={b.href}>{b.title}</span>)}
        </div>
    ),
}));

describe('AppLayout Authentication Handling', () => {
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
        mockUseForm.mockReturnValue({
            data: {},
            setData: vi.fn(),
            post: vi.fn(),
            processing: false,
            errors: {},
            reset: vi.fn(),
        });
    });

    it('renders app layout with authenticated user', () => {
        render(
            <AppLayout>
                <div data-testid="page-content">Protected Content</div>
            </AppLayout>
        );

        expect(screen.getByTestId('sidebar')).toBeInTheDocument();
        expect(screen.getByTestId('page-content')).toBeInTheDocument();
        expect(screen.getByText('Protected Content')).toBeInTheDocument();
    });

    it('displays user information in navigation', () => {
        render(
            <AppLayout>
                <div>Content</div>
            </AppLayout>
        );

        expect(screen.getByText('Test User')).toBeInTheDocument();
        expect(screen.getByText('test@example.com')).toBeInTheDocument();
    });

    it('shows verified user status', () => {
        render(
            <AppLayout>
                <div>Content</div>
            </AppLayout>
        );

        // User info should be displayed (verified users can access protected content)
        expect(screen.getByText('Test User')).toBeInTheDocument();
    });

    it('handles unverified user state', () => {
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

        render(
            <AppLayout>
                <div>Content</div>
            </AppLayout>
        );

        // Unverified user should still see layout (verification not enforced currently)
        expect(screen.getByText('Test User')).toBeInTheDocument();
        expect(screen.getByText('test@example.com')).toBeInTheDocument();
    });

    it('includes navigation items for authenticated users', () => {
        render(
            <AppLayout>
                <div>Content</div>
            </AppLayout>
        );

        // Check for main navigation items
        expect(screen.getByText('Dashboard')).toBeInTheDocument();
        expect(screen.getByTestId('app-logo')).toBeInTheDocument();
    });

    it('provides user menu with settings and logout options', () => {
        render(
            <AppLayout>
                <div>Content</div>
            </AppLayout>
        );

        // User menu should be present
        expect(screen.getByTestId('dropdown-menu')).toBeInTheDocument();
        expect(screen.getByText('Settings')).toBeInTheDocument();
        expect(screen.getByText('Log out')).toBeInTheDocument();
    });

    it('handles breadcrumbs properly', () => {
        const breadcrumbs = [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Settings', href: '/settings' },
        ];

        render(
            <AppLayout breadcrumbs={breadcrumbs}>
                <div>Content</div>
            </AppLayout>
        );

        expect(screen.getByText('Dashboard')).toBeInTheDocument();
        expect(screen.getByText('Settings')).toBeInTheDocument();
    });

    it('maintains proper layout structure for protected pages', () => {
        render(
            <AppLayout>
                <div data-testid="protected-content">
                    This is protected content that requires authentication
                </div>
            </AppLayout>
        );

        // Verify layout structure
        expect(screen.getByTestId('sidebar')).toBeInTheDocument();
        expect(screen.getByTestId('sidebar-header')).toBeInTheDocument();
        expect(screen.getByTestId('sidebar-content')).toBeInTheDocument();
        expect(screen.getByTestId('sidebar-footer')).toBeInTheDocument();
        expect(screen.getByTestId('protected-content')).toBeInTheDocument();
    });

    it('handles authentication state changes gracefully', () => {
        const { rerender } = render(
            <AppLayout>
                <div>Content</div>
            </AppLayout>
        );

        // Initially authenticated
        expect(screen.getByText('Test User')).toBeInTheDocument();

        // Simulate different user
        const newUser = {
            ...mockUser,
            id: 2,
            name: 'New User',
            email: 'new@example.com',
        };

        mockUsePage.mockReturnValue({
            props: {
                ...mockSharedData,
                auth: { user: newUser },
            },
        });

        rerender(
            <AppLayout>
                <div>Content</div>
            </AppLayout>
        );

        expect(screen.getByText('New User')).toBeInTheDocument();
        expect(screen.getByText('new@example.com')).toBeInTheDocument();
    });
}); 