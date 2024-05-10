<?php
namespace app\index\controller; //指出该文件的位置
use think\Controller;   //用于向V层进行数据的传递
use app\common\model\News;   //新闻模型
use think\Request;  //引用Request
use think\Db;

/**
 * 新闻模块，继承think\Controller类后，利用V层对数据进行打包上传
 */
class NewsController extends Controller 
{
    public function add()
    {
        return $this->fetch();
    }
    /**
     * 删除
     */
    public function delete() {
        //获取传入的id值
        $id = Request::instance()->param('id/d');
        if (is_null($id) || 0 === $id) {
            return $this->error('为获取到ID信息');
        }
        //获取要删除的对象
        $News = News::get($id);
        //要删除的对象不存在
        if (is_null($News)) {
            return $this->error('不存在id为' . $id . '的新闻，删除失败');
        }

        //删除对象
        if (!$News->delete()) {
            return $this->error('删除失败：' . $News->getError());
        }
        //跳转
        return $this->success('删除成功', url('upload'));
    }

    /**
     * 读取数据
     */
    public function edit() {
        //获取传入id
        $id = Request::instance()->param('id/d');
        
        //在表模型中获取当前记录
        if (is_null( $News = News::get($id))) {
            return '未找到ID为' . $id . '的记录';
        }

        //将数据传给V层
        $this->assign('News', $News);
        //获取封装好的V层内容并返回给客户
        return $this->fetch();
    }

    public function index()
    {
        //获取当前页码，默认为第一页
        $currentPage = input('page', 1);

        //查询新闻数据并分页
        $News = new News;
        $pageSize = 5;
        $newses = $News->paginate($pageSize, false, ['query' => request()->param()]);

        // 向V层传数据
        $this->assign('newses', $newses);
        $this->assign('currentPage', $newses);

        // 取回打包后的数据
        $htmls = $this->fetch();

        // 将数据返回给用户
        return $htmls;
    }

    public function insert()
    {
        $file = request()->file('file');
       
        if ($file) 
        {
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if ($info) 
            {
                //获取文件描述信息即文件标题
                $description = request()->post('Description');
                //获取文件上传日期
                $uploadDate = request()->post('UploadDate');
                // 获取保存的文件名
                $fileName = $info->getSaveName();                
                // 获取文件扩展名
                $fileExtension = $info->getExtension();  
                $news = new News();
                $data = [
                    'NewsName' => $fileName,
                    'NewsPath' => $info->getPathname(),
                    'Description' => $description,
                    'Extension' => $fileExtension,
                    'UploadDate' => $uploadDate,
                ];
                $saveResult = $news->save($data);             
                if ($saveResult) 
                {
                    // 保存成功
                    return '文件上传并保存成功！';
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
        //return $this->fetch();
    }

    /**
     * 置顶文件
     * newsId form表单传过来的id值
     * id 置顶操作
     */
    public function setTop() {  
        //接受数据
        $postData = Request::instance()->post();
        $id = $postData['newsId'];
        if (is_null($id)) {
            return $this->error('请选择要置顶的新闻', url('upload'));
        }
        $News  = new News();
        //调用M层中的top方法
        $result = $News->top($id);
        if ($result) {
            return $this->success('置顶成功', url('upload'));
        } else {
            return $this->error('置顶失败请重试');
        }
    }

    /**
     * 接受处理edit传过来的数据
     */
    public function update() {
        //接收数据
        $id = Request()->instance()->post('id/d');
        
        //获取当前对象
        $News = News::get($id);
        if (!is_null($News)) {
            $News->Description = Request::instance()->post('Description');
            $News->UploadDate = Request::instance()->post('UploadDate');
            //更新数据
            if (false === $News->save()) {
                return '更新失败' . $News->getError();
            } 
        } else {
            throw new \Exception("所更新的记录不存在", 1);
        }
        return $this->success('编辑成功', url('upload'));
    }

    /**
     * 管理端上传文件列表
     */
    public function upload() {
        $News = new News();
        $news = News::select();
        $this->assign('news', $news);
        return $this->fetch();
        
    }
}