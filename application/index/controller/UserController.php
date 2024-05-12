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
        return $this->fetch();
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
        //获取id
        $id = Request::instance()->post('id/d');
        //查找是否存在id为多少的用户
        if (is_null( $User = User::get($id))) {
            return '未找到ID为' . $id . '的记录';
        }
        //将数据传给V层
        $this->assign('User', $User);
        //获取封装好的V层内容并返回给客户
        return $this->fetch();
    }
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
        $result = $User->save();
        if ($result) {
            return $this->success('新增成功', url('User/index'));
        } else {
            return $this->error('新增失败');
        }
    }

    //处理数据
    public function update() {
        //接收数据
        $id = Request::instance()->post('id/d');
        $User = User::get($id);
        if (!is_null($User)) {
            //写入要更新的数据
            $User->name = Request::instance()->post('name');
            $User->username = Request::instance()->post('username');
            $User->email = Request::instance()->post('email');
            //保存要更新的数据
            if (false === $User->validate(true)->save()) {
                return $this->error('更新失败');
            }
        }
        return $this->success('更新成功', url('User/index'));
    }
}