<?php
namespace app\common\validate;
use think\Validate;

class Lab extends Validate {
    protected $rule = [
        'title' => 'require|min:2,50',
        'author' => 'require|length:2,25',
        'content' => 'require|min:2',
    ];
}