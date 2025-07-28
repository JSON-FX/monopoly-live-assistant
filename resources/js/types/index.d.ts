import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

// Monopoly Live game types
/** Represents the possible segments on a Monopoly Live wheel */
export type MonopolyLiveSegment = '1' | '2' | '5' | '10' | 'Chance' | '4 Rolls';

/** 
 * Interface for betting status information displayed in the BettingStatusCard
 * Used to track current session's betting state and performance
 */
export interface BettingStatusData {
    /** Current status of the betting session */
    sessionStatus: string;
    /** Current bet amount in USD */
    currentBet: number;
    /** Total profit/loss for the session */
    totalPL: number;
    /** Duration of the current session in HH:MM:SS format */
    sessionDuration: string;
    /** Total number of spins in the current session */
    spinCount: number;
}

export interface Session {
    id: number;
    userId: number;
    startTime: string;
    endTime: string | null;
}

export interface Spin {
    id: number;
    sessionId: number;
    result: string;
    betAmount: number;
    pl: number;
}

/**
 * Extended Spin interface for frontend display with additional UI-specific properties
 * Used by SpinHistoryCard to display spin information with timestamps and formatting
 */
export interface SpinHistoryItem extends Spin {
    /** Timestamp when the spin occurred (ISO string or display format) */
    timestamp: string;
    /** Formatted result for display (e.g., "Segment 1", "Chance") */
    displayResult: string;
    /** Spin sequence number within the session */
    spinNumber: number;
}

/**
 * Interface for SpinHistoryCard component props
 * Handles both populated and empty state scenarios
 */
export interface SpinHistoryData {
    /** Array of spin history items to display */
    spins: SpinHistoryItem[];
    /** Maximum number of spins to display in the history */
    maxSpins?: number;
    /** Whether to show spin numbers instead of timestamps */
    showSpinNumbers?: boolean;
}

/**
 * Utility types for timestamp formatting and empty state handling
 */
export type TimestampFormat = 'relative' | 'time' | 'datetime';

export interface EmptyStateConfig {
    /** Message to display when no spins exist */
    message: string;
    /** Optional description for empty state */
    description?: string;
    /** Whether to show a placeholder icon */
    showIcon?: boolean;
}
