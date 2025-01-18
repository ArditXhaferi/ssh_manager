<?php

namespace App\Actions;

use Livewire\Component;

class DispatchNotification
{
    public static function execute(Component $component, string $title, string $message, string $type = 'success'): void
    {
        $component->dispatch('notify', [
            'title' => $title,
            'message' => $message,
            'type' => $type
        ]);
    }
} 