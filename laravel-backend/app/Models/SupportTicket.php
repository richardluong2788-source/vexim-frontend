<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'message',
        'status',
        'admin_reply',
        'replied_at'
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'open');
    }

    // Helper methods
    public function isOpen()
    {
        return in_array($this->status, ['open', 'in_progress']);
    }

    public function isClosed()
    {
        return in_array($this->status, ['resolved', 'closed']);
    }
}
