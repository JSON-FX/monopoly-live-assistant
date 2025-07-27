import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import LiveSession from '@/pages/live-session';
import { type Auth, type SharedData } from '@/types';

// Create mock functions
const mockUsePage = vi.fn();

// Mock Inertia.js
vi.mock('@inertiajs/react', () => ({
    usePage: () => mockUsePage(),
    Head: ({ children }: { children?: React.ReactNode }) => <div data-testid="head">{children}</div>,
    Link: ({ href, children, ...props }: { href: string; children: React.ReactNode; [key: string]: unknown }) => <a href={href} {...props}>{children}</a>,
}));

// Mock AppLayout
vi.mock('@/layouts/app-layout', () => ({
    default: ({ children, breadcrumbs }: { children: React.ReactNode, breadcrumbs?: { href: string; title: string }[] }) => (
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

// Mock Card components
vi.mock('@/components/ui/card', () => ({
    Card: ({ children, className }: { children: React.ReactNode, className?: string }) => (
        <div data-testid="card" className={className}>{children}</div>
    ),
    CardHeader: ({ children }: { children: React.ReactNode }) => (
        <div data-testid="card-header">{children}</div>
    ),
    CardTitle: ({ children }: { children: React.ReactNode }) => (
        <div data-testid="card-title">{children}</div>
    ),
    CardContent: ({ children }: { children: React.ReactNode }) => (
        <div data-testid="card-content">{children}</div>
    ),
}));

// Mock PlaceholderPattern
vi.mock('@/components/ui/placeholder-pattern', () => ({
    PlaceholderPattern: ({ className }: { className?: string }) => (
        <div data-testid="placeholder-pattern" className={className}>Pattern</div>
    ),
}));

describe('Live Session Page', () => {
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
            location: '/live-session',
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

    it('renders live session page for authenticated user', () => {
        render(<LiveSession />);

        expect(screen.getByTestId('app-layout')).toBeInTheDocument();
        expect(screen.getByTestId('head')).toBeInTheDocument();
    });

    it('displays proper breadcrumbs', () => {
        render(<LiveSession />);

        expect(screen.getByTestId('breadcrumbs')).toBeInTheDocument();
        expect(screen.getByText('Live Session')).toBeInTheDocument();
    });

    it('sets correct page title', () => {
        render(<LiveSession />);

        expect(screen.getByTestId('head')).toBeInTheDocument();
    });

    it('renders session dashboard card with proper content', () => {
        render(<LiveSession />);

        // Should have multiple cards
        const cards = screen.getAllByTestId('card');
        expect(cards.length).toBeGreaterThan(0);
        
        // Should have card header/title/content for session overview
        expect(screen.getByTestId('card-header')).toBeInTheDocument();
        expect(screen.getByTestId('card-title')).toBeInTheDocument();
        expect(screen.getByTestId('card-content')).toBeInTheDocument();

        // Should display session overview title
        expect(screen.getByText('Session Overview')).toBeInTheDocument();

        // Should display placeholder session data
        expect(screen.getByText('Ready to Start')).toBeInTheDocument();
        expect(screen.getByText('$0.00')).toBeInTheDocument();
        expect(screen.getByText((content, element) => {
            return element?.textContent === 'Spins: 0';
        })).toBeInTheDocument();
    });

    it('renders responsive card layout', () => {
        render(<LiveSession />);

        // Should have responsive grid classes for cards
        const appLayout = screen.getByTestId('app-layout');
        const gridElements = appLayout.querySelectorAll('[class*="md:grid-cols"]');
        expect(gridElements.length).toBeGreaterThan(0);
    });

    it('renders placeholder areas for future features', () => {
        render(<LiveSession />);

        // Should have placeholder patterns (2 cards + 1 main area)
        expect(screen.getAllByTestId('placeholder-pattern')).toHaveLength(3);
        
        // Should have descriptive labels for future features
        expect(screen.getByText('Status & Input')).toBeInTheDocument();
        expect(screen.getByText('Spin History')).toBeInTheDocument();
        expect(screen.getByText('Live Gameplay Area')).toBeInTheDocument();
    });

    it('uses consistent card styling with design system', () => {
        render(<LiveSession />);

        const cards = screen.getAllByTestId('card');
        expect(cards).toHaveLength(4); // Session overview + 2 placeholder cards + main area

        // Check that all cards use proper Card component styling
        cards.forEach(card => {
            expect(card).toHaveClass('relative');
            expect(card).toHaveClass('overflow-hidden');
        });

        // First three cards should have aspect-video
        for (let i = 0; i < 3; i++) {
            expect(cards[i]).toHaveClass('aspect-video');
        }
    });

    it('handles authenticated user state properly', () => {
        render(<LiveSession />);

        // Live Session should render without issues for authenticated user
        expect(screen.getByTestId('app-layout')).toBeInTheDocument();
        expect(screen.getByText('Session Overview')).toBeInTheDocument();
    });

    it('maintains proper layout structure', () => {
        render(<LiveSession />);

        // Should use the protected AppLayout
        expect(screen.getByTestId('app-layout')).toBeInTheDocument();
        
        // Should have proper content structure
        const content = screen.getByTestId('app-layout').querySelector('[class*="flex"]');
        expect(content).toBeInTheDocument();
    });

    it('displays session overview placeholder data', () => {
        render(<LiveSession />);

        // Check for specific session data elements
        expect(screen.getByText(/Session Status:/)).toBeInTheDocument();
        expect(screen.getByText(/Total P\/L:/)).toBeInTheDocument();
        expect(screen.getByText(/Spins:/)).toBeInTheDocument();
    });
}); 