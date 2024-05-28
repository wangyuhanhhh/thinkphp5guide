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
        $file = request()->file('file');
       
        if ($file) 
        {
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            
            if ($info) 
            {
                $postData = Request::instance()->post();

                //获取资料名称
                $name = $info->getInfo('name');
            
                //获取文件上传日期
                $time = request()->post('time');
                
                //生成url路径
                $url = '/thinkphp5guide/public/uploads/' . $info->getSaveName();
                
                // 获取文件扩展名
                $fileExtension = $info->getExtension();

                //获取当前时间戳
                $currentTime = time(); 

                //将时间戳转化为'y-m-d'型   
                $formattedDate = date('Y-m-d', $currentTime);
                $putTime = $postData['time'];
                //用户选择的时间
                if ($putTime <= $formattedDate) {
                    $experiment = new Experiment();
                    $data = [
                        'name' => $name,
                        'path' => $info->getPathname(),
                        'saveName' => $url,
                        'time' => $time,
                    ];
                    if ($experiment->save($data)) {
                        return $this->success('新增成功', url('Experiment/upload'));
                    } 
                } else {
                    //如果触发报错跳转，保存此时表单中的内容
                    $this->saveFormData($postData);
                    return $this->error('请选择今天及今天之前的时间', url('Experiment/add'));
                }
            } else {
                return $this->error('资料获取失败', url('Experiment/upload'));
            }
        } else {
            return $this->error('没有文件被上传', url('Experiment/upload'));
        }
    }

    /**
     * 保存数据到Flash中，在新增报错回跳后，保留报错前表单信息
     */
    private function saveFormData($postData) {
        //使用flash函数保存数据
        session('formData', $postData);
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