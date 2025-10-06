<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'company_id',
        'status',
        'language',
        'weekly_contact_count',
        'contact_count_reset_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'contact_count_reset_at' => 'datetime',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'buyer_id');
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    // Role checks
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isSupplier()
    {
        return $this->role === 'supplier';
    }

    public function isBuyer()
    {
        return $this->role === 'buyer';
    }

    // Masked contact info
    public function getMaskedEmailAttribute()
    {
        $parts = explode('@', $this->email);
        $name = $parts[0];
        $domain = $parts[1];
        
        $maskedName = substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
        return $maskedName . '@' . $domain;
    }

    public function getMaskedPhoneAttribute()
    {
        if (!$this->phone) return null;
        
        $length = strlen($this->phone);
        return substr($this->phone, 0, 3) . str_repeat('*', $length - 6) . substr($this->phone, -3);
    }

    public function shouldResetContactCount()
    {
        if (!$this->contact_count_reset_at) {
            return true;
        }
        
        return now()->greaterThan($this->contact_count_reset_at);
    }

    public function resetContactCount()
    {
        $this->update([
            'weekly_contact_count' => 0,
            'contact_count_reset_at' => now()->addWeek()
        ]);
    }
}
