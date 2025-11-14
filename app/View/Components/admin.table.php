<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class admin.table extends Component
{    
    public $headers; 
    public $rows;
    
    public function __construct($headers  = [], $rows  = [])
    {
        $this->headers = $headers; 
        $this->rows = rows; 
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.admin.table');
    }
}
