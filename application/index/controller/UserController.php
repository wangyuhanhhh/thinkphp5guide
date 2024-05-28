<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\common\model\User;
class UserController extends Controller {
    /**
     * 用户添加表单
     */
    public function add() {
        //判断用户登录状态
        if(User::checkLoginStatus()) {
            //已登录
            return $this->fetch();
        } else {
            //未登录
            $this->redirect('Login/loginForm');
        }
        
        
    }
    
    //删除
    public function delete() {
        //获取id
        $id = Request::instance()->param('id/d');
        if (is_null($id) || 0 === $id) {
            return $this->error('为获取到ID信息');
        }
        //获取要删除的对象
        $user = User::get($id);
        //对象不存在
        if (is_null($user)) {
            return $this->error('不存在id为' . $id . '的用户，删除失败');
        }
        //删除
        if (!$user->delete()) {
            return $this->error('删除失败：' . $user->getError());
        }
        //跳转
        return $this->success('删除成功', url('User/index'));
    }

    //读取数据
    public function edit() {
        //判断用户登录状态
        if(User::checkLoginStatus()) {
            //已登录，获取id
            $userId = Request::instance()->post('id/d');
            //查找是否存在id为多少的用户
            if (is_null( $User = User::get($userId))) {
                return '未找到ID为' . $id . '的记录';
            }
            //将数据传给V层
            $this->assign('User', $User);
            //获取封装好的V层内容并返回给客户
            return $this->fetch();
        } else {
            //未登录
            $this->redirect('Login/loginForm');
        }
        
    }
    /**
     * 用户中心
     */
    public function index() {
        //判断用户登录状态
        if(User::checkLoginStatus()) {
            //已登录,同时启用分页
            $pageSize = 5;
            $User = new User();
            $users = $User->paginate($pageSize);

            //向V层传递数据
            $this->assign('users', $users);
            return $this->fetch();
        } else {
            //未登录
            $this->redirect('Login/loginForm');
        }
    }
    /**
     * 处理添加的数据
     */
    public function insert() {
        //接收数据
        $postData = Request::instance()->post();
        $User = new User();
        $User->name = $postData['name'];
        $User->username = $postData['username'];
        $User->email = $postData['email'];
        $User->create_date = $postData['create_date'];
        $result = $User->validate(true)->save();
        if($result === false) {
            return $this->error('数据添加错误：' . $User->getError());
        } else {
            return $this->success('新增成功', url('User/upload'));
        }
    }

    //处理数据
    public function update() {
        //接收数据
        $userId = Request::instance()->post('id/d');
     
        $User = User::get($userId);
        if (!is_null($User)) {
            //写入要更新的数据
            $User->name = Request::instance()->post('name');
            $User->username = Request::instance()->post('username');
            $User->email = Request::instance()->post('email');
            //保存要更新的数据
            $result = $User->validate(true)->save();
            if($result === false) {
                return $this->error('数据添加错误：' . $User->getError());
            } else {
                return $this->success('新增成功', url('User/index'));
            }
        }
    }
}