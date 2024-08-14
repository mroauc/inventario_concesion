<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function index(Request $request){
        $logs = \App\Models\Log::where('id_concession', auth()->user()->id_concession)->get();
        return view('logs.index')->with('logs', $logs);
    }
}
