<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use app\common\model\Photo;
class PhotoController extends Controller{
    public function add(Request $request) {
        $type = $this->request->param('type');
       
        session('type', $type);
        //增加按钮增加页面
        return $this->fetch();
    }
    
    public function delete() {

    }

    public function edit() {
        return $this->fetch();
    }

    public function index(Request $request) {
        $photoType = [];
        $Photo = new Photo();
        $types = $Photo->column('type', 'type');     
        //遍历每一种类型
        foreach ($types as $typeItem) {
            //查询属于该类型的图片
            $photos = $Photo->where('type', $typeItem)
                            ->order('order', 'asc')
                            ->select();
            //将查询结果放到$photoByType数组
           
            $photoByType[$typeItem] = $photos;
           
        }
      
        //photoByType传递给模板的变量，与{volist name='photoByType'} 相呼应
       
        $this    ->assign('photos', $photos);
        return $this->fetch();
    }

    /**
     * 上传插入一张照片
     **/
    public function insert() {      
        $file = request()->file('file');     
        if ($file) {
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'photo');
            if ($info) {
                $type = session('type');
                //$info方法返回的名称
                $getPath = $info->getSaveName();
                //将\替换为/ 一个\代表转义字符，使用两个\\告诉php将其视为普通反斜杠
                $savePath = str_replace('\\', '/', $getPath);
                $path = '/thinkphp5guide/public/uploads/photo/' . $savePath;   
                $uploadDate = request()->post('UploadDate');
                $extension = $info->getExtension();
                $photo = new photo();
                $data['extension'] = $extension;
                $data['UploadDate'] = $uploadDate;
                $data['type'] = $type;
                $data['path'] = $path;
                $photo->save($data);
                return $this->success('上传成功', url('Photo/index'));
            }
        } else {
            return $this->error('上传失败', url('Photo/index'));
        }        
    }

    //更新数据，处理从edit表单传过来的东西
    public function update() {

    }
    
   
}