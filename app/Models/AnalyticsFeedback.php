<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsFeedback extends Model
{
    public $timestamps = false;
    protected $table   = 'analytics_feedback';
    protected $fillable = [
        'tracking_id', 'session_id', 'page_url', 'rating', 'comment', 'ip', 'created_at',
    ];
    protected $casts = ['created_at' => 'datetime'];
}
