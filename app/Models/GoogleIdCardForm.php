<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleIdCardForm extends Model
{
    protected $fillable = ['request_time',
        'request_email','regno','phone',
        'entry_session','fullname','degree',
        'programme','faculty','department',
        'passport','signature'];
}
