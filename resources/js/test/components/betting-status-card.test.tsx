import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import { BettingStatusCard } from '@/components/betting-status-card';
import type { BettingStatusData } from '@/types';

describe('BettingStatusCard', () => {
  it('renders with default placeholder data', () => {
    render(<BettingStatusCard />);
    
    expect(screen.getByText('Betting Status')).toBeInTheDocument();
    expect(screen.getByText('Ready to Start')).toBeInTheDocument();
    expect(screen.getByText('$10.00')).toBeInTheDocument();
    expect(screen.getByText('$0.00')).toBeInTheDocument();
    expect(screen.getByText('00:00:00')).toBeInTheDocument();
    expect(screen.getByText('0')).toBeInTheDocument();
  });

  it('renders with custom data', () => {
    const customData: BettingStatusData = {
      sessionStatus: 'Active',
      currentBet: 25,
      totalPL: 150,
      sessionDuration: '01:23:45',
      spinCount: 12
    };

    render(<BettingStatusCard data={customData} />);
    
    expect(screen.getByText('Active')).toBeInTheDocument();
    expect(screen.getByText('$25.00')).toBeInTheDocument();
    expect(screen.getByText('+$150.00')).toBeInTheDocument();
    expect(screen.getByText('01:23:45')).toBeInTheDocument();
    expect(screen.getByText('12')).toBeInTheDocument();
  });

  it('displays positive P/L with green color and plus sign', () => {
    const positiveData: BettingStatusData = {
      sessionStatus: 'Active',
      currentBet: 10,
      totalPL: 75.50,
      sessionDuration: '00:30:00',
      spinCount: 5
    };

    render(<BettingStatusCard data={positiveData} />);
    
    const plElement = screen.getByText('+$75.50');
    expect(plElement).toBeInTheDocument();
    expect(plElement).toHaveClass('text-green-600');
  });

  it('displays negative P/L with red color and minus sign', () => {
    const negativeData: BettingStatusData = {
      sessionStatus: 'Active',
      currentBet: 10,
      totalPL: -50.25,
      sessionDuration: '00:45:00',
      spinCount: 8
    };

    render(<BettingStatusCard data={negativeData} />);
    
    const plElement = screen.getByText('-$50.25');
    expect(plElement).toBeInTheDocument();
    expect(plElement).toHaveClass('text-red-600');
  });

  it('displays zero P/L without sign and neutral color', () => {
    const zeroData: BettingStatusData = {
      sessionStatus: 'Active',
      currentBet: 10,
      totalPL: 0,
      sessionDuration: '00:15:00',
      spinCount: 3
    };

    render(<BettingStatusCard data={zeroData} />);
    
    const plElement = screen.getByText('$0.00');
    expect(plElement).toBeInTheDocument();
    expect(plElement).toHaveClass('text-muted-foreground');
  });

  it('formats currency correctly', () => {
    const dataWithDecimals: BettingStatusData = {
      sessionStatus: 'Active',
      currentBet: 12.75,
      totalPL: 99.99,
      sessionDuration: '00:20:00',
      spinCount: 4
    };

    render(<BettingStatusCard data={dataWithDecimals} />);
    
    expect(screen.getByText('$12.75')).toBeInTheDocument();
    expect(screen.getByText('+$99.99')).toBeInTheDocument();
  });

  it('displays all required labels', () => {
    render(<BettingStatusCard />);
    
    expect(screen.getByText('Status:')).toBeInTheDocument();
    expect(screen.getByText('Current Bet:')).toBeInTheDocument();
    expect(screen.getByText('Total P/L:')).toBeInTheDocument();
    expect(screen.getByText('Duration:')).toBeInTheDocument();
    expect(screen.getByText('Spins:')).toBeInTheDocument();
  });

  it('has correct card structure', () => {
    render(<BettingStatusCard />);
    
    const card = screen.getByText('Betting Status').closest('.relative');
    expect(card).toBeInTheDocument();
    expect(card).toHaveClass('relative');
  });
}); 