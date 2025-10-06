<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'business_license',
        'tax_code',
        'address',
        'country',
        'phone',
        'email',
        'website',
        'description',
        'logo',
        'verification_status',
        'verification_id',
        'verification_date',
        'package_id',
        'package_start_date',
        'package_end_date',
        'total_exports',
        'rating',
        'view_count'
    ];

    protected $casts = [
        'verification_date' => 'datetime',
        'package_start_date' => 'datetime',
        'package_end_date' => 'datetime',
        'rating' => 'decimal:2',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function exportHistories()
    {
        return $this->hasMany(ExportHistory::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Helper methods
    public function isVerified()
    {
        return $this->verification_status === 'verified';
    }

    public function isPending()
    {
        return $this->verification_status === 'pending';
    }

    public function hasActivePackage()
    {
        return $this->package_end_date && $this->package_end_date->isFuture();
    }

    public function incrementViews()
    {
        $this->increment('view_count');
    }
}
