<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hash extends Model
{
    protected $table = 'hashes';
    public $timestamps = false;
    public function blocks()
    {
        return $this->hasMany(BlockIndex::class);
    }

    public function proofs()
    {
        return $this->hasMany(ProofIndex::class, 'proof_hash_id', 'id');
    }

    public function contracts()
    {
        return $this->hasMany(ProofIndex::class, 'fc_hash_id', 'id');
    }

    public function miner() {
        return $this->belongsTo(Miner::class);
    }
}
