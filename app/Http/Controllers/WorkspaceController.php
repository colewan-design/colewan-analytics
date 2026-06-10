<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsClick;
use App\Models\AnalyticsFeedback;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSite;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkspaceController extends Controller
{
    public function show(string $trackingId)
    {
        $site = AnalyticsSite::where('tracking_id', $trackingId)->firstOrFail();

        $totalPv = AnalyticsPageview::where('tracking_id', $trackingId)->count();
        $totalCl = AnalyticsClick::where('tracking_id', $trackingId)->count();
        $totalFb = AnalyticsFeedback::where('tracking_id', $trackingId)->count();

        return Inertia::render('Workspace/Index', [
            'site'    => array_merge($site->toArray(), [
                'created_at_human' => $site->created_at->diffForHumans(),
            ]),
            'totalPv' => $totalPv,
            'totalCl' => $totalCl,
            'totalFb' => $totalFb,
        ]);
    }

    public function update(Request $request, string $trackingId)
    {
        $site = AnalyticsSite::where('tracking_id', $trackingId)->firstOrFail();

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
        ]);

        $site->update($validated);

        return back()->with('success', 'Workspace settings updated.');
    }
}
