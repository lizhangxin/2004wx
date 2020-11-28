<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_openidModel extends Model
{
    //
    protected  $table = "user_openid";
    public $timestamps = false;
    protected  $guarded = [];
}
