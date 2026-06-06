<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsClick extends Model
{
    public $timestamps = false;
    protected $table = 'analytics_clicks';
    protected $fillable = [
        'tracking_id', 'session_id', 'page_url',
        'element_tag', 'element_id', 'element_class', 'element_text', 'element_href',
        'x_pos', 'y_pos', 'created_at',
    ];

    protected $casts = ['created_at' => 'datetime'];
}
