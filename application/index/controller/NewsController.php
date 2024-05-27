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
                 //保存文字。接收传入数据
                $postData = Request::instance()->post();       
                //实例化空对象
                $News = new News();
                // 将换行符转换为<br>标签
                $postData['content'] = nl2br($postData['content']);
                //获取当前时间戳
                $currentTime = time(); 
                //将时间戳转化为'y-m-d'型   
                $formattedDate = date('Y-m-d', $currentTime);
                $putTime = $postData['time'];
                //用户选择的时间
                if ($putTime < $formattedDate) {
                    //插入信息
                    $News->Description = $postData['Description'];
                    $News->author = $postData['author'];
                    $News->content = $postData['content'];
                    $path = '/thinkphp5guide/public/uploads/photo/' . $savePath;
                    $News->photo_path = $path;
                    $News->time = $postData['time'];
                    $News->submitTime = $currentTime;
                    if ($News->save()) {
                        return $this->success('新增成功', url('News/upload'));
                    } else {
                        return $this->error('保存失败', url('News/upload'));
                    }
                } else {
                        return $this->error('请选择今天或之前的时间', url('News/add'));
                }
            } else {
                return $this->error('上传失败请重试', url('News/add'));
            }
        } else {
            return $this->error('未上传文件', url('News/upload'));
        }     
    }  
    
    /**
     * 取消置顶
     * newsId form表单传过来的newsId值
     */
    public function recallTop() {
        //接收数据
        $newsId = Request()->instance()->post('id/d');
        $News = new News();
        //查询当前新闻的Sort值
        $sort = $News->where(['id' => $newsId])->value('Sort');
        if ($sort == 1) {
            //如果为0，则将Sort值加1，置顶
            $result = $News->where(['id' => $newsId])->setField('Sort', 0);
            if ($result !== false) {
                $this->success('取消置顶成功', url('News/upload'));
            } else {
                $this->error('取消置顶失败，请重试', url('News/upload'));
            }
        } else {
            // 如果Sort已经是0，提示已置顶
            $this->error('当前新闻未置顶', url('News/upload'));
        }
    }

    /**
     * 置顶文件
     * newsId form表单传过来的newsId值
     */
    public function setTop() {  
        //接收数据
        $newsId = Request()->instance()->post('id/d');
        $News = new News();
        //查询当前新闻的Sort值
        $sort = $News->where(['id' => $newsId])->value('Sort');
        if ($sort == 0) {
            //如果为0，则将Sort值加1，置顶
            $result = $News->where(['id' => $newsId])->setField('Sort', 1);
            if ($result !== false) {
                $this->success('置顶成功', url('News/upload'));
            } else {
                $this->error('置顶失败，请重试', url('News/upload'));
            }
        } else {
            // 如果Sort已经是1，提示已置顶
            $this->error('当前新闻已置顶', url('News/upload'));
        }
    }

    /**
     * 处理表单传过来的数据
     */
    public function update() {
        // 接收数据
        $id = Request::instance()->post('id/d');
        $file = request()->file('file');
        // 获取当前对象
        $news = News::get($id);
        if (is_null($news)) {
            return $this->error('所更新的记录不存在', url('Photo/index'));
        }
        // 文件上传
        if ($file) {
            $validate = [
                'ext' => 'jpg,jpeg,png,gif'
            ];
            $info = $file->validate($validate)->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'photo');
            if ($info) {
                $getPath = $info->getSaveName();
                //将\替换为/ 一个\代表转义字符，使用两个\\告诉php将其视为普通反斜杠
                $savePath = str_replace('\\', '/', $getPath);
                $path = '/thinkphp5guide/public/uploads/photo/' . $savePath;        
                // 更新
                $news->photo_path = $path;
                $news->Description = Request::instance()->post('Description');
                $news->author = Request::instance()->post('author');
                $news->content = Request::instance()->post('content');
                $news->time = Request::instance()->post('time');
                //获取当前时间戳
                $currentTime = time();
                $news->submitTime = $currentTime;
                //更新数据
                if ($news->save()) {
                    return $this->success('编辑成功', url('upload'));
                } 
            } else {
                throw new \Exception("所更新的记录不存在", 1);
            }
        }
    }

    /**
     * 管理端上传文件列表
     */
    public function upload() {
        $pageSize = 5;
        $News = new News();
        // 获取Sort值为1的数据，并按time倒序排序
        $sort = $News::where('Sort', 1)->order('time', 'desc')->select();
        // 获取Sort值为0的数据，并按time倒序排序
        $other = $News::where('Sort', 0)->order('time', 'desc')->select();
        // 合并两个数组，保证Sort值为1的始终在前面
        $mergedNews = array_merge($sort, $other);
        // 计算总页数
        $totalRows = count($mergedNews);
        //ceil 向上取整
        $totalPages = ceil($totalRows / $pageSize);
        //从get请求中获取当前页码。如果没有page参数，默认使用1
        $currentPage = input('get.page/d', 1);
        //计算页码偏移量
        $offset = ($currentPage - 1) * $pageSize;
        //array_slice 截取当前页的数据
        $pagedNews = array_slice($mergedNews, $offset, $pageSize);
        // 将分页信息传递到视图
        $this->assign('news', $pagedNews);
        $this->assign('totalPages', $totalPages);
        $this->assign('currentPage', $currentPage);
        // 模拟上一页、下一页的url      
        $this->assign('prevPageUrl', ($currentPage > 1) ? "?page=".($currentPage-1) : '');
        $this->assign('nextPageUrl', ($currentPage < $totalPages) ? "?page=".($currentPage+1) : '');
        return $this->fetch();
    }
}