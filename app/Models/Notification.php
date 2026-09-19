<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'app_notifications';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public static function send(int $userId, string $title, ?string $body = null, ?string $url = null): self
    {
        return static::create([
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'url' => $url,
        ]);
    }
}