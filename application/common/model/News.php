<?php
namespace app\common\model;
use think\Model;

class News extends Model {
    protected $autoWriteTimestamp = false;
    public function index(){
        echo 'hello news';
    }
    
}
