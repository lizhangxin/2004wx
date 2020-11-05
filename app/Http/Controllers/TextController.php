<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TextController extends Controller
{
    public function add(){
    	$key = "2004shop";
    	Redis::set($key,time());
    	echo Redis::get($key);

    }
}
