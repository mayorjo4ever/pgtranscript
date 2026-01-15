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
        'status'
    ];
    
}
