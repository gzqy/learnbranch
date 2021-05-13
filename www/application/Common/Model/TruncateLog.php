<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
class TruncateLog extends Model {
    
    protected $table = 'truncate_log';
    
    public $timestamps = false;
}