<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\common\model\Nav;
class NavController extends Controller {
    /**
     * 增加
     */
    public function add() {
        return $this->fetch();
    }
    /**
     * 处理数据
     */
    public function insert() {
        //接受数据
        $postData = Request::instance()->post();
        $Nav = new Nav();
        $Nav->title = $postData['title'];    
        $Nav->save();
        return '插入成功';
    }
}