<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;

class ContactStat extends Model {
    
    protected $table = 'contact_stat';
    
    public $timestamps = false;

        
    function getData($contact_id){
        if ( empty($contact_id) ) {
            return false;
        }
        $_data = self::where("contact_id",$contact_id)->first();
        if ($_data) {
            $data = $_data->toArray();
        }
        return $data;
    }
}