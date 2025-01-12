<?php

namespace App\Http\Controllers;

use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Menu;
use App\Models\SshConnection;
use Native\Laravel\Facades\Window;

class MenuController extends Controller
{
    public function buildMenu()
    {
        $recentConnections = SshConnection::orderBy('updated_at', 'desc')
            ->take(3)
            ->pluck('name', 'id')
            ->toArray();

        $menuItems = [
            Menu::label('Recent Connections'),
            Menu::separator(),
        ];

        if (empty($recentConnections)) {
            $menuItems[] = Menu::label('No recent connections');
        } else {
            $connectionMenuItems = collect($recentConnections)->map(function ($name, $id) {
                if (!is_array($name)) {
                    $label = (string) $id . ' | ' . (string) $name;
                    return Menu::label((string) $label);
                }
            })->values()->all();

            $menuItems = array_merge($menuItems, $connectionMenuItems);
        }

        $menuItems = array_merge($menuItems, [
            Menu::separator(),
            Menu::quit()
        ]);

        return Menu::make(...$menuItems);
    }

    public function refreshMenu()
    {
        Window::create();
        MenuBar::create()
            ->width(300)
            ->height(600)
            ->icon(public_path('menuBarIcon.png'))
            ->blendBackgroundBehindWindow()
            ->withContextMenu($this->buildMenu());
    }
}