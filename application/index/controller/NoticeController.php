<?php
namespace app\index\controller;

use think\Controller;
use app\common\model\Notice;
use think\Request;

class NoticeController extends Controller
{
    public function index()
    {
        //查询数据并启用分页
        $pageSize = 5;
        $Notice = new Notice();
        $notices = $Notice->paginate($pageSize);

        //向V层传数据
        $this->assign('notices', $notices);

        //取回数据
        $htmls = $this->fetch();

        //返回至V层
        return $htmls;

    }
}
