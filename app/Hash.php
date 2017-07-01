<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hash extends Model
{
    protected $table = 'hashes';

    public function blocks()
    {
        return $this->hasMany(BlockIndex::class);
    }
}
