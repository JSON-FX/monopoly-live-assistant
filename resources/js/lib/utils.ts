import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

/**
 * Formats a timestamp for display in the SpinHistoryCard
 * @param timestamp - ISO string or timestamp to format
 * @param format - Format type for display
 * @returns Formatted timestamp string
 */
export function formatTimestamp(timestamp: string | Date, format: 'relative' | 'time' | 'datetime' = 'time'): string {
  const date = typeof timestamp === 'string' ? new Date(timestamp) : timestamp;
  
  switch (format) {
    case 'relative':
      return formatRelativeTime(date);
    case 'time':
      return date.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: false 
      });
    case 'datetime':
      return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
      });
    default:
      return date.toLocaleTimeString();
  }
}

/**
 * Formats relative time (e.g., "2 minutes ago", "just now")
 * @param date - Date to format relative to now
 * @returns Relative time string
 */
function formatRelativeTime(date: Date): string {
  const now = new Date();
  const diffInMs = now.getTime() - date.getTime();
  const diffInMins = Math.floor(diffInMs / (1000 * 60));
  
  if (diffInMins < 1) return 'Just now';
  if (diffInMins < 60) return `${diffInMins}m ago`;
  
  const diffInHours = Math.floor(diffInMins / 60);
  if (diffInHours < 24) return `${diffInHours}h ago`;
  
  const diffInDays = Math.floor(diffInHours / 24);
  return `${diffInDays}d ago`;
}

/**
 * Generates a mock timestamp for development/testing
 * @param minutesAgo - How many minutes ago the timestamp should be
 * @returns ISO timestamp string
 */
export function generateMockTimestamp(minutesAgo: number = 0): string {
  const now = new Date();
  const timestamp = new Date(now.getTime() - (minutesAgo * 60 * 1000));
  return timestamp.toISOString();
}

/**
 * Formats a number as USD currency
 * @param amount - The amount to format
 * @returns Formatted currency string
 */
export function formatCurrency(amount: number): string {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
}

/**
 * Formats profit/loss amounts with appropriate sign and color
 * @param amount - The P/L amount to format
 * @returns Object with formatted string and CSS color classes
 */
export function formatProfitLoss(amount: number): { 
  formatted: string; 
  colorClass: string; 
} {
  const formatted = formatCurrency(Math.abs(amount));
  let displayFormatted: string;
  let colorClass: string;

  if (amount > 0) {
    displayFormatted = `+${formatted}`;
    colorClass = 'text-green-600 dark:text-green-400';
  } else if (amount < 0) {
    displayFormatted = `-${formatted}`;
    colorClass = 'text-red-600 dark:text-red-400';
  } else {
    displayFormatted = formatted;
    colorClass = 'text-muted-foreground';
  }

  return {
    formatted: displayFormatted,
    colorClass
  };
}
