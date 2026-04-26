<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
     protected $fillable = [
        'telegram_id',
        'type',
        'reference',
        'title',
        'note'
    ];

    public function user()
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_id', 'telegram_id');
    }
}
