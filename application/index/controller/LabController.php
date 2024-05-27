<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\common\model\Lab;

class LabController extends Controller {
    public function add() {
        return $this->fetch();
    }

    public function delete() {
        //获取传入的id值
        $id = Request::instance()->param('id/d');
        if (is_null($id) || 0 === $id) {
            return $this->error('为获取到ID信息');
        }
        //获取要删除的对象
        $Lab = Lab::get($id);
        //要删除的对象不存在
        if (is_null($Lab)) {
            return $this->error('不存在id为' . $id . '的简介，删除失败');
        }

        //删除对象
        if (!$Lab->delete()) {
            return $this->error('删除失败：' . $Lab->getError());
        }
        //跳转
        return $this->success('删除成功', url('upload'));
    }

    public function edit() {
        //获取传入id
        $id = Request::instance()->param('id/d');
        
        //在表模型中获取当前记录
        if (is_null( $lab = lab::get($id))) {
            return '未找到ID为' . $id . '的记录';
        }

        //将数据传给V层
        $this->assign('Lab', $lab);
        //获取封装好的V层内容并返回给客户
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

    public function update() {
        // 接收数据
        $id = Request::instance()->post('id/d');
        $file = request()->file('file');
        // 获取当前对象
        $lab = Lab::get($id);
        if (is_null($lab)) {
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
                $lab->photo_path = $path;
                $lab->title = Request::instance()->post('title');
                $lab->author = Request::instance()->post('author');
                $lab->content = Request::instance()->post('content');
                $lab->time = Request::instance()->post('time');
                //获取当前时间戳
                $currentTime = time();
                $lab->submitTime = $currentTime;
                //更新数据
                if ($lab->save()) {
                    return $this->success('编辑成功', url('upload'));
                } 
            } else {
                throw new \Exception("所更新的记录不存在", 1);
            }
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