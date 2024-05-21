<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use app\common\model\Photo;
class PhotoController extends Controller{
    public function add() {
        //增加按钮增加页面
        return $this->fetch();
    }
    
    public function delete() {
        //获取传入的id值
        $id = Request::instance()->param('id/d');
        if (is_null($id) || 0 === $id) {
            return $this->error('为获取到ID信息');
        }
        //获取要删除的对象
        $photo = Photo::get($id);
        //要删除的对象不存在
        if (is_null($photo)) {
            return $this->error('不存在id为' . $id . '的新闻，删除失败');
        }

        //删除对象
        if (!$photo->delete()) {
            return $this->error('删除失败：' . $photo->getError());
        }
        //跳转
        return $this->success('删除成功', url('Photo/index'));
    }

    //对应编辑表单
    public function edit($id) {
        $id = Request::instance()->param('id/d');

        if ($id <= 0) {
           return $this->error('无效的图片ID');
        }
        // 根据$id获取单个图片记录
        $Photo = new Photo();
        $photo = $Photo->find($id); // find() 用于根据主键查找单条记录
        // 检查图片是否存在
        if (!$photo) {
            $this->error('图片未找到');
        }
        $this->assign('photo', $photo);
        return $this->fetch();
    }
    public function index() {
        $Photo = new Photo();
        //photos与{volist name="photos"}对应
        $photo = $Photo->select();
        $this->assign('photos', $photo);
        return $this->fetch();
    }
    
    /**
     * $getPath 表单传来的值
     * $savePath 将\替换成/的地址
     * $path保存到数据库的地址
     **/
    public function insert() {      
        $file = request()->file('file'); 
        //获取表单传过来的type值
        $type = request()->param('type');   
        if ($file) {
            // 移动到/public/uploads/ 目录
            $info = $file->validate([
                'ext' => 'jpg,jpeg,png,gif'
            ])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'photo');
            if ($info) {               
                $getPath = $info->getSaveName();
                //将\替换为/ 一个\代表转义字符，使用两个\\告诉php将其视为普通反斜杠
                $savePath = str_replace('\\', '/', $getPath);
                $path = '/thinkphp5guide/public/uploads/photo/' . $savePath;
                $uploadDate = request()->post('UploadDate');
                $photo = new photo();
                $data['UploadDate'] = $uploadDate;
                $data['path'] = $path;
                $data['type'] = $type;
                $photo->save($data);
                return $this->success('成功上传图片', url('Photo/index'));
            } else {
                return $this->error('新增失败', url('Photo/index'));
            }
        }
        else {
            return $this->error('未选择图片', url('Photo/add'));
        }
    }

    //处理编辑表单传过来的信息
    public function update() {
        // 接收数据
        $id = Request::instance()->post('id/d');
        $file = request()->file('file');
        
        // 获取当前对象
        $photo = Photo::get($id);
        if (is_null($photo)) {
            return $this->error('所更新的记录不存在', url('Photo/index'));
        }

        // 文件上传
        if ($file) {
            $validate = [
                'ext' => 'jpg,jpeg,png,gif'
            ];
            $info = $file->validate($validate)->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'photo');
            if ($info) {
                $savePath = str_replace('\\', '/', $getPath);
                $path = '/thinkphp5guide/public/uploads/photo/' . $savePath;               
                // 更新
                $updateData = [
                    'UploadDate' => Request::instance()->post('UploadDate'),
                    'path' => $path,
                ];               
                // 保存
                if ($photo->save($updateData)) {
                    return $this->success('更新成功', url('Photo/index'));
                } else {
                    return $this->error('更新失败: ' . $photo->getError(), url('Photo/index'));
                }
            }
        } else {
            return $this->error('未接收到文件', url('Photo/index'));
        }
    }
}