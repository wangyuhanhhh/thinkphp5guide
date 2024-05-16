<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use app\common\model\Experiment;
use think\Db;

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

    public function detail($id)
    {
        //根据id检索实验内容
        $experiment = Db::name('experiment')->find($id);

        //获取实验列表
        $experimentList = Db::name('experiment')->order('id', 'asc')->select();

        //找出当前实验位置
        $currentPosition = array_search($experiment, $experimentList, true);

        //如果没有找到，默认为第一个实验
        if ($currentPosition === false) {
            $nextId = $experimentList[0]['id'];
        } else {
            //获取下一个实验的键值
            $nextIndex = $currentPosition + 1;

            //获取上一条试验键值
            $prevIndex = $currentPosition - 1;

            if ($prevIndex >= 0) {
                $prevId = $experimentList[$prevIndex]['id'];
            } else {
                //如果已经是第一个实验，设置默认值
                $prevId = null;
            }

            if(isset($experimentList[$nextIndex])) {
                $nextId = $experimentList[$nextIndex]['id'];
            } else {
                //如果已经是最后一个，设置默认
                $nextId = null;
            }
        }

        //传数据到模版
        $this->assign('experiment', $experiment);
        $this->assign('nextIndex', $nextIndex);
        $this->assign('prevIndex', $prevIndex);
        $this->assign('prevId', $prevId);
        $this->assign('nextId', $nextId);
        $this->assign('experimentList', $experimentList);

        if(!$experiment)
        {
            //如果没有找到，抛出异常
            $this->error('该实验内容不存在');
        }

        return $this->fetch('detail', ['experiment' => $experiment]);
    }
}