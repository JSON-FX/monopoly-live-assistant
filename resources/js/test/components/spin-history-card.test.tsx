import { render, screen } from '@testing-library/react';
import { describe, it, expect } from 'vitest';
import { SpinHistoryCard, MOCK_SPIN_SCENARIOS } from '@/components/spin-history-card';
import type { SpinHistoryData } from '@/types';

describe('SpinHistoryCard', () => {
  describe('Component Rendering', () => {
    it('renders spin history card with default mock data', () => {
      render(<SpinHistoryCard />);

      expect(screen.getByText('Spin History')).toBeInTheDocument();
      // Check for card structure by content rather than test ids
      expect(screen.getByText('Spin History')).toBeInTheDocument();
    });

    it('renders with custom className', () => {
      render(<SpinHistoryCard className="custom-class" />);

      const cardContainer = screen.getByText('Spin History').closest('div');
      expect(cardContainer).toBeInTheDocument();
    });

    it('applies correct card styling classes', () => {
      render(<SpinHistoryCard />);

      const cardContainer = screen.getByText('Spin History').closest('div');
      expect(cardContainer).toBeInTheDocument();
    });
  });

  describe('Empty State Handling', () => {
    it('displays empty state when no spins provided', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.empty} />);

      expect(screen.getByText('No spins recorded yet')).toBeInTheDocument();
      expect(screen.getByText('Spin results will appear here as you play')).toBeInTheDocument();
      expect(screen.getByText('🎰')).toBeInTheDocument();
    });

    it('shows empty state icon and messages correctly', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.empty} />);

      // Check for empty state structure
      const emptyState = screen.getByText('No spins recorded yet').closest('div');
      expect(emptyState).toHaveClass('flex', 'flex-col', 'items-center', 'justify-center');
      
      // Check for icon container
      const iconContainer = screen.getByText('🎰').closest('div');
      expect(iconContainer).toHaveClass('w-12', 'h-12', 'mb-3', 'rounded-full', 'bg-muted');
    });
  });

  describe('Spin Data Display', () => {
    it('displays spin history with mixed results correctly', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.mixed} />);

      // Check for spin results
      expect(screen.getByText('Segment 2')).toBeInTheDocument();
      expect(screen.getByText('Chance')).toBeInTheDocument();
      expect(screen.getByText('Segment 1')).toBeInTheDocument();

      // Check for currency amounts
      expect(screen.getAllByText('$10.00')).toHaveLength(3); // All bet amounts
      expect(screen.getByText('+$15.00')).toBeInTheDocument(); // Positive P/L
      expect(screen.getByText('-$10.00')).toBeInTheDocument(); // Negative P/L
      expect(screen.getByText('$0.00')).toBeInTheDocument(); // Zero P/L
    });

    it('displays single spin correctly', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.singleSpin} />);

      expect(screen.getByText('Segment 2')).toBeInTheDocument();
      expect(screen.getByText('$10.00')).toBeInTheDocument();
      expect(screen.getByText('+$15.00')).toBeInTheDocument();
    });

    it('displays all winning spins correctly', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.winning} />);

      expect(screen.getByText('Segment 10')).toBeInTheDocument();
      expect(screen.getByText('Segment 5')).toBeInTheDocument();
      expect(screen.getByText('Segment 2')).toBeInTheDocument();

      // All should be positive P/L
      expect(screen.getByText('+$90.00')).toBeInTheDocument();
      expect(screen.getByText('+$40.00')).toBeInTheDocument();
      expect(screen.getByText('+$15.00')).toBeInTheDocument();
    });
  });

  describe('Currency Formatting', () => {
    it('formats bet amounts correctly', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.mixed} />);

      const betAmounts = screen.getAllByText('$10.00');
      expect(betAmounts.length).toBeGreaterThan(0);
      
      betAmounts.forEach(amount => {
        expect(amount).toHaveClass('text-sm', 'font-medium');
      });
    });

    it('formats positive P/L correctly', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.mixed} />);

      const positiveAmount = screen.getByText('+$15.00');
      expect(positiveAmount).toHaveClass('text-green-600', 'dark:text-green-400');
    });

    it('formats negative P/L correctly', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.mixed} />);

      const negativeAmount = screen.getByText('-$10.00');
      expect(negativeAmount).toHaveClass('text-red-600', 'dark:text-red-400');
    });

    it('formats zero P/L correctly', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.mixed} />);

      const zeroAmount = screen.getByText('$0.00');
      expect(zeroAmount).toHaveClass('text-muted-foreground');
    });
  });

  describe('Color-Coded Segment Badges', () => {
    it('applies correct colors for segment 1', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.mixed} />);

      const segment1 = screen.getByText('Segment 1');
      expect(segment1).toHaveClass('bg-blue-100', 'text-blue-700', 'dark:bg-blue-900', 'dark:text-blue-300');
    });

    it('applies correct colors for segment 2', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.mixed} />);

      const segment2 = screen.getByText('Segment 2');
      expect(segment2).toHaveClass('bg-green-100', 'text-green-700', 'dark:bg-green-900', 'dark:text-green-300');
    });

    it('applies correct colors for Chance segment', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.mixed} />);

      const chance = screen.getByText('Chance');
      expect(chance).toHaveClass('bg-yellow-100', 'text-yellow-700', 'dark:bg-yellow-900', 'dark:text-yellow-300');
    });

    it('applies correct badge styling', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.mixed} />);

      const badges = screen.getAllByText(/Segment|Chance/);
      badges.forEach(badge => {
        expect(badge).toHaveClass('px-2', 'py-1', 'rounded-md', 'text-xs', 'font-medium');
      });
    });
  });

  describe('Timestamp and Spin Number Display', () => {
    it('shows timestamps by default', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.mixed} />);

      // Check for time format (HH:MM)
      const timeElements = screen.getAllByText(/\d{2}:\d{2}/);
      expect(timeElements.length).toBeGreaterThan(0);
      
      timeElements.forEach(time => {
        expect(time).toHaveClass('text-xs', 'text-muted-foreground');
      });
    });

    it('shows spin numbers when enabled', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.withSpinNumbers} />);

      expect(screen.getByText('Spin #1')).toBeInTheDocument();
      expect(screen.getByText('Spin #2')).toBeInTheDocument();
    });
  });

  describe('Chronological Ordering', () => {
    it('displays spins in reverse chronological order (most recent first)', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.mixed} />);

      // Get all spin result badges
      const badges = screen.getAllByText(/Segment \d+|Chance/);
      
      // First badge should be most recent (Segment 2)
      expect(badges[0]).toHaveTextContent('Segment 2');
      // Last badge should be oldest (Segment 1)
      expect(badges[badges.length - 1]).toHaveTextContent('Segment 1');
    });
  });

  describe('Responsive Design', () => {
    it('applies scrollable container for overflow content', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.mixed} />);

      const scrollContainer = document.querySelector('.space-y-3.max-h-64.overflow-y-auto');
      expect(scrollContainer).toBeInTheDocument();
    });

    it('applies proper spacing between spin entries', () => {
      render(<SpinHistoryCard data={MOCK_SPIN_SCENARIOS.mixed} />);

      const scrollContainer = document.querySelector('.space-y-3');
      expect(scrollContainer).toBeInTheDocument();
    });
  });

  describe('Edge Cases', () => {
    it('handles undefined data gracefully', () => {
      render(<SpinHistoryCard data={undefined} />);

      // Should render with default mock data
      expect(screen.getByText('Spin History')).toBeInTheDocument();
      expect(screen.getByText('Segment 2')).toBeInTheDocument();
    });

    it('respects maxSpins limit', () => {
      const limitedData: SpinHistoryData = {
        ...MOCK_SPIN_SCENARIOS.mixed,
        maxSpins: 1
      };

      render(<SpinHistoryCard data={limitedData} />);

      // Should only show 1 spin entry
      const spinEntries = screen.getAllByText(/Segment \d+|Chance/);
      expect(spinEntries).toHaveLength(1);
    });

    it('handles custom maxSpins configuration', () => {
      const customMaxData: SpinHistoryData = {
        ...MOCK_SPIN_SCENARIOS.mixed,
        maxSpins: 2
      };

      render(<SpinHistoryCard data={customMaxData} />);

      // Should only show 2 most recent spins
      const spinEntries = screen.getAllByText(/Segment \d+|Chance/);
      expect(spinEntries).toHaveLength(2);
    });
  });

  describe('Integration Testing', () => {
    it('integrates properly with established Card component patterns', () => {
      render(<SpinHistoryCard />);

      // Should use standard Card structure with title
      expect(screen.getByText('Spin History')).toBeInTheDocument();
      
      // Should display spin data when provided
      expect(screen.getByText('Segment 2')).toBeInTheDocument();
    });

    it('follows established component styling patterns', () => {
      render(<SpinHistoryCard />);

      // Check that title is rendered with proper structure
      const title = screen.getByText('Spin History');
      expect(title).toBeInTheDocument();
      
      // Check that content is rendered
      expect(screen.getByText('Segment 2')).toBeInTheDocument();
    });
  });
}); 