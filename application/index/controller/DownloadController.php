<?php
namespace app\index\controller;
use think\Request;
use think\Controller;
use app\common\model\Download;
use think\Db;

class DownloadController extends Controller
{
    public function index()
    {
        //查询资料数据并分页
        $pageSize = 5;
        $Download = new Download;
        $downloads = $Download->paginate($pageSize);

        //向V层传数据
        $this->assign('downloads', $downloads);

        //取回打包好的数据
        $htmls = $this->fetch();

        //返回数据
        return $htmls;

    }
}