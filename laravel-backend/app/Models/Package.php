<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'duration_months',
        'max_products',
        'max_photos',
        'priority_listing',
        'featured_badge',
        'analytics_access',
        'description',
        'contact_limit',
        'visibility_level'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'priority_listing' => 'boolean',
        'featured_badge' => 'boolean',
        'analytics_access' => 'boolean',
    ];

    // Relationships
    public function companies()
    {
        return $this->hasMany(Company::class);
    }
}
