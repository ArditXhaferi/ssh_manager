<?php

use App\Http\Controllers\SshConnectionController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::controller(SshConnectionController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::post('/ssh-connections', 'store')->name('ssh-connections.store');
    Route::put('/ssh-connections/{sshConnection}', 'update')->name('ssh-connections.update');
    Route::delete('/ssh-connections/{sshConnection}', 'destroy')->name('ssh-connections.destroy');
    Route::post('/ssh-connections/{sshConnection}/toggle', 'toggleConnection')->name('ssh-connections.toggle');
});
