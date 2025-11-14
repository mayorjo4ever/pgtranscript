<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Degree extends Model
{
    //
    
    public static function name($id){
        $degree = Degree::findOrFail($id); 
        if($degree->category == "professional"):
            return $degree->full_name." ".$degree->prefix; 
        else :
            return $degree->short_name;
        endif;
        
    }
}
