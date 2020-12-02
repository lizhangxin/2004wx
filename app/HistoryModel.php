<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistoryModel extends Model
{
    //设置表名
    protected $table="history";
    //设置主键
    protected $primaryKey="hid";
    //设置时间戳
    public $timestamps=false;
}
