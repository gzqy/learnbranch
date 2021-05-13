<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;

class ReceiveAccount extends Model {
    
    protected $table = 'receive_account';
    
    public $timestamps = false;
    
    
    function getData(){
        $_data = self::get();
        if ($_data) {
            foreach($_data as $data) {
                $result[$data->account_id] = $data->toArray();
            }
        }
        return $result;
    }
}