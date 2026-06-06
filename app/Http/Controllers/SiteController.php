<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsSite;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'domain' => 'nullable|string|max:255',
        ]);

        $site = AnalyticsSite::create($data);

        return redirect()->route('dashboard.site', $site->tracking_id)
            ->with('success', "Site \"{$site->name}\" created. Your tracking ID is: {$site->tracking_id}");
    }

    public function destroy(AnalyticsSite $site)
    {
        $site->pageviews()->delete();
        $site->clicks()->delete();
        $site->delete();

        return redirect()->route('dashboard.index')->with('success', 'Site deleted.');
    }
}
