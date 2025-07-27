import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import type { MonopolyLiveSegment } from '@/types';

interface SpinInputCardProps {
  onSegmentClick?: (segment: MonopolyLiveSegment) => void;
  disabled?: boolean;
}

// Segment configuration - extracted for maintainability and consistency
const SEGMENT_CONFIG = {
  segments: ['1', '2', '5', '10', 'Chance', '4 Rolls'] as const,
  colors: {
    '1': 'bg-blue-600 hover:bg-blue-700 text-white',
    '2': 'bg-green-600 hover:bg-green-700 text-white', 
    '5': 'bg-purple-600 hover:bg-purple-700 text-white',
    '10': 'bg-orange-600 hover:bg-orange-700 text-white',
  } as Record<string, string>,
  variants: {
    'Chance': 'secondary' as const,
    '4 Rolls': 'outline' as const,
  } as Record<string, 'default' | 'secondary' | 'outline'>
};

export function SpinInputCard({ onSegmentClick, disabled = false }: SpinInputCardProps) {
  const segments: MonopolyLiveSegment[] = [...SEGMENT_CONFIG.segments];

  const handleSegmentClick = (segment: MonopolyLiveSegment) => {
    console.log(`Segment clicked: ${segment}`);
    onSegmentClick?.(segment);
  };

  const getButtonVariant = (segment: MonopolyLiveSegment) => {
    return SEGMENT_CONFIG.variants[segment] || 'default' as const;
  };

  const getButtonColor = (segment: MonopolyLiveSegment) => {
    return SEGMENT_CONFIG.colors[segment] || '';
  };

  return (
    <Card className="relative">
      <CardHeader>
        <CardTitle>Record Spin Result</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          <p className="text-sm text-muted-foreground">
            Click the segment where the wheel landed:
          </p>
          
          {/* Number segments in a 2x2 grid */}
          <div className="grid grid-cols-2 gap-3">
            {segments.slice(0, 4).map((segment) => (
              <Button
                key={segment}
                variant={getButtonVariant(segment)}
                size="lg"
                className={`h-12 font-semibold ${getButtonColor(segment)}`}
                onClick={() => handleSegmentClick(segment)}
                disabled={disabled}
              >
                {segment}
              </Button>
            ))}
          </div>
          
          {/* Special segments in full width */}
          <div className="space-y-2">
            {segments.slice(4).map((segment) => (
              <Button
                key={segment}
                variant={getButtonVariant(segment)}
                size="lg"
                className="w-full h-12 font-semibold"
                onClick={() => handleSegmentClick(segment)}
                disabled={disabled}
              >
                {segment}
              </Button>
            ))}
          </div>
        </div>
      </CardContent>
    </Card>
  );
} 