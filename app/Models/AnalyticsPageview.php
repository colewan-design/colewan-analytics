<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsPageview extends Model
{
    public $timestamps = false;
    protected $table = 'analytics_pageviews';
    protected $fillable = [
        'view_id', 'tracking_id', 'session_id', 'url', 'title', 'referrer',
        'ip', 'country', 'country_code', 'city', 'region',
        'browser', 'browser_version', 'os', 'device',
        'screen_width', 'screen_height', 'language', 'duration', 'created_at',
    ];

    protected $casts = ['created_at' => 'datetime'];
}
