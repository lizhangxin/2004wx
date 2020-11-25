<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    //
    public function test(){
                $data=[
                    "name"=>"adsasd",
                    "age"=>123,
                    "sex"=>1
                ];
            echo json_encode($data);
        //        echo '<pre>';print_r($_POST);echo '</pre>';
//        echo '<pre>';print_r($_GET);echo '</pre>';
    }
}
