<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use app\common\model\Experiment;
use app\common\model\User;
use think\Db;

class ExperimentController extends Controller
{
    public function add()
    {
        //判断用户登录状态
        if(User::checkLoginStatus()) {
            //已登录
            return $this->fetch();
        } else {
            //未登录
            $this->redirect('Login/loginForm');
        }
       
        
    }

    /**
     * 删除
     */
    public function delete()
    {
        //获取传入的id值
        $id = Request::instance()->param('id/d');
        if (is_null($id) || 0 === $id) {
            return $this->error('为获取到ID信息');
        }
        //获取要删除的对象
        $Experiments = Experiment::get($id);

        //要删除的对象不存在
        if (is_null($Experiments)) {
            return $this->error('不存在id为' . $id . '的新闻，删除失败');
        }

        //删除对象
        if (!$Experiments->delete()) {
            return $this->error('删除失败：' . $Experiments->getError());
        }
        //跳转
        return $this->success('删除成功', url('upload'));
    }

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

    /**
     * 视频上传
     */
    public function insert()
    {
        $file = request()->file('video');
        
        if ($file) 
        {
            //移动到public下的videos目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'videos');
            
            if ($info) 
            {
                //获取资料名称
                $name = $info->getInfo('name');
            
                //获取文件上传日期
                $time = request()->post('time');
                
                //生成url路径
                $url = '/thinkphp5guide/public/videos/' . $info->getSaveName();
                
                // 获取文件扩展名
                $fileExtension = $info->getExtension();  
                $experiment = new Experiment();
                $data = [
                    'name' => $name,
                    'path' => $info->getPathname(),
                    'saveName' => $url,
                    'time' => $time,
                ];

                //如果以上数据成功保存到数据库中，$saveResult的值为1；反之，为0
                $saveResult = $experiment->save($data);
                
                if ($saveResult) 
                {
                    // 保存成功
                    $this->success('视频上传成功！', 'Experiment/upload');
                } else 
                {
                    // 保存失败
                    return '视频上传失败，保存数据库时出错！';
                }
            } else 
            {
                // 文件移动失败，输出错误信息
                return '视频移动失败';
            }
        } else 
        {
            // 没有上传文件
            return '没有视频被上传！';
        }
    }

    /**
     * 实验上传列表
     */
    public function upload()
    {
        //判断用户登录状态
        if(User::checkLoginStatus()) {
            //已登录，查询资料数据并分页
            $pageSize = 5;
            $Experiment = new Experiment;
            $experiments = $Experiment->paginate($pageSize);

            //向v层传数据
            return $this->fetch('upload', ['experiments' => $experiments]);
        } else {
            //未登录
            $this->redirect('Login/loginForm');
        }        
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