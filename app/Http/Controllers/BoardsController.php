<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsClick;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSite;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BoardsController extends Controller
{
    public function index()
    {
        $sites = AnalyticsSite::latest()->get();

        $stats = [];
        foreach ($sites as $site) {
            $since     = now()->subDays(7);
            $prevSince = now()->subDays(14);
            $tid       = $site->tracking_id;

            $views     = AnalyticsPageview::where('tracking_id', $tid)->where('created_at', '>=', $since)->count();
            $prevViews = AnalyticsPageview::where('tracking_id', $tid)->whereBetween('created_at', [$prevSince, $since])->count();
            $visitors  = AnalyticsPageview::where('tracking_id', $tid)->where('created_at', '>=', $since)->distinct('session_id')->count('session_id');
            $clicks    = AnalyticsClick::where('tracking_id', $tid)->where('created_at', '>=', $since)->count();

            $sparkMap = AnalyticsPageview::where('tracking_id', $tid)
                ->where('created_at', '>=', $since)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as cnt'))
                ->groupBy('date')->get()->pluck('cnt', 'date')->toArray();

            $sparkline = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = now()->subDays($i)->format('Y-m-d');
                $sparkline[] = (int) ($sparkMap[$d] ?? 0);
            }

            $stats[$site->id] = [
                'views'     => $views,
                'visitors'  => $visitors,
                'clicks'    => $clicks,
                'sparkline' => $sparkline,
                'change'    => $prevViews > 0 ? round(($views - $prevViews) / $prevViews * 100) : null,
            ];
        }

        return Inertia::render('Boards/Index', [
            'sites' => $sites,
            'stats' => $stats,
        ]);
    }
}
