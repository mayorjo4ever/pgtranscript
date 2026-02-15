<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticable
{
    use HasFactory, HasRoles; 
    
    protected $guard = 'admin'; 
    
     protected $fillable = [
        'surname',
         'name',
        'firstname',
        'othername',
        'email',
        'mobile',
        'password',
        'status', 
        'last_login_at',
        'last_logout_at',
        'last_seen_at',
        'last_login_ip',
        'user_agent',
    ];
     
     protected $casts = [
        'last_login_at' => 'datetime',
        'last_logout_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    
}
