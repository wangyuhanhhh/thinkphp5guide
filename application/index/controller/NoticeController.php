<?php
namespace app\index\controller;

use think\Controller;
use app\common\model\Notice;
use think\Request;

class NoticeController extends Controller
{
    public function index()
    {
        $Notice = new Notice();
        $notices = $Notice->select();

        //向V层传数据
        $this->assign('notices', $notices);

        //取回数据
        $htmls = $this->fetch();

        //返回至V层
        return $htmls;

    }
}
