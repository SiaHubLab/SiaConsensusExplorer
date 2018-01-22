<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlockMetric extends Model
{
    protected $table = 'block_metrics';

    public $timestamps = false;

    protected $fillable = ['height', 'difficulty', 'estimatedhashrate', 'timestamp', 'transactions', 'new_file_contracts', 'revisioned_file_contracts'];
}