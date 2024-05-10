<?php
namespace app\common\model;
use think\Model;
class User extends Model {
     /**
     * 验证密码是否正确
     * @param string $password 密码
     * @return bool
     */
    public function checkPassword($password) {
        if ($this->getData('password') === $password) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 用户登录
     * @param string $username 用户名
     * @param string $password 密码
     * @return bool 成功返回true 失败返回false
     */
    static public function login($username, $password) {
        //验证用户名是否存在
        $map = array('username' => $username);
        $User = self::get($map);
        if (!is_null($User)) {
            if ($User->checkPassword($password)) {
                //登录 存入session
                session('userId', $User->getData('userId'));
                return true;
            }
        }
        return false;
    }
   
    /**
     * 注销
     *@return bool 成功true 失败false
     */
    static public function logOut() {
        //销毁session中数据
        session('userId', null);
        return true;
    }
}