<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsClick;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $sites = AnalyticsSite::withCount(['pageviews', 'clicks'])->latest()->get();

        return view('dashboard.index', compact('sites'));
    }

    public function site(Request $request, string $trackingId)
    {
        $site = AnalyticsSite::where('tracking_id', $trackingId)->firstOrFail();

        $range     = $request->integer('days', 30);
        $since     = now()->subDays($range);
        $prevSince = now()->subDays($range * 2);

        $pv     = AnalyticsPageview::where('tracking_id', $trackingId)->where('created_at', '>=', $since);
        $prevPv = AnalyticsPageview::where('tracking_id', $trackingId)
            ->where('created_at', '>=', $prevSince)
            ->where('created_at', '<', $since);

        $totalViews     = (clone $pv)->count();
        $uniqueVisitors = (clone $pv)->distinct('session_id')->count('session_id');
        $totalClicks    = AnalyticsClick::where('tracking_id', $trackingId)->where('created_at', '>=', $since)->count();
        $avgDuration    = (clone $pv)->whereNotNull('duration')->avg('duration');

        $prevViews       = (clone $prevPv)->count();
        $prevVisitors    = (clone $prevPv)->distinct('session_id')->count('session_id');
        $prevAvgDuration = (clone $prevPv)->whereNotNull('duration')->avg('duration');
        $prevClicks      = AnalyticsClick::where('tracking_id', $trackingId)
            ->where('created_at', '>=', $prevSince)->where('created_at', '<', $since)->count();

        $viewsChange    = $prevViews > 0    ? round(($totalViews - $prevViews) / $prevViews * 100)                         : null;
        $visitorsChange = $prevVisitors > 0 ? round(($uniqueVisitors - $prevVisitors) / $prevVisitors * 100)               : null;
        $durationChange = ($prevAvgDuration && $avgDuration) ? round(($avgDuration - $prevAvgDuration) / $prevAvgDuration * 100) : null;
        $clicksChange   = $prevClicks > 0   ? round(($totalClicks - $prevClicks) / $prevClicks * 100)                     : null;

        // Bounce rate: sessions with only 1 pageview
        $sessionCounts  = (clone $pv)->select('session_id', DB::raw('count(*) as cnt'))->groupBy('session_id')->get();
        $totalSessions  = $sessionCounts->count();
        $bounceRate     = $totalSessions > 0 ? round($sessionCounts->where('cnt', 1)->count() / $totalSessions * 100) : 0;

        $prevSessionCounts = (clone $prevPv)->select('session_id', DB::raw('count(*) as cnt'))->groupBy('session_id')->get();
        $prevTotalSessions = $prevSessionCounts->count();
        $prevBounceRate    = $prevTotalSessions > 0 ? round($prevSessionCounts->where('cnt', 1)->count() / $prevTotalSessions * 100) : 0;
        $bounceChange      = $prevTotalSessions > 0 ? ($bounceRate - $prevBounceRate) : null;

        // Live visitors: unique sessions in last 30 minutes
        $liveVisitors = AnalyticsPageview::where('tracking_id', $trackingId)
            ->where('created_at', '>=', now()->subMinutes(30))
            ->distinct('session_id')->count('session_id');

        $topPages = (clone $pv)->select('url', DB::raw('count(*) as views'))
            ->groupBy('url')->orderByDesc('views')->limit(10)->get();

        $topCountries = (clone $pv)->select('country', 'country_code', DB::raw('count(*) as views'))
            ->whereNotNull('country')->groupBy('country', 'country_code')
            ->orderByDesc('views')->limit(10)->get();

        $browsers = (clone $pv)->select('browser', DB::raw('count(*) as cnt'))
            ->groupBy('browser')->orderByDesc('cnt')->limit(8)->get();

        $devices = (clone $pv)->select('device', DB::raw('count(*) as cnt'))
            ->groupBy('device')->orderByDesc('cnt')->get();

        $os = (clone $pv)->select('os', DB::raw('count(*) as cnt'))
            ->groupBy('os')->orderByDesc('cnt')->limit(8)->get();

        $topClicks = AnalyticsClick::where('tracking_id', $trackingId)
            ->where('created_at', '>=', $since)
            ->select('element_tag', 'element_text', 'element_href', DB::raw('count(*) as cnt'))
            ->groupBy('element_tag', 'element_text', 'element_href')
            ->orderByDesc('cnt')->limit(10)->get();

        $chartData   = $this->viewsPerDay($trackingId, $range);
        $referrers   = $this->topReferrers($trackingId, $since);
        $recentViews = (clone $pv)->latest('created_at')->limit(20)->get();

        return view('dashboard.site', compact(
            'site', 'range',
            'totalViews', 'uniqueVisitors', 'totalClicks', 'avgDuration',
            'viewsChange', 'visitorsChange', 'durationChange', 'clicksChange',
            'bounceRate', 'bounceChange', 'totalSessions', 'liveVisitors',
            'topPages', 'topCountries', 'browsers', 'devices', 'os',
            'topClicks', 'chartData', 'referrers', 'recentViews'
        ));
    }

    private function viewsPerDay(string $trackingId, int $days): array
    {
        $rows = AnalyticsPageview::where('tracking_id', $trackingId)
            ->where('created_at', '>=', now()->subDays($days))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as views'))
            ->groupBy('date')->orderBy('date')->get();

        $map    = $rows->pluck('views', 'date')->toArray();
        $labels = [];
        $data   = [];

        for ($i = $days; $i >= 0; $i--) {
            $d        = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $data[]   = $map[$d] ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function topReferrers(string $trackingId, \Carbon\Carbon $since): \Illuminate\Support\Collection
    {
        return AnalyticsPageview::where('tracking_id', $trackingId)
            ->where('created_at', '>=', $since)
            ->whereNotNull('referrer')
            ->select('referrer', DB::raw('count(*) as cnt'))
            ->groupBy('referrer')->orderByDesc('cnt')->limit(10)->get();
    }
}
