<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

trait HasSidebar
{
    public bool $isSidebarMenuVisible = false;

    public function toggleSidebarMenu(): void
    {
        $this->isSidebarMenuVisible = !$this->isSidebarMenuVisible;
    }
}
