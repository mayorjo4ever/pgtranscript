<?php

namespace App\View\Components\Admin;

use App\Models\Admin;
use Illuminate\View\Component;
use function now;
use function view;

class Dashboard extends Component
{
    public $activeAdmins;
    public $inactiveAdmins;

    public function __construct($activeAdmins, $inactiveAdmins)
    {
        $this->activeAdmins = $activeAdmins;
        $this->inactiveAdmins = $inactiveAdmins;
    }

    public function render()
    {
        return view('components.admin.dashboard');
    }
}
