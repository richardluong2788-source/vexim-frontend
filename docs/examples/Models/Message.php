<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'company_id',
        'subject',
        'message',
        'admin_status',
        'admin_note',
        'buyer_email_masked',
        'buyer_phone_masked',
        'status'
    ];

    // Relationships
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
