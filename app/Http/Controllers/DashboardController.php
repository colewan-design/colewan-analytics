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
        $sites = AnalyticsSite::withCount([
            'pageviews',
            'clicks',
        ])->latest()->get();

        return view('dashboard.index', compact('sites'));
    }

    public function site(Request $request, string $trackingId)
    {
        $site = AnalyticsSite::where('tracking_id', $trackingId)->firstOrFail();

        $range = (int) $request->get('days', 30);
        $since = now()->subDays($range);

        $pv = AnalyticsPageview::where('tracking_id', $trackingId)->where('created_at', '>=', $since);

        $totalViews      = (clone $pv)->count();
        $uniqueVisitors  = (clone $pv)->distinct('session_id')->count('session_id');
        $totalClicks     = AnalyticsClick::where('tracking_id', $trackingId)->where('created_at', '>=', $since)->count();

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

        $chartData = $this->viewsPerDay($trackingId, $range);
        $referrers = $this->topReferrers($trackingId, $since);
        $recentViews = (clone $pv)->latest('created_at')->limit(20)->get();

        return view('dashboard.site', compact(
            'site', 'range', 'totalViews', 'uniqueVisitors', 'totalClicks',
            'topPages', 'topCountries', 'browsers', 'devices', 'os',
            'topClicks', 'chartData', 'referrers', 'recentViews'
        ));
    }

    private function viewsPerDay(string $trackingId, int $days): array
    {
        $rows = AnalyticsPageview::where('tracking_id', $trackingId)
            ->where('created_at', '>=', now()->subDays($days))
            ->select(DB::raw("DATE(created_at) as date"), DB::raw('count(*) as views'))
            ->groupBy('date')->orderBy('date')->get();

        $map = $rows->pluck('views', 'date')->toArray();

        $labels = [];
        $data   = [];
        for ($i = $days; $i >= 0; $i--) {
            $d        = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $data[]   = $map[$d] ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function topReferrers(string $trackingId, $since): \Illuminate\Support\Collection
    {
        return AnalyticsPageview::where('tracking_id', $trackingId)
            ->where('created_at', '>=', $since)
            ->whereNotNull('referrer')
            ->select('referrer', DB::raw('count(*) as cnt'))
            ->groupBy('referrer')->orderByDesc('cnt')->limit(10)->get();
    }
}
