<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\common\model\Lab;

class LabController extends Controller {
    public function add() {
        return $this->fetch();
    }

    public function index() {
        $Lab = new Lab();
        //photos与{volist name="photos"}对应
        $lab = $Lab->select();
        $this->assign('lab', $lab);
        return $this->fetch();
    }

    public function insert() {
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
                $Lab = new Lab();
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
                    $Lab->title = $postData['title'];
                    $Lab->author = $postData['author'];
                    $Lab->content = $postData['content'];
                    $path = '/thinkphp5guide/public/uploads/photo/' . $savePath;
                    $Lab->photo_path = $path;
                    $Lab->time = $postData['time'];
                    if ($Lab->save()) {
                        return $this->success('新增成功', url('Lab/upload'));
                    } else {
                        return $this->error('保存失败', url('Lab/upload'));
                    }
                } else {
                        return $this->error('请选择今天或之前的时间', url('Lab/add'));
                }
            } else {
                return $this->error('上传失败请重试', url('Lab/add'));
            }
        } else {
            return $this->error('未上传文件', url('Lab/upload'));
        }   
    }

    public function upload() {
        $Lab = new Lab();
        //photos与{volist name="photos"}对应
        $lab = $Lab->select();
        $this->assign('lab', $lab);
        return $this->fetch();
    }
}