<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProofIndex extends Model
{
    protected $table = 'filecontract_proof_index';

    public function contract()
    {
        return $this->belongsTo(Hash::class, 'fc_hash_id');
    }

    public function proof()
    {
        return $this->belongsTo(Hash::class, 'proof_hash_id');
    }
}
