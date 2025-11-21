<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programme extends Model
{
    //
    protected $fillable = ['degree_id','name']; 
    
    public static function name($id){
        $name = Programme::find($id); 
        return $name->name; 
    }
    
    public function degree(){
        return $this->belongsTo(Degree::class,'degree_id');
    }
}
