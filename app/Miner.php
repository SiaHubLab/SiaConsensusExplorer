<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Miner extends Model
{
    protected $table = 'miners';

    protected $fillable = ['name'];
}
