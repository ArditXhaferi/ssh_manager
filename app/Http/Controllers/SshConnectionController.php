<?php

namespace App\Http\Controllers;

use App\Models\SshConnection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use phpseclib3\Net\SSH2;
use Illuminate\Support\Facades\Log;
use App\Helpers\SSHHelper;

class SshConnectionController extends Controller
{
    public function index()
    {
        $connections = SshConnection::orderBy('created_at', 'desc')->get();
        return view('home', compact('connections'));
    }

}