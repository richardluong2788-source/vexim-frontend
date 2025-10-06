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
        'features',
        'max_products',
        'priority_listing',
        'badge_color'
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
    ];

    // Relationships
    public function companies()
    {
        return $this->hasMany(Company::class);
    }
}
