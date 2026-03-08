<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public bool $hideSidebar;
    public bool $hideHeaderSearch;
    public bool $hideDashboardLink;
    public string $pageTitle;

    /**
     * Create a new component instance.
     */
    public function __construct(
        bool $hideSidebar = false,
        bool $hideHeaderSearch = false,
        bool $hideDashboardLink = false,
        string $pageTitle = ''
    ) {
        $this->hideSidebar = $hideSidebar;
        $this->hideHeaderSearch = $hideHeaderSearch;
        $this->hideDashboardLink = $hideDashboardLink;
        $this->pageTitle = $pageTitle;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
