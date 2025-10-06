<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class Contact extends Model
{
    protected $fillable = [
        'buyer_id',
        'company_id',
        'subject',
        'message',
        'buyer_email',
        'buyer_phone',
        'status',
        'is_unlocked',
        'unlocked_at',
        'unlocked_by',
        'unlock_fee',
        'admin_notes',
        'forwarded_at',
    ];

    protected $casts = [
        'is_unlocked' => 'boolean',
        'unlocked_at' => 'datetime',
        'forwarded_at' => 'datetime',
        'unlock_fee' => 'decimal:2',
    ];

    protected $encrypted = [
        'buyer_email',
        'buyer_phone',
    ];

    /**
     * Encrypt buyer_email before saving
     */
    public function setBuyerEmailAttribute($value)
    {
        $this->attributes['buyer_email'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt buyer_email when accessing
     */
    public function getBuyerEmailAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value; // Return as-is if decryption fails (for legacy data)
        }
    }

    /**
     * Encrypt buyer_phone before saving
     */
    public function setBuyerPhoneAttribute($value)
    {
        if ($value) {
            $this->attributes['buyer_phone'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt buyer_phone when accessing
     */
    public function getBuyerPhoneAttribute($value)
    {
        if (!$value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value; // Return as-is if decryption fails (for legacy data)
        }
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function unlocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unlocked_by');
    }

    /**
     * Mask email address
     */
    public function getMaskedEmailAttribute(): string
    {
        if ($this->is_unlocked) {
            return $this->buyer_email;
        }

        $parts = explode('@', $this->buyer_email);
        $name = $parts[0];
        $domain = $parts[1];
        
        $maskedName = substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
        
        return $maskedName . '@' . $domain;
    }

    /**
     * Mask phone number
     */
    public function getMaskedPhoneAttribute(): ?string
    {
        if (!$this->buyer_phone || $this->is_unlocked) {
            return $this->buyer_phone;
        }

        $length = strlen($this->buyer_phone);
        $visible = 4; // Show first 2 and last 2 digits
        
        return substr($this->buyer_phone, 0, 2) . 
               str_repeat('*', $length - 4) . 
               substr($this->buyer_phone, -2);
    }
}
