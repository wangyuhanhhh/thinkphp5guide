<?php
namespace app\index\controller; //指出该文件的位置
use think\Controller;   //用于向V层进行数据的传递
use app\common\model\News;   //新闻模型
use think\Request;  //引用Request
use think\Db;
use app\common\model\Nav;

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
     * 根据新闻id获取正文
     */
    public function detail($id) 
    {
        //根据id从数据库中检索新闻数据
        $news = Db::name('news')->find($id);
        
        //获取新闻列表(desc降序排列;asc升序排列)
        $newsList = Db::name('news')->order('id', 'asc')->select(); 
       
        // 找出当前新闻在列表中的位置($currentPosition是键值) 
        $currentPosition = array_search($news, $newsList, true);
       
        //如果没有找到(可能是因为列表不完整或当前新闻不在列表中)，则默认为第一条
        if ($currentPosition === false) {
            $nextId = $newsList[0]['id'];
        } else {
            //获取下一条新闻的键值(注意检查边界条件)
            $nextIndex = $currentPosition + 1;

            //获取上一条新闻的键值
            $prevIndex = $currentPosition - 1;

            if($prevIndex >= 0) {
                $prevId = $newsList[$prevIndex]['id'];
            } else {
                //如果已经是第一条新闻了，设置默认
                $prevId = null;
            }

            if (isset($newsList[$nextIndex])) {
                $nextId = $newsList[$nextIndex]['id'];
            } else {
                //如果没有下一个新闻，设置默认提示
                $nextId = null;
            }
        }

        //传数据到模版
        $this->assign('news', $news);
        $this->assign('nextIndex', $nextIndex);
        $this->assign('prevIndex', $prevIndex);
        $this->assign('prevId', $prevId);
        $this->assign('nextId', $nextId);
        $this->assign('newsList', $newsList);

        if(!$news) {
            //如果找不到新闻，抛出异常
            $this->error('新闻不存在');
        }

        //将新闻数据传给V层，同时渲染出模版
        return $this->fetch('detail', ['new' => $news]);
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
        //查询新闻数据并分页
        $News = new News;
        $pageSize = 5;
        $newses = $News->paginate($pageSize);

        // 向V层传数据
        $this->assign('newses', $newses);

        // 取回打包后的数据
        $htmls = $this->fetch();

        // 将数据返回给用户
        return $htmls;
    }
    /**
     * $getPath 表单传来的值
     * $savePath 将\替换成/的地址
     * $path保存到数据库的地址
     */
    public function insert()
    {
        //处理图片
        $file = request()->file('file');
        if ($file) {
            $info = $file->validate([
                'ext' => 'jpg,jpeg,png,gif'
                ])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'photo');
            if ($info) {
                $getPath = $info->getSaveName();
                //将\替换为/ 一个\代表转义字符，使用两个\\告诉php将其视为普通反斜杠
                $savePath = str_replace('\\', '/', $getPath);
                $path = '/thinkphp5guide/public/uploads/photo/' . $savePath;
            }
        }   

        //保存文字。接收传入数据
        $postData = Request::instance()->post();       
        //实例化空对象
        $News = new News();
        // 将换行符转换为<br>标签
        $postData['content'] = nl2br($postData['content']);
        //插入信息
        $News->Description = $postData['Description'];
        $News->author = $postData['author'];
        $News->content = $postData['content'];
        $News->photo_path = $path;
        $News->time = $postData['time'];
        //保存                
        $News->save();
        return $this->success('新增成功', url('News/upload'));
    }  

    /**
     * 置顶文件
     * id form表单传过来的id值
     * NewsId 置顶操作
     */
    public function setTop() {  
        //接收数据
        $postData = Request::instance()->post();
        $NewsId = $postData['id'];
        if (is_null($NewsId)) {
            return $this->error('请选择要置顶的新闻', url('upload'));
        }
        $News  = new News();
        //调用M层中的top方法
        $result = $News->top($NewsId);
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
        $news = News::order('Sort', 'desc')->select();
        $Nav = new Nav();
        $navList = Nav::select();
        // 将数据传给视图
        $this->assign('navList', $navList);
        $this->assign('news', $news);
        return $this->fetch();
    }
}