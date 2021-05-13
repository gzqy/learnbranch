<?php
namespace Common\Model;
use Common\Model\CommonModel;
class ContactModel extends CommonModel {
    protected $table = 'contacts';
    protected $tableName = 'contacts';
    public $timestamps = false;
    private static $_contact_data = array();
    public function gender($key=0){
        $array = array(
            1=>'男',
            2=>'女',
        );
        if ($array[$key]) {
            return $array[$key];
        }
        return  false;
    }
    
    function getOption(){
        $_data = self::orderBy('sort', 'asc')->get();
        if ( $_data ) {
            $data = $_data->toArray();
            while (list($key, $val) = @each($data)) {
                $result[$val['id']] = $val;
            }
        }
        return $result;
    }
    
    function getGroups($group_id){
        if ( empty($group_id) ) {
            return false;
        }
        $_data = self::where("group_id",$group_id)->get();
        if ($_data) {
            $data = $_data->toArray();
            $result = self::getData($data);
        }
        return $result;
    }
    
    function getGroupCount($group_id){
        if ( empty($group_id) ) {
            return false;
        }
        $_data = self::where("group_id",$group_id)->count();

        return $_data;
    }
    
    function getData($data){
        
        while (list($key, $val) = @each($data)) {
            $result[$key] = self::forData($val);
        }

        return $result;
    }
    
    function forData($val){
        $stats = \Models\ContactStat::getData($val['id']);
        if ( $stats ) {
            $val = array_merge($stats,$val);
        }
        return $val;
    }
    
    function getAn($id){
        if ( empty($id) ) {
            return false;
        }
        $_data = self::where("id",$id)->first();
        if ( $_data ) {
            $data = $_data->toArray();
            $data = self::forData($data);
        }
        
        
        return $data;
    }
    
    function getId($tel){
        if ( empty($tel) ) {
            return false;
        }
        $_data = self::where("tel1",$tel)->first();
        if ( $_data->id ) {
            return $_data->id;
        }
        return false;
    }
    
    function getContact($tel,$contact_id=0){
        if ( empty($tel) ) {
            return false;
        }
        if ( self::$_contact_data[$tel] ) {
            return self::$_contact_data[$tel];
        }
        if ( $contact_id ) {
            $_data = self::where("id",$contact_id)->first();
        } else {
            $_data = self::where("tel1",$tel)->first();
        }
        if ( $_data->id ) {
            $data = $_data->toArray();
            self::$_contact_data[$tel] = $data;
            return $data;
        }
        return $tel;
    }
    
    function resetKeywords($data) {
        if ( $data['keywords'] || empty($data['id'])) {
            return false;
        }
        $_data = self::where("id",$data['id'])->first();
        
        $_data->keywords = self::getKeywords($data);;
        $_data->save();
        return $data;
    }
    
    
    function getKeywords($data) {
        $keyword_array[] = $data['name'];
        if ( $data['tel1'] ) {
            $keyword_array[] = $data['tel1'];
        }
        if ( $data['tel2'] ) {
            $keyword_array[] = $data['tel2'];
        }
        if ( $data['tel3'] ) {
            $keyword_array[] = $data['tel3'];
        }
        if ( $data['email'] ) {
            $keyword_array[] = $data['email'];
        }
        if ( $data['fax'] ) {
            $keyword_array[] = $data['fax'];
        }
        if ( $data['company'] ) {
            $keyword_array[] = $data['company'];
        }
        if ( $data['position'] ) {
            $keyword_array[] = $data['position'];
        }
        if ( $data['country'] ) {
            $keyword_array[] = $data['country'];
        }
        if ( $data['province'] ) {
            $keyword_array[] = $data['province'];
        }
        if ( $data['city'] ) {
            $keyword_array[] = $data['city'];
        }
        if ( $data['address'] ) {
            $keyword_array[] = $data['address'];
        }
        if ( $data['note'] ) {
            $keyword_array[] = $data['note'];
        }
        return @implode(',', $keyword_array);
    }
}