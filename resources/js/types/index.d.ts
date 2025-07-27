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
