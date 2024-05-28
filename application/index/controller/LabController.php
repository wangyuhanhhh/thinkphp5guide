<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\common\model\Lab;
use think\Db;

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

    public function detail($id) {
        
        //根据id从数据库中检索简介数据
        $lab = Db::name('lab')->find($id);
      
        //获取简介列表(desc降序排列;asc升序排列)
        $labList = Lab::order('id', 'asc')->select();
        
        // 初始化当前位置变量
        $currentPosition = false;

        // 初始化上一条和下一条简介的ID
        $prevId = null;
        $nextId = null;
        $prevIndex = 0;
        $nextIndex = 0;
    
       // 找出当前简介在列表中的位置
        foreach ($labList as $key => $item) {
            if ($item['id'] == $id) {
                $currentPosition = $key;
                break;
            }
        }

        //如果没有找到(可能是因为列表不完整或当前简介不在列表中)，则默认为第一条
        if ($currentPosition === false) {
            $nextId = $labList[0]['id'];
        } else {
            //获取下一条简介的键值(注意检查边界条件)
            $nextIndex = $currentPosition + 1;

            //获取上一条简介的键值
            $prevIndex = $currentPosition - 1;

            if($prevIndex >= 0) {
                $prevId = $labList[$prevIndex]['id'];
            } else {
                //如果已经是第一条简介了，设置默认
                $prevId = null;
            }
            
            if (isset($labList[$nextIndex])) {
                $nextId = $labList[$nextIndex]['id'];
            } else {
                //如果没有下一个简介，设置默认提示
                $nextId = null;
            }
        }

        //传数据到模版
        $this->assign('lab', $lab);
        $this->assign('nextIndex', $nextIndex);
        $this->assign('prevIndex', $prevIndex);
        $this->assign('prevId', $prevId);
        $this->assign('nextId', $nextId);
        $this->assign('labList', $labList);

        if(!$lab) {
            //如果找不到简介，抛出异常
            $this->error('简介不存在');
        }

        //将简介数据传给V层，同时渲染出模版
        return $this->fetch();

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
                if ($putTime <= $formattedDate) {
                    //插入信息
                    $Lab->title = $postData['title'];
                    $Lab->author = $postData['author'];
                    $Lab->content = $postData['content'];
                    $path = '/thinkphp5guide/public/uploads/photo/' . $savePath;
                    $Lab->photo_path = $path;
                    $Lab->time = $postData['time'];
                    $result = $Lab->validate(true)->save();
                    if($result === false) {
                        return $this->error('数据添加错误：' . $Lab->getError());
                    } else {
                        return $this->success('新增成功', url('Lab/upload'));
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
                //获取当前时间戳
                $currentTime = time();
                //将时间戳转化为'y-m-d'型   
                $formattedDate = date('Y-m-d', $currentTime);
                $postData = Request::instance()->post(); 
                $putTime = $postData['time'];
                // 将换行符转换为<br>标签
                $postData['content'] = nl2br($postData['content']);
                if ($putTime <= $formattedDate) {
                    // 更新
                    $lab->photo_path = $path;
                    $lab->title = Request::instance()->post('title');
                    $lab->author = Request::instance()->post('author');
                    $lab->content = Request::instance()->post('content');
                    $lab->time = Request::instance()->post('time');
                    $lab->submitTime = $currentTime;
                    $result = $lab->validate(true)->save();
                    if($result === false) {
                        return $this->error('数据添加错误：' . $lab->getError());
                    } else {
                        return $this->success('新增成功', url('Lab/upload'));
                    }
                } else {
                    return $this->error('请选择今天或之前的时间', url('edit'));
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