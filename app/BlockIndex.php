<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlockIndex extends Model
{
    protected $table = 'block_hash_index';

    public function hash()
    {
        return $this->belongsTo(Hash::class);
    }
}
