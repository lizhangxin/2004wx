<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MediaModel extends Model
{
    protected  $table = "media";
    public $timestamps = false;
    protected  $guarded = [];

}
