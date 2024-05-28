<?php
namespace app\common\validate;
use think\Validate;

class User extends Validate {
    protected $rule = [
        'name' => 'require|min:2,25',
        'username' => 'require|length:2,25',
        'email' => 'email',
    ];
}