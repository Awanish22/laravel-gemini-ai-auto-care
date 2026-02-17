<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'vehicle_make',
        'vehicle_model',
        'vehicle_year',
        'odometer_reading',
        'ai_analysis',
        'ai_recommendations',
        'severity_level',
        'estimated_cost',
        'status',
        'user_id'
    ];

    protected $casts = [
        'ai_recommendations' => 'array',
        'estimated_cost' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}