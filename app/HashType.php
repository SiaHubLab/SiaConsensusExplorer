<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HashType extends Model
{
    public static $type = null;

    public function __construct($type){
        self::$type = $type;
    }

    public static function title(){
        $title = "Unknown";
        switch (self::$type) {
            case 'blockid':
                $title = "Block";
                break;

            case 'transactionid':
                $title = "Transaction ID";
                break;

            case 'unlockhash':
                $title = "Unlock hash";
                break;

            case 'siacoinoutputid':
                $title = "SiaCoin Output";
                break;

            case 'filecontractid':
                $title = "File Contract";
                break;

            case 'siafundoutputid':
                $title = "SiaFund Output";
                break;
        }
        return $title;
    }

}
