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
    
    public function index() {
        $Photo = new Photo();
        $photo = $Photo->select();
        var_dump($photo);
        die();
        return $this->fetch();
    }
    /**
     * 上传插入一张照片
     **/
    public function insert() {      
        $file = request()->file('file');     
        if ($file) {
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . 'photo');
            if ($info) {
                $uploadDate = request()->post('UploadDate');
                $extension = $info->getExtension();
                $photo = new photo();
                $data['extension'] = $extension;
                $data['UploadDate'] = $uploadDate;
                $photo->save($data);
            }
        }        
    }
    /**
     * 上传插入多张图片
    */
    public function upload() {
        $files = request()->file('file');     
        foreach($files as $file) {
            //移动到public\uploads\
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . 'photo');
            if ($info) {
                $uploadDate = request()->post('UploadDate');
                $extension = $info->getExtension();
                //$fileName = $info->getSaveName();
                $photo = new photo();
                $data['extension'] = $extension;
                $data['UploadDate'] = $uploadDate;
                $photo->save($data);
            }
        }
    }
}