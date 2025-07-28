import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatTimestamp, generateMockTimestamp, formatCurrency, formatProfitLoss } from '@/lib/utils';
import type { SpinHistoryData, SpinHistoryItem, MonopolyLiveSegment } from '@/types';
import { useMemo } from 'react';

interface SpinHistoryCardProps {
  data?: SpinHistoryData;
  className?: string;
}

// Default configuration - extracted for maintainability
const DEFAULT_CONFIG = {
  maxSpins: 10,
  emptyStateMessage: 'No spins recorded yet',
  emptyStateDescription: 'Spin results will appear here as you play',
} as const;

// Segment color mapping - extracted as constant for performance
const SEGMENT_COLORS = {
  '1': 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
  '2': 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
  '5': 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300',
  '10': 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300',
  'Chance': 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
  '4 Rolls': 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
} as const;

const DEFAULT_SEGMENT_COLOR = 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300' as const;

// Mock data scenarios for development and testing
export const MOCK_SPIN_SCENARIOS = {
  // Empty state scenario
  empty: {
    spins: [],
    maxSpins: DEFAULT_CONFIG.maxSpins,
    showSpinNumbers: false,
  } as SpinHistoryData,

  // Single spin scenario
  singleSpin: {
    spins: [
      {
        id: 1,
        sessionId: 1,
        result: '2',
        betAmount: 10,
        pl: 15,
        timestamp: generateMockTimestamp(1),
        displayResult: 'Segment 2',
        spinNumber: 1,
      },
    ],
    maxSpins: DEFAULT_CONFIG.maxSpins,
    showSpinNumbers: false,
  } as SpinHistoryData,

  // Mixed results scenario (default)
  mixed: {
    spins: [
      {
        id: 3,
        sessionId: 1,
        result: '2',
        betAmount: 10,
        pl: 15,
        timestamp: generateMockTimestamp(2),
        displayResult: 'Segment 2',
        spinNumber: 3,
      },
      {
        id: 2,
        sessionId: 1,
        result: 'Chance',
        betAmount: 10,
        pl: -10,
        timestamp: generateMockTimestamp(5),
        displayResult: 'Chance',
        spinNumber: 2,
      },
      {
        id: 1,
        sessionId: 1,
        result: '1',
        betAmount: 10,
        pl: 0,
        timestamp: generateMockTimestamp(8),
        displayResult: 'Segment 1',
        spinNumber: 1,
      },
    ],
    maxSpins: DEFAULT_CONFIG.maxSpins,
    showSpinNumbers: false,
  } as SpinHistoryData,

  // All winning scenario
  winning: {
    spins: [
      {
        id: 4,
        sessionId: 1,
        result: '10',
        betAmount: 10,
        pl: 90,
        timestamp: generateMockTimestamp(1),
        displayResult: 'Segment 10',
        spinNumber: 4,
      },
      {
        id: 3,
        sessionId: 1,
        result: '5',
        betAmount: 10,
        pl: 40,
        timestamp: generateMockTimestamp(3),
        displayResult: 'Segment 5',
        spinNumber: 3,
      },
      {
        id: 2,
        sessionId: 1,
        result: '2',
        betAmount: 10,
        pl: 15,
        timestamp: generateMockTimestamp(5),
        displayResult: 'Segment 2',
        spinNumber: 2,
      },
    ],
    maxSpins: DEFAULT_CONFIG.maxSpins,
    showSpinNumbers: false,
  } as SpinHistoryData,

  // Show spin numbers instead of timestamps
  withSpinNumbers: {
    spins: [
      {
        id: 2,
        sessionId: 1,
        result: 'Chance',
        betAmount: 10,
        pl: -10,
        timestamp: generateMockTimestamp(2),
        displayResult: 'Chance',
        spinNumber: 2,
      },
      {
        id: 1,
        sessionId: 1,
        result: '1',
        betAmount: 10,
        pl: 0,
        timestamp: generateMockTimestamp(5),
        displayResult: 'Segment 1',
        spinNumber: 1,
      },
    ],
    maxSpins: DEFAULT_CONFIG.maxSpins,
    showSpinNumbers: true,
  } as SpinHistoryData,
};

// Default placeholder data for development
const DEFAULT_SPIN_HISTORY: SpinHistoryData = MOCK_SPIN_SCENARIOS.mixed;

/**
 * Formats display result for spin segments
 * @param result - The raw result value
 * @returns Formatted display string
 */
const formatDisplayResult = (result: string): string => {
  // Handle special cases
  if (result === 'Chance' || result === '4 Rolls') return result;
  // Handle numeric segments
  return `Segment ${result}`;
};

/**
 * Gets the appropriate color classes for a segment badge
 * @param result - The segment result
 * @returns CSS color classes
 */
const getResultBadgeColor = (result: string): string => {
  return SEGMENT_COLORS[result as keyof typeof SEGMENT_COLORS] || DEFAULT_SEGMENT_COLOR;
};

export function SpinHistoryCard({ data, className }: SpinHistoryCardProps) {
  const historyData = data || DEFAULT_SPIN_HISTORY;
  const { spins, maxSpins = DEFAULT_CONFIG.maxSpins, showSpinNumbers = false } = historyData;

  // Console logging only in development mode
  if (process.env.NODE_ENV === 'development') {
    console.log('SpinHistoryCard rendered with data:', {
      spinsCount: spins.length,
      maxSpins,
      showSpinNumbers,
      hasCustomData: !!data
    });
  }

  // Memoize sorted spins for performance - only recalculate when dependencies change
  const sortedSpins = useMemo(() => {
    return [...spins]
      .sort((a, b) => new Date(b.timestamp).getTime() - new Date(a.timestamp).getTime())
      .slice(0, maxSpins);
  }, [spins, maxSpins]);

  // Empty state component with accessibility improvements
  const EmptyState = () => (
    <div 
      className="flex flex-col items-center justify-center py-8 text-center"
      role="status"
      aria-label="No spin history available"
    >
      <div className="w-12 h-12 mb-3 rounded-full bg-muted flex items-center justify-center">
        <span className="text-xl text-muted-foreground" aria-hidden="true">🎰</span>
      </div>
      <p className="text-sm font-medium text-foreground mb-1">
        {DEFAULT_CONFIG.emptyStateMessage}
      </p>
      <p className="text-xs text-muted-foreground">
        {DEFAULT_CONFIG.emptyStateDescription}
      </p>
    </div>
  );

  return (
    <Card className={`relative overflow-hidden ${className || ''}`}>
      <CardHeader>
        <CardTitle>Spin History</CardTitle>
      </CardHeader>
      <CardContent>
        {sortedSpins.length === 0 ? (
          <EmptyState />
        ) : (
          <div 
            className="space-y-3 max-h-64 overflow-y-auto"
            role="list"
            aria-label={`Spin history showing ${sortedSpins.length} recent spins`}
          >
            {sortedSpins.map((spin, index) => {
              const profitLoss = formatProfitLoss(spin.pl);
              
              return (
                <div
                  key={spin.id}
                  role="listitem"
                  className="flex items-center justify-between p-3 rounded-lg border bg-muted/50"
                  aria-label={`Spin ${spin.spinNumber}: ${formatDisplayResult(spin.result)}, bet ${formatCurrency(spin.betAmount)}, ${profitLoss.formatted}`}
                >
                  <div className="flex items-center gap-3">
                    <span
                      className={`px-2 py-1 rounded-md text-xs font-medium ${getResultBadgeColor(spin.result)}`}
                      aria-label={`Result: ${formatDisplayResult(spin.result)}`}
                    >
                      {formatDisplayResult(spin.result)}
                    </span>
                    <div className="flex flex-col">
                      <span className="text-sm font-medium" aria-label={`Bet amount: ${formatCurrency(spin.betAmount)}`}>
                        {formatCurrency(spin.betAmount)}
                      </span>
                      <span className="text-xs text-muted-foreground">
                        {showSpinNumbers 
                          ? `Spin #${spin.spinNumber}` 
                          : formatTimestamp(spin.timestamp, 'time')}
                      </span>
                    </div>
                  </div>
                  <div className="text-right">
                    <div 
                      className={`text-sm font-medium ${profitLoss.colorClass}`}
                      aria-label={`Profit/Loss: ${profitLoss.formatted}`}
                    >
                      {profitLoss.formatted}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </CardContent>
    </Card>
  );
} 