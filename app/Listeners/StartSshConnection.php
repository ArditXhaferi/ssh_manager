<?php

namespace App\Listeners;

use Native\Laravel\Events\Menu\MenuItemClicked;
use App\Events\SshMenuItemConnect;
use App\Livewire\SshManager;
use Illuminate\Support\Facades\Log;

class StartSshConnection
{
    /**
     * Handle the menu item clicked event.
     */
    public function handle(MenuItemClicked $event): void
    {
        Log::info('Starting SSH connection', [
            'connection_id' => $event->item['label']
        ]);

        $connectionId = explode(' | ', $event->item['label'])[0];
        $connectionName = explode(' | ', $event->item['label'])[1];

        $manager = new SshManager();
        $manager->startConnection($connectionId);

        Log::info('SSH connection initiated successfully');
    }
}