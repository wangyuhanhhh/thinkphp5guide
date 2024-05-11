<?php
namespace app\index\controller;
use think\Controller;
use app\common\model\User;
class UserController extends Controller {
    /**
     * 用户中心
     */
    public function index() {
        $User = new User();
        $users = $User->select();
        //向V层传递数据
        $this->assign('users', $users);
        return $this->fetch();
    }
}