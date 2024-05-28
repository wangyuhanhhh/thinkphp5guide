<?php
namespace app\index\controller; //指出该文件的位置
use think\Controller;   //用于向V层进行数据的传递
use think\Db;
use app\common\model\Web;   //首页模型
use think\Request;  //引用Request
use app\common\model\News;
use app\common\model\Notice;
use app\common\model\Experiment;
use app\common\model\Download;
use app\common\model\Photo;
use app\common\model\Lab;

/**
 * 网页首页，继承think\Controller后，就可以利用V层对数据进行打包了
 */
class WebController extends Controller
{

    public function index()
    {
        //查询数据(部分)用于显示
        $noticeList = Notice::limit(5)->select();
        $experimentList = Experiment::limit(6)->select();
        $downloadList = Download::limit(5)->select();
        $BigPhotoList = Db::name('photo')->where('type', 1)->select();
        $SmallPhotoList = Db::name('photo')->where('type', 0)->select();
        $lab = Lab::limit(1)->select();
     
        // 从数据库中获取新闻数据
        $topNewsList = News::where('Sort', 1)->order('time', 'desc')->select();
        $normalNewsList = News::where('Sort', 0)->order('time', 'desc')->select();

        //确保查询结果不为空
        if($topNewsList && $normalNewsList) {
            // 合并置顶和非置顶新闻数据
            $newsList = array_merge($topNewsList, $normalNewsList);
        } elseif ($topNewsList) {
            $newsList = $topNewsList; 
        } elseif ($normalNewsList) {
            $newsList = $normalNewsList;
        } else {
            //如果两个查询都为空，直接复制为空数组
            $newsList = [];
        }
        
        // 只取前6条数据
        $newsList = array_slice($newsList, 0, 6);

        //将查询的内容传给V层
        $this->assign('newsList', $newsList);
        $this->assign('noticeList', $noticeList);
        $this->assign('experimentList', $experimentList);
        $this->assign('downloadList', $downloadList);
        $this->assign('BigPhotoList', $BigPhotoList);
        $this->assign('SmallPhotoList', $SmallPhotoList);
        $this->assign('lab', $lab);
        //创建Web对象
        $Web = new Web; 
        $webs = $Web->select();

        // 向V层传数据
        $this->assign('webs', $webs);

        // 取回打包后的数据
        $htmls = $this->fetch();

        // 将数据返回给用户
        return $htmls;
    }
}