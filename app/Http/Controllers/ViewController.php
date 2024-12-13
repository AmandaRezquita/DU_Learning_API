<?php

namespace App\Http\Controllers;

use App\Models\Student\Auth\Student;
use Illuminate\Http\Request;

class ViewController extends Controller
{
    public function index(){
        $student = Student::all();
        return view('student.index', compact('student'));
    }
}
