<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsSite;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    public function show(string $trackingId)
    {
        $site = AnalyticsSite::where('tracking_id', $trackingId)->firstOrFail();
        return view('workspace.index', compact('site'));
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
