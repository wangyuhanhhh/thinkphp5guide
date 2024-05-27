<?php
namespace app\index\controller;
use think\Request;
use think\Controller;
use app\common\model\Download;
use app\common\model\User;
use think\Db;
use think\facade\Env; 

class DownloadController extends Controller
{
    /**
     * 资料上传
     */
    public function add()
    {
        //判断登录状态
        if(User::checkLoginStatus()) {
            //已登录
            return $this->fetch();
        } else {
            //未登录
            $this->redirect('Login/loginForm');
        }    
    }

    /**
     * 删除操作
     */
    public function delete()
    {
        //获取传入的id值
        $id = Request::instance()->param('id/d');
        if (is_null($id) || 0 === $id) {
            return $this->error('为获取到ID信息');
        }
        //获取要删除的对象
        $Downloads = Download::get($id);

        //要删除的对象不存在
        if (is_null($Downloads)) {
            return $this->error('不存在id为' . $id . '的新闻，删除失败');
        }

        //删除对象
        if (!$Downloads->delete()) {
            return $this->error('删除失败：' . $Downloads->getError());
        }
        //跳转
        return $this->success('删除成功', url('upload'));
    }

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

    /**
     * 资料上传
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
                    $download = new Download();
                    $data = [
                        'name' => $name,
                        'path' => $info->getPathname(),
                        'saveName' => $url,
                        'time' => $time,
                    ];
                    if ($download->save($data)) {
                        return $this->success('新增成功', url('Download/upload'));
                    } 
                } else {
                    return $this->error('请选择今天及今天之前的时间', url('Download/add'));
                }
            } else {
                return $this->error('资料获取失败', url('Download/upload'));
            }
        } else {
            return $this->error('没有文件被上传', url('Download/upload'));
        }
    }
            

    /**
     * 获取上传文件的信息
     * @param string $name
     * @return array string
     */
    public function getInfo($name = '')
    {
        return isset($this->info[$name]) ? $this->info[$name] : $this->info;
    }

    /**
     * 管理端资料上传列表
     */
    public function upload() 
    {
        //判断用户登录状态
        if(User::checkLoginStatus()) {
            //已登录，查询资料数据并分页
            $pageSize = 5;
            $Download = new Download;
            $downloads = $Download->paginate($pageSize);

            //向v层传数据
            return $this->fetch('upload', ['downloads' => $downloads]);
        } else {
            //未登录
            $this->redirect('Login/loginForm');
        }     
    }
}