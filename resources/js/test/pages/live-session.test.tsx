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

// Mock BettingStatusCard
vi.mock('@/components/betting-status-card', () => ({
    BettingStatusCard: ({ data }: { data?: any }) => (
        <div data-testid="betting-status-card">
            <div data-testid="card-title">Betting Status</div>
            <div>Ready to Start</div>
            <div>$10.00</div>
            <div>$0.00</div>
            <div>00:00:00</div>
            <div>0</div>
        </div>
    ),
}));

// Mock SpinInputCard
vi.mock('@/components/spin-input-card', () => ({
    SpinInputCard: ({ onSegmentClick, disabled }: { onSegmentClick?: (segment: string) => void, disabled?: boolean }) => (
        <div data-testid="spin-input-card">
            <div data-testid="card-title">Record Spin Result</div>
            <div>Click the segment where the wheel landed:</div>
            {['1', '2', '5', '10', 'Chance', '4 Rolls'].map(segment => (
                <button 
                    key={segment} 
                    onClick={() => {
                        console.log(`Segment clicked: ${segment}`);
                        onSegmentClick?.(segment);
                    }}
                    disabled={disabled}
                >
                    {segment}
                </button>
            ))}
        </div>
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
        expect(screen.getAllByTestId('card-title')).toHaveLength(3); // Session Overview + Betting Status + Record Spin Result
        expect(screen.getByTestId('card-content')).toBeInTheDocument();

        // Should display session overview title
        expect(screen.getByText('Session Overview')).toBeInTheDocument();

        // Should display placeholder session data within session overview
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

    it('renders new status and input cards', () => {
        render(<LiveSession />);

        // Should now have betting status card instead of placeholder
        expect(screen.getByTestId('betting-status-card')).toBeInTheDocument();
        expect(screen.getByText('Betting Status')).toBeInTheDocument();
        
        // Should now have spin input card instead of placeholder
        expect(screen.getByTestId('spin-input-card')).toBeInTheDocument();
        expect(screen.getByText('Record Spin Result')).toBeInTheDocument();
        
        // Should still have live gameplay area placeholder
        expect(screen.getByText('Live Gameplay Area')).toBeInTheDocument();
        expect(screen.getAllByTestId('placeholder-pattern')).toHaveLength(1);
    });

    it('uses consistent card styling with design system', () => {
        render(<LiveSession />);

        const cards = screen.getAllByTestId('card');
        expect(cards).toHaveLength(2); // Session overview + main area (betting/input cards use different structure)

        // Check that all cards use proper Card component styling
        cards.forEach(card => {
            expect(card).toHaveClass('relative');
            expect(card).toHaveClass('overflow-hidden');
        });
    });

    it('renders all monopoly live segment buttons', () => {
        render(<LiveSession />);

        // Check all segment buttons are present
        expect(screen.getByRole('button', { name: '1' })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: '2' })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: '5' })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: '10' })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Chance' })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: '4 Rolls' })).toBeInTheDocument();
    });

    it('handles segment button clicks with proper logging', () => {
        const consoleSpy = vi.spyOn(console, 'log').mockImplementation(() => {});
        
        render(<LiveSession />);
        
        const segment1Button = screen.getByRole('button', { name: '1' });
        segment1Button.click();
        
        expect(consoleSpy).toHaveBeenCalledWith('Segment clicked: 1');
        
        consoleSpy.mockRestore();
    });

    it('displays betting status information', () => {
        render(<LiveSession />);

        expect(screen.getByTestId('betting-status-card')).toBeInTheDocument();
        expect(screen.getAllByText('Ready to Start')).toHaveLength(2); // Both in session overview and betting status
        expect(screen.getAllByText('$0.00')).toHaveLength(2); // Both in session overview and betting status
        expect(screen.getByText('$10.00')).toBeInTheDocument(); // Only in betting status
        expect(screen.getByText('00:00:00')).toBeInTheDocument(); // Only in betting status
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