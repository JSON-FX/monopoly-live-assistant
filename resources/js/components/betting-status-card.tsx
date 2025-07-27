import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { BettingStatusData } from '@/types';

interface BettingStatusCardProps {
  data?: BettingStatusData;
}

// Default placeholder data - extracted as constant for reusability
const DEFAULT_BETTING_STATUS: BettingStatusData = {
  sessionStatus: 'Ready to Start',
  currentBet: 10,
  totalPL: 0,
  sessionDuration: '00:00:00',
  spinCount: 0
};

export function BettingStatusCard({ data }: BettingStatusCardProps) {
  const statusData = data || DEFAULT_BETTING_STATUS;

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount);
  };

  const formatPL = (amount: number) => {
    const formatted = formatCurrency(Math.abs(amount));
    if (amount > 0) return `+${formatted}`;
    if (amount < 0) return `-${formatted}`;
    return formatted;
  };

  const getPLColor = (amount: number) => {
    if (amount > 0) return 'text-green-600 dark:text-green-400';
    if (amount < 0) return 'text-red-600 dark:text-red-400';
    return 'text-muted-foreground';
  };

  return (
    <Card className="relative">
      <CardHeader>
        <CardTitle>Betting Status</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-3">
          <div className="flex justify-between items-center">
            <span className="text-sm text-muted-foreground">Status:</span>
            <span className="text-sm font-medium">{statusData.sessionStatus}</span>
          </div>
          
          <div className="flex justify-between items-center">
            <span className="text-sm text-muted-foreground">Current Bet:</span>
            <span className="text-sm font-medium">{formatCurrency(statusData.currentBet)}</span>
          </div>
          
          <div className="flex justify-between items-center">
            <span className="text-sm text-muted-foreground">Total P/L:</span>
            <span className={`text-sm font-medium ${getPLColor(statusData.totalPL)}`}>
              {formatPL(statusData.totalPL)}
            </span>
          </div>
          
          <div className="flex justify-between items-center">
            <span className="text-sm text-muted-foreground">Duration:</span>
            <span className="text-sm font-medium">{statusData.sessionDuration}</span>
          </div>
          
          <div className="flex justify-between items-center">
            <span className="text-sm text-muted-foreground">Spins:</span>
            <span className="text-sm font-medium">{statusData.spinCount}</span>
          </div>
        </div>
      </CardContent>
    </Card>
  );
} 