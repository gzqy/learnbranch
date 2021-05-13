<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;

class DeviceCallFlag extends Model {
    
    protected $table = 'device_call_flag';
    
    public $timestamps = false;
    
    function getData($account_id){
        if ( empty($account_id) ) {
            return false;
        }
        $_data = self::where("account_id",$account_id)->get();
        if ($_data) {
            
        }
        return $result;
    }
    
    function isAttention($account_id,$call_id){
        if ( empty($account_id) || empty($call_id) ) {
            return false;
        }
        $_data = self::where("account_id",$account_id)->where("call_id",$call_id)->first();
        if ($_data->id) {
            return true;
        }
        return false;
    }
}