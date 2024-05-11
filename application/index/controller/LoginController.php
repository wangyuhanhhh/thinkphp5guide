<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\common\model\User;
class LoginController extends Controller {
    /**
     * 用户登录表单
     */
    public function loginForm() {
        return $this->fetch('User/loginForm');
    }
    /**
     * 处理用户提交的登录数据
     */
    public function login() {
        //接受post信息
        $postData = Request::instance()->post();
        
        //直接调用M层方法，进行登录
        if (User::login($postData['username'], $postData['password'])) {
            return $this->success('登录成功', url('News/upload'));
        } else {
            //用户名不存在，跳转到登录界面
            return $this->error('用户名或密码不正确', url('index'));
        }
    }
    /**
     * 注销
     */
    public function logOut() {
        if (User::logOut()) {
            return $this->success('成功退出登录', url('Web/index'));
        } else {
            return $this->error('注销失败', url('index'));
        }
    }
}