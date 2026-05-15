<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
      protected $fillable = [
        'telegram_id',
        'date',
        'preacher',
        'topic',
        'message',
        'additional_notes'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    public function getFormattedDateAttribute()
    {
        return $this->date->format('M d, Y');
    }
    
    public function user()
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_id', 'telegram_id');
    }
}
