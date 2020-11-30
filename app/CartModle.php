<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CartModle extends Model
{
    //
    protected  $table = "cart";
    public $timestamps = false;
    protected  $guarded = [];
}
