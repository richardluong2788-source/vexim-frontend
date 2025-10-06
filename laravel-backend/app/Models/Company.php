<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'verification_id',
        'description',
        'country',
        'city',
        'address',
        'website',
        'email',
        'phone',
        'business_type',
        'main_products',
        'year_established',
        'employee_count',
        'annual_revenue',
        'logo',
        'cover_image',
        'verification_status',
        'verified_at',
        'package_id',
        'package_expires_at',
        'visibility_level',
        'rating',
        'total_reviews',
        'contact_email',
        'contact_phone',
        'show_contact_info',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'package_expires_at' => 'datetime',
        'rating' => 'decimal:2',
        'show_contact_info' => 'boolean',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
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

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function documents()
    {
        return $this->hasMany(SupplierDocument::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopeActive($query)
    {
        return $query->where('verification_status', 'verified')
                     ->where('package_expires_at', '>', now());
    }

    // Helper methods
    public function isVerified()
    {
        return $this->verification_status === 'verified';
    }

    public function hasActivePackage()
    {
        return $this->package_expires_at && $this->package_expires_at->isFuture();
    }

    public function generateVerificationId()
    {
        $prefix = 'VXM';
        $year = date('Y');
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $year . $random;
    }

    public function getMaskedEmailAttribute()
    {
        if ($this->show_contact_info || !$this->contact_email) {
            return $this->contact_email;
        }

        $parts = explode('@', $this->contact_email);
        $name = $parts[0];
        $domain = $parts[1];
        
        $maskedName = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));
        
        return $maskedName . '@' . $domain;
    }

    public function getMaskedPhoneAttribute()
    {
        if ($this->show_contact_info || !$this->contact_phone) {
            return $this->contact_phone;
        }

        $length = strlen($this->contact_phone);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        
        return substr($this->contact_phone, 0, 2) . 
               str_repeat('*', $length - 4) . 
               substr($this->contact_phone, -2);
    }
}
