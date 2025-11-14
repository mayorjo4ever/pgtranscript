<?php

namespace App\View\Components\admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class modal extends Component
{
    /**
     * Create a new component instance.
     *
     * @param string $name Modal identifier for opening/closing
     * @param string $title Default modal title
     * @param string $size Modal size (sm, md, lg, xl, 2xl, etc.)
     * @param bool $backdrop Whether clicking backdrop closes modal
     * @param bool $keyboard Whether escape key closes modal
     * @param bool $show Whether modal is initially shown
     * @param string|null $maxWidth Custom max width override
     */
    public string $name;
    public string $title;
    public string $size;
    public bool $backdrop;
    public bool $keyboard;
    public bool $show;
    public ?string $maxWidth;
    
       public function __construct(
        string $name = 'modal',
        string $title = 'Modal Title',
        string $size = 'md',
        bool $backdrop = true,
        bool $keyboard = true,
        bool $show = false,
        ?string $maxWidth = null
    ) {
        $this->name = $name;
        $this->title = $title;
        $this->size = $size;
        $this->backdrop = $backdrop;
        $this->keyboard = $keyboard;
        $this->show = $show;
        $this->maxWidth = $maxWidth;
    }
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.admin.modal');
    }
     public function getSizeClasses(): array
    {
        return [
            'sm' => 'max-w-sm',
            'md' => 'max-w-md',
            'lg' => 'max-w-lg',
            'xl' => 'max-w-xl',
            '2xl' => 'max-w-2xl',
            '3xl' => 'max-w-3xl',
            '4xl' => 'max-w-4xl',
            '5xl' => 'max-w-5xl',
            '6xl' => 'max-w-6xl',
            '7xl' => 'max-w-7xl'
        ];
    }

    /**
     * Get the appropriate size class
     *
     * @return string
     */
    public function getModalSizeClass(): string
    {
        if ($this->maxWidth) {
            return "max-w-{$this->maxWidth}";
        }

        $sizeClasses = $this->getSizeClasses();
        return $sizeClasses[$this->size] ?? $sizeClasses['md'];
    }
}
