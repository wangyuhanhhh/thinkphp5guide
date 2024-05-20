<?php
namespace app\index\controller;
use think\Request;
use think\Controller;
use app\common\model\Download;
use think\Db;
use think\facade\Env; 

class DownloadController extends Controller
{
    /**
     * 资料上传
     */
    public function add()
    {
        return $this->fetch();
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
                //获取资料名称
                $name = $info->getInfo('name');
            
                //获取文件上传日期
                $time = request()->post('time');
                
                //生成url路径
                $url = '/thinkphp5guide/public/uploads/' . $info->getSaveName();
                
                // 获取文件扩展名
                $fileExtension = $info->getExtension();  
                $download = new Download();
                $data = [
                    'name' => $name,
                    'path' => $info->getPathname(),
                    'saveName' => $url,
                    'time' => $time,
                ];

                //如果以上数据成功保存到数据库中，$saveResult的值为1；反之，为0
                $saveResult = $download->save($data);
                
                if ($saveResult) 
                {
                    // 保存成功
                    $this->success('文件上传成功！', 'Download/upload');
                } else 
                {
                    // 保存失败
                    return '文件上传失败，保存数据库时出错！';
                }
            } else 
            {
                // 文件移动失败，输出错误信息
                return '文件移动失败';
            }
        } else 
        {
            // 没有上传文件
            return '没有文件被上传！';
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
        //查询资料数据并分页
        $pageSize = 5;
        $Download = new Download;
        $downloads = $Download->paginate($pageSize);

        //向v层传数据
        return $this->fetch('upload', ['downloads' => $downloads]);
    }
}