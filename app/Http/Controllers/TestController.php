<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function guzzle1(){
        echo __METHOD___;
    }
}
