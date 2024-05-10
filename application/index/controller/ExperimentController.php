<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use app\common\model\Experiment;

class ExperimentController extends Controller
{
    public function index()
    {
        //查询数据并启用分页
        $pageSize = 5;
        $Experiment = new Experiment();
        $experiments = $Experiment->paginate($pageSize);

        //向V层传数据
        $this->assign('experiments', $experiments);

        //取回打包好的数据
        $htmls = $this->fetch();

        //返回
        return $htmls;
    }
}