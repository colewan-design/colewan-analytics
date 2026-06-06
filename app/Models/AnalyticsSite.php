<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AnalyticsSite extends Model
{
    protected $table = 'analytics_sites';
    protected $fillable = ['name', 'domain', 'tracking_id'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn($site) => $site->tracking_id = Str::random(32));
    }

    public function pageviews()
    {
        return $this->hasMany(AnalyticsPageview::class, 'tracking_id', 'tracking_id');
    }

    public function clicks()
    {
        return $this->hasMany(AnalyticsClick::class, 'tracking_id', 'tracking_id');
    }
}
