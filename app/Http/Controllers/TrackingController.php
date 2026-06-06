<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsClick;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;

class TrackingController extends Controller
{
    public function pageview(Request $request)
    {
        $data = $request->validate([
            'tracking_id' => 'required|string|size:32',
            'session_id'  => 'required|string|max:64',
            'url'         => 'required|url|max:2000',
            'title'       => 'nullable|string|max:500',
            'referrer'    => 'nullable|url|max:2000',
            'screen_w'    => 'nullable|integer',
            'screen_h'    => 'nullable|integer',
            'language'    => 'nullable|string|max:20',
        ]);

        if (!AnalyticsSite::where('tracking_id', $data['tracking_id'])->exists()) {
            return response()->json(['error' => 'Invalid tracking ID'], 403);
        }

        $ip   = $request->ip();
        $geo  = $this->geoip($ip);
        $ua   = $this->parseUserAgent($request->userAgent());

        AnalyticsPageview::create([
            'tracking_id'     => $data['tracking_id'],
            'session_id'      => $data['session_id'],
            'url'             => $data['url'],
            'title'           => $data['title'] ?? null,
            'referrer'        => $data['referrer'] ?? null,
            'ip'              => $ip,
            'country'         => $geo['country'] ?? null,
            'country_code'    => $geo['country_code'] ?? null,
            'city'            => $geo['city'] ?? null,
            'region'          => $geo['region'] ?? null,
            'browser'         => $ua['browser'],
            'browser_version' => $ua['browser_version'],
            'os'              => $ua['os'],
            'device'          => $ua['device'],
            'screen_width'    => $data['screen_w'] ?? null,
            'screen_height'   => $data['screen_h'] ?? null,
            'language'        => $data['language'] ?? null,
            'created_at'      => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function click(Request $request)
    {
        $data = $request->validate([
            'tracking_id'   => 'required|string|size:32',
            'session_id'    => 'required|string|max:64',
            'page_url'      => 'required|url|max:2000',
            'element_tag'   => 'nullable|string|max:50',
            'element_id'    => 'nullable|string|max:255',
            'element_class' => 'nullable|string|max:500',
            'element_text'  => 'nullable|string|max:500',
            'element_href'  => 'nullable|url|max:2000',
            'x'             => 'nullable|integer',
            'y'             => 'nullable|integer',
        ]);

        if (!AnalyticsSite::where('tracking_id', $data['tracking_id'])->exists()) {
            return response()->json(['error' => 'Invalid tracking ID'], 403);
        }

        AnalyticsClick::create([
            'tracking_id'   => $data['tracking_id'],
            'session_id'    => $data['session_id'],
            'page_url'      => $data['page_url'],
            'element_tag'   => $data['element_tag'] ?? null,
            'element_id'    => $data['element_id'] ?? null,
            'element_class' => $data['element_class'] ?? null,
            'element_text'  => $data['element_text'] ?? null,
            'element_href'  => $data['element_href'] ?? null,
            'x_pos'         => $data['x'] ?? null,
            'y_pos'         => $data['y'] ?? null,
            'created_at'    => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    private function geoip(string $ip): array
    {
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return ['country' => 'Local', 'country_code' => 'LO', 'city' => 'Local', 'region' => ''];
        }

        return Cache::remember("geoip:{$ip}", 86400, function () use ($ip) {
            try {
                $res = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=country,countryCode,city,regionName");
                if ($res->ok()) {
                    $body = $res->json();
                    return [
                        'country'      => $body['country'] ?? null,
                        'country_code' => $body['countryCode'] ?? null,
                        'city'         => $body['city'] ?? null,
                        'region'       => $body['regionName'] ?? null,
                    ];
                }
            } catch (\Throwable) {}
            return [];
        });
    }

    private function parseUserAgent(?string $ua): array
    {
        $agent = new Agent();
        $agent->setUserAgent($ua ?? '');

        $browser = $agent->browser();
        $version = $agent->version($browser);
        $os      = $agent->platform();

        $device = 'Desktop';
        if ($agent->isPhone()) $device = 'Mobile';
        elseif ($agent->isTablet()) $device = 'Tablet';

        return [
            'browser'         => $browser ?: 'Unknown',
            'browser_version' => $version ?: null,
            'os'              => $os ?: 'Unknown',
            'device'          => $device,
        ];
    }
}
