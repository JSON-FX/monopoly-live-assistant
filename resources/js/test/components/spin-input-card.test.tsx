import { describe, it, expect, vi, afterEach } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { SpinInputCard } from '@/components/spin-input-card';
import type { MonopolyLiveSegment } from '@/types';

// Mock console.log to test logging functionality
const mockConsoleLog = vi.spyOn(console, 'log').mockImplementation(() => {});

describe('SpinInputCard', () => {
  afterEach(() => {
    mockConsoleLog.mockClear();
  });

  it('renders all monopoly live segments', () => {
    render(<SpinInputCard />);
    
    expect(screen.getByText('Record Spin Result')).toBeInTheDocument();
    expect(screen.getByText('Click the segment where the wheel landed:')).toBeInTheDocument();
    
    // Check all segment buttons are present
    expect(screen.getByRole('button', { name: '1' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: '2' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: '5' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: '10' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Chance' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: '4 Rolls' })).toBeInTheDocument();
  });

  it('calls onSegmentClick when a segment button is clicked', () => {
    const mockOnSegmentClick = vi.fn();
    render(<SpinInputCard onSegmentClick={mockOnSegmentClick} />);
    
    const segment1Button = screen.getByRole('button', { name: '1' });
    fireEvent.click(segment1Button);
    
    expect(mockOnSegmentClick).toHaveBeenCalledWith('1');
    expect(mockOnSegmentClick).toHaveBeenCalledTimes(1);
  });

  it('logs segment clicks to console', () => {
    render(<SpinInputCard />);
    
    const chanceButton = screen.getByRole('button', { name: 'Chance' });
    fireEvent.click(chanceButton);
    
    expect(mockConsoleLog).toHaveBeenCalledWith('Segment clicked: Chance');
  });

  it('calls both console.log and onSegmentClick when provided', () => {
    const mockOnSegmentClick = vi.fn();
    render(<SpinInputCard onSegmentClick={mockOnSegmentClick} />);
    
    const rollsButton = screen.getByRole('button', { name: '4 Rolls' });
    fireEvent.click(rollsButton);
    
    expect(mockConsoleLog).toHaveBeenCalledWith('Segment clicked: 4 Rolls');
    expect(mockOnSegmentClick).toHaveBeenCalledWith('4 Rolls');
  });

  it('disables all buttons when disabled prop is true', () => {
    render(<SpinInputCard disabled={true} />);
    
    const buttons = screen.getAllByRole('button');
    buttons.forEach(button => {
      expect(button).toBeDisabled();
    });
  });

  it('enables all buttons when disabled prop is false', () => {
    render(<SpinInputCard disabled={false} />);
    
    const buttons = screen.getAllByRole('button');
    buttons.forEach(button => {
      expect(button).not.toBeDisabled();
    });
  });

  it('enables buttons by default when no disabled prop is provided', () => {
    render(<SpinInputCard />);
    
    const buttons = screen.getAllByRole('button');
    buttons.forEach(button => {
      expect(button).not.toBeDisabled();
    });
  });

  it('does not call onSegmentClick when buttons are disabled', () => {
    const mockOnSegmentClick = vi.fn();
    render(<SpinInputCard onSegmentClick={mockOnSegmentClick} disabled={true} />);
    
    const segment5Button = screen.getByRole('button', { name: '5' });
    fireEvent.click(segment5Button);
    
    // Should not be called because button is disabled
    expect(mockOnSegmentClick).not.toHaveBeenCalled();
  });

  it('has correct button styling for number segments', () => {
    render(<SpinInputCard />);
    
    const segment1Button = screen.getByRole('button', { name: '1' });
    const segment2Button = screen.getByRole('button', { name: '2' });
    const segment5Button = screen.getByRole('button', { name: '5' });
    const segment10Button = screen.getByRole('button', { name: '10' });
    
    expect(segment1Button).toHaveClass('bg-blue-600');
    expect(segment2Button).toHaveClass('bg-green-600');
    expect(segment5Button).toHaveClass('bg-purple-600');
    expect(segment10Button).toHaveClass('bg-orange-600');
  });

  it('has correct layout structure', () => {
    render(<SpinInputCard />);
    
    // Check that number segments are in a 2x2 grid
    const gridContainer = screen.getByRole('button', { name: '1' }).closest('.grid-cols-2');
    expect(gridContainer).toBeInTheDocument();
    
    // Check that special segments (Chance, 4 Rolls) are full width
    const chanceButton = screen.getByRole('button', { name: 'Chance' });
    const rollsButton = screen.getByRole('button', { name: '4 Rolls' });
    expect(chanceButton).toHaveClass('w-full');
    expect(rollsButton).toHaveClass('w-full');
  });

  it('tests all segment types for correct callbacks', () => {
    const mockOnSegmentClick = vi.fn();
    render(<SpinInputCard onSegmentClick={mockOnSegmentClick} />);
    
    const segments: MonopolyLiveSegment[] = ['1', '2', '5', '10', 'Chance', '4 Rolls'];
    
    segments.forEach(segment => {
      const button = screen.getByRole('button', { name: segment });
      fireEvent.click(button);
      expect(mockOnSegmentClick).toHaveBeenCalledWith(segment);
    });
    
    expect(mockOnSegmentClick).toHaveBeenCalledTimes(6);
  });

  it('has accessibility attributes', () => {
    render(<SpinInputCard />);
    
    const buttons = screen.getAllByRole('button');
    expect(buttons).toHaveLength(6);
    
    // Check that all buttons are accessible and visible
    buttons.forEach(button => {
      expect(button).toBeVisible();
      expect(button).toBeEnabled(); // by default
    });
  });
}); 