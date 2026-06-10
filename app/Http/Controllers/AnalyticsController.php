<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsClick;
use App\Models\AnalyticsFeedback;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    private function getSite(string $trackingId): AnalyticsSite
    {
        return AnalyticsSite::where('tracking_id', $trackingId)->firstOrFail();
    }

    public function charts(Request $request, string $trackingId)
    {
        $site  = $this->getSite($trackingId);
        $range = $request->integer('days', 30);
        $since = now()->subDays($range);

        $pv = fn () => AnalyticsPageview::where('tracking_id', $trackingId)->where('created_at', '>=', $since);

        $dayRows = AnalyticsPageview::where('tracking_id', $trackingId)
            ->where('created_at', '>=', $since)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as views'), DB::raw('count(distinct session_id) as sessions'))
            ->groupBy('date')->orderBy('date')->get()->keyBy('date');

        $labels = $viewData = $sessionData = [];
        for ($i = $range; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $labels[]      = now()->subDays($i)->format('M d');
            $viewData[]    = (int) ($dayRows[$d]->views    ?? 0);
            $sessionData[] = (int) ($dayRows[$d]->sessions ?? 0);
        }

        $hourlyRows = AnalyticsPageview::where('tracking_id', $trackingId)
            ->where('created_at', '>=', $since)
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as cnt'))
            ->groupBy('hour')->orderBy('hour')->get()->keyBy('hour');

        $hourLabels = $hourData = [];
        for ($h = 0; $h < 24; $h++) {
            $hourLabels[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
            $hourData[]   = (int) ($hourlyRows[$h]->cnt ?? 0);
        }

        $dowRows = AnalyticsPageview::where('tracking_id', $trackingId)
            ->where('created_at', '>=', $since)
            ->select(DB::raw('DAYOFWEEK(created_at) as dow'), DB::raw('count(*) as cnt'))
            ->groupBy('dow')->orderBy('dow')->get()->keyBy('dow');

        $dowLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $dowData   = [];
        for ($d = 1; $d <= 7; $d++) {
            $dowData[] = (int) ($dowRows[$d]->cnt ?? 0);
        }

        $osList    = ($pv)()->select('os', DB::raw('count(*) as cnt'))->whereNotNull('os')->groupBy('os')->orderByDesc('cnt')->limit(8)->get();
        $languages = ($pv)()->select('language', DB::raw('count(*) as cnt'))->whereNotNull('language')->groupBy('language')->orderByDesc('cnt')->limit(10)->get();
        $screens   = ($pv)()->select('screen_width', 'screen_height', DB::raw('count(*) as cnt'))->whereNotNull('screen_width')->groupBy('screen_width', 'screen_height')->orderByDesc('cnt')->limit(10)->get();

        return Inertia::render('Analytics/Charts', [
            'site'        => $site,
            'range'       => $range,
            'labels'      => $labels,
            'viewData'    => $viewData,
            'sessionData' => $sessionData,
            'hourLabels'  => $hourLabels,
            'hourData'    => $hourData,
            'dowLabels'   => $dowLabels,
            'dowData'     => $dowData,
            'osList'      => $osList,
            'languages'   => $languages,
            'screens'     => $screens,
        ]);
    }

    public function replay(Request $request, string $trackingId)
    {
        $site  = $this->getSite($trackingId);
        $range = $request->integer('days', 7);
        $since = now()->subDays($range);

        $sessions = AnalyticsPageview::where('tracking_id', $trackingId)
            ->where('created_at', '>=', $since)
            ->select([
                'session_id',
                DB::raw('MIN(created_at) as first_seen'),
                DB::raw('MAX(created_at) as last_seen'),
                DB::raw('COUNT(*) as page_count'),
                DB::raw('SUM(COALESCE(duration, 0)) as total_duration'),
                DB::raw('MIN(country) as country'),
                DB::raw('MIN(country_code) as country_code'),
                DB::raw('MIN(browser) as browser'),
                DB::raw('MIN(device) as device'),
                DB::raw('MIN(os) as os'),
            ])
            ->groupBy('session_id')
            ->orderByDesc('first_seen')
            ->paginate(20)
            ->through(fn ($s) => array_merge($s->toArray(), [
                'first_seen_human' => \Carbon\Carbon::parse($s->first_seen)->diffForHumans(),
            ]));

        $sessionIds   = collect($sessions->items())->pluck('session_id')->toArray();
        $sessionPages = AnalyticsPageview::where('tracking_id', $trackingId)
            ->whereIn('session_id', $sessionIds)
            ->select('session_id', 'url', 'title', 'created_at', 'duration')
            ->orderBy('created_at')
            ->get()
            ->groupBy('session_id')
            ->map(fn ($pages) => $pages->map(fn ($p) => [
                'url'      => $p->url,
                'title'    => $p->title,
                'duration' => $p->duration,
                'time'     => \Carbon\Carbon::parse($p->created_at)->format('H:i:s'),
            ]));

        return Inertia::render('Analytics/Replay', [
            'site'         => $site,
            'range'        => $range,
            'sessions'     => $sessions,
            'sessionPages' => $sessionPages,
        ]);
    }

    public function heatmap(Request $request, string $trackingId)
    {
        $site = $this->getSite($trackingId);

        $pages = AnalyticsClick::where('tracking_id', $trackingId)
            ->select('page_url', DB::raw('count(*) as total'))
            ->groupBy('page_url')->orderByDesc('total')->get();

        $selectedPage = $request->input('page', $pages->first()?->page_url);
        $clicks       = [];
        $topElements  = collect();

        if ($selectedPage) {
            $clicks = AnalyticsClick::where('tracking_id', $trackingId)
                ->where('page_url', $selectedPage)
                ->whereNotNull('x_pos')->whereNotNull('y_pos')
                ->select('x_pos', 'y_pos', DB::raw('count(*) as cnt'))
                ->groupBy('x_pos', 'y_pos')->get()->toArray();

            $topElements = AnalyticsClick::where('tracking_id', $trackingId)
                ->where('page_url', $selectedPage)
                ->select('element_tag', 'element_text', 'element_href', DB::raw('count(*) as cnt'))
                ->groupBy('element_tag', 'element_text', 'element_href')
                ->orderByDesc('cnt')->limit(10)->get();
        }

        return Inertia::render('Analytics/Heatmap', [
            'site'         => $site,
            'pages'        => $pages,
            'selectedPage' => $selectedPage,
            'clicks'       => $clicks,
            'topElements'  => $topElements,
        ]);
    }

    public function feedback(Request $request, string $trackingId)
    {
        $site  = $this->getSite($trackingId);
        $range = $request->integer('days', 30);
        $since = now()->subDays($range);

        $stats = AnalyticsFeedback::where('tracking_id', $trackingId)
            ->where('created_at', '>=', $since)
            ->selectRaw('COUNT(*) as total, AVG(rating) as avg_rating,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as positive,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as neutral,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as negative')
            ->first();

        $byPage = AnalyticsFeedback::where('tracking_id', $trackingId)
            ->where('created_at', '>=', $since)
            ->select('page_url', DB::raw('COUNT(*) as cnt'), DB::raw('AVG(rating) as avg'))
            ->groupBy('page_url')->orderByDesc('cnt')->limit(10)->get();

        $recentFeedback = AnalyticsFeedback::where('tracking_id', $trackingId)
            ->where('created_at', '>=', $since)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(fn ($fb) => array_merge($fb->toArray(), [
                'created_at_human' => $fb->created_at->diffForHumans(),
            ]));

        return Inertia::render('Analytics/Feedback', [
            'site'           => $site,
            'range'          => $range,
            'stats'          => $stats,
            'byPage'         => $byPage,
            'recentFeedback' => $recentFeedback,
        ]);
    }

    public function visitors(Request $request, string $trackingId)
    {
        $site  = $this->getSite($trackingId);
        $range = $request->integer('days', 30);
        $since = now()->subDays($range);

        $sessions = AnalyticsPageview::where('tracking_id', $trackingId)
            ->where('created_at', '>=', $since)
            ->select([
                'session_id',
                DB::raw('MIN(created_at) as first_seen'),
                DB::raw('MAX(created_at) as last_seen'),
                DB::raw('COUNT(*) as page_count'),
                DB::raw('SUM(COALESCE(duration, 0)) as total_duration'),
                DB::raw('MIN(country) as country'),
                DB::raw('MIN(country_code) as country_code'),
                DB::raw('MIN(city) as city'),
                DB::raw('MIN(browser) as browser'),
                DB::raw('MIN(device) as device'),
                DB::raw('MIN(os) as os'),
                DB::raw('MIN(language) as language'),
                DB::raw('MIN(screen_width) as screen_width'),
                DB::raw('MIN(screen_height) as screen_height'),
            ])
            ->groupBy('session_id')
            ->orderByDesc('first_seen')
            ->paginate(25)
            ->through(fn ($s) => array_merge($s->toArray(), [
                'first_seen_human' => \Carbon\Carbon::parse($s->first_seen)->diffForHumans(),
            ]));

        return Inertia::render('Analytics/Visitors', [
            'site'     => $site,
            'range'    => $range,
            'sessions' => $sessions,
        ]);
    }

    public function events(Request $request, string $trackingId)
    {
        $site  = $this->getSite($trackingId);
        $range = $request->integer('days', 30);
        $since = now()->subDays($range);

        $q = AnalyticsClick::where('tracking_id', $trackingId)->where('created_at', '>=', $since);

        $currentTag = $request->input('tag');
        if ($request->filled('tag')) {
            $q->where('element_tag', $currentTag);
        }

        $events = (clone $q)->orderByDesc('created_at')->paginate(25)
            ->through(fn ($ev) => array_merge($ev->toArray(), [
                'created_at_human' => \Carbon\Carbon::parse($ev->created_at)->diffForHumans(),
            ]));

        $summary = AnalyticsClick::where('tracking_id', $trackingId)
            ->where('created_at', '>=', $since)
            ->select('element_tag', DB::raw('count(*) as cnt'))
            ->groupBy('element_tag')->orderByDesc('cnt')->get();

        $tags = AnalyticsClick::where('tracking_id', $trackingId)
            ->where('created_at', '>=', $since)
            ->distinct()->pluck('element_tag');

        return Inertia::render('Analytics/Events', [
            'site'       => $site,
            'range'      => $range,
            'events'     => $events,
            'summary'    => $summary,
            'tags'       => $tags,
            'currentTag' => $currentTag,
        ]);
    }

    public function integration(string $trackingId)
    {
        $site = $this->getSite($trackingId);
        return Inertia::render('Analytics/Integration', ['site' => $site]);
    }
}
