<?php
namespace app\index\controller;

use think\Request;
use think\Controller;
use app\common\model\Download;

class DownloadController extends Controller
{
    public function index()
    {
        $Download = new Download();
        $downloads = $Download->select();

        //向V层传数据
        $this->assign('downloads', '$downloads');

        //取回打包好的数据
        $htmls = $this->fetch();

        //返回数据
        return $htmls;


    }
}