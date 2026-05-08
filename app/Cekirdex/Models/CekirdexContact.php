<?php

namespace App\Cekirdex\Models;

use Illuminate\Database\Eloquent\Model;

class CekirdexContact extends Model
{
    protected $table = 'cekirdex_contacts';

    public const STATUSES = [
        'new'     => 'Yeni',
        'read'    => 'Okundu',
        'replied' => 'Yanıtlandı',
        'closed'  => 'Kapandı',
        'spam'    => 'Spam',
    ];

    protected $fillable = [
        'name', 'email', 'phone',
        'restaurant_name', 'city',
        'subject', 'message', 'source',
        'status', 'read_at', 'replied_at', 'notes',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'read_at'    => 'datetime',
        'replied_at' => 'datetime',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) return $query;
        $like = '%'.$term.'%';
        return $query->where(function ($q) use ($like) {
            $q->where('name', 'like', $like)
              ->orWhere('email', 'like', $like)
              ->orWhere('subject', 'like', $like)
              ->orWhere('restaurant_name', 'like', $like)
              ->orWhere('message', 'like', $like);
        });
    }
}
