<?php
namespace Common\Model;

class ContactGroupModel extends CommonModel {
    protected $table = 'contact_group_app';
    public $timestamps = false;
    
    function getOption(){
        $dat=$_SESSION['ACCOUNTS']['tel'];
        if( !$dat){
            $data='';
            return $data;
        }else{

        $data =  M("$this->table")->where("app_code = {$dat}")->order("id desc")->select();


        return $data;
        }
    }
    
    
    function getName($id){
        if ( empty($id) ) {
            return false;
        }
        $_data =  M("$this->table")->where("id",$id)->first();
        if ( $_data ) {
            $data = $_data->name;  
        }
        return $data;
    }
}