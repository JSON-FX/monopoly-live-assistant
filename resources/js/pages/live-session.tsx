import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import { BettingStatusCard } from '@/components/betting-status-card';
import { SpinInputCard } from '@/components/spin-input-card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type MonopolyLiveSegment } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Live Session',
        href: '/live-session',
    },
];

export default function LiveSession() {
    const handleSegmentClick = (segment: MonopolyLiveSegment) => {
        console.log(`Processing spin result: ${segment}`);
        // Future API integration will happen here
    };

    // Shared session data - eliminates duplication between overview and betting status
    const sessionData = {
        sessionStatus: 'Ready to Start',
        totalPL: 0,
        spinCount: 0,
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Live Session" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    {/* Session Dashboard Card */}
                    <Card className="relative aspect-video overflow-hidden">
                        <CardHeader>
                            <CardTitle>Session Overview</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                <p className="text-sm text-muted-foreground">Session Status: <span className="font-medium">{sessionData.sessionStatus}</span></p>
                                <p className="text-sm text-muted-foreground">Total P/L: <span className="font-medium">${sessionData.totalPL.toFixed(2)}</span></p>
                                <p className="text-sm text-muted-foreground">Spins: <span className="font-medium">{sessionData.spinCount}</span></p>
                            </div>
                        </CardContent>
                    </Card>
                    
                    {/* Betting Status Card */}
                    <div className="aspect-video">
                        <BettingStatusCard />
                    </div>
                    
                    {/* Spin Input Card */}
                    <div className="aspect-video">
                        <SpinInputCard onSegmentClick={handleSegmentClick} />
                    </div>
                </div>
                <Card className="relative min-h-[100vh] flex-1 overflow-hidden md:min-h-min">
                    <div className="absolute inset-0 flex items-center justify-center">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        <span className="relative z-10 text-sm text-muted-foreground">Live Gameplay Area</span>
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
} 