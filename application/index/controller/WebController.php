<?php
namespace app\index\controller; //指出该文件的位置
use think\Controller;   //用于向V层进行数据的传递
use app\common\model\Web;   //首页模型
use think\Request;  //引用Request

/**
 * 网页首页，继承think\Controller后，就可以利用V层对数据进行打包了
 */
class WebController extends Controller
{

    public function index()
    {
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