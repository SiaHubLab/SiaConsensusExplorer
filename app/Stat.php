<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    protected $table = 'stats';

    protected $fillable = ['route_id', 'execution_time', 'source', 'request_data'];
}
