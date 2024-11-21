<?php

namespace App\Http\Controllers\StudentCtrl\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TimeGreetingController extends Controller
{
    public function greet(){
    
        {
            $name = auth()->user()->nickname ?? 'User';
    
            date_default_timezone_set('Asia/Jakarta');
            $hour = now()->hour;
    
            if ($hour < 12) {
                $time = 'morning';
            } 
            elseif ($hour < 18) {
                $time = 'afternoon';
            } 
            else{
                $time = 'night';
            }
    
            return response()->json([
                'name' => $name,
                'time' => $time,
            ]);
        }
    
    }
}
