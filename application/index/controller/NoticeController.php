<?php
namespace app\index\controller;

use think\Controller;
use app\common\model\Notice;
use think\Request;
use think\Db;

class NoticeController extends Controller
{
    public function add() {
        return $this->fetch();
    }

    public function index()
    {
        //查询数据并启用分页
        $pageSize = 5;
        $Notice = new Notice();
        $notices = $Notice->paginate($pageSize);

        //向V层传数据
        $this->assign('notices', $notices);

        //取回数据
        $htmls = $this->fetch();

        //返回至V层
        return $htmls;

    }

    /**
     * 根据公告id获取对应具体内容，并且渲染模版
     */
    public function detail($id) 
    {
        //根据对应id查询数据
        $notices = Db::name('notice')->find($id);

        //获取公告列表(asc升序)
        $noticeList = Db::name('notice')->order('id', 'asc')->select();

        //获取当前公告在列表中的键值(位置)
        $currentPosition = array_search($notices, $noticeList, true);

        //如果没有找到，默认为第一条
        if ($currentPosition === false) {
            $nextId = $noticeList[0]['id'];
        } else {
            //获取下一条公告的键值
            $nextIndex = $currentPosition + 1;

            //获取上一条公告的键值
            $prevIndex = $currentPosition - 1;

            if ($prevIndex >= 0) {
                $prevId = $noticeList[$prevIndex]['id'];
            } else {
                //如果已经是第一条公告了，设置默认提示
                $prevId = null;
            }

            if (isset($noticeList[$nextIndex])) {
                $nextId = $noticeList[$nextIndex]['id'];
            } else {
                //如果已经是最后一条公告了，设置默认提示
                $nextId = null;
            }
        }

        //传数据到模版
        $this->assign('notices', $notices);
        $this->assign('nextIndex', $nextIndex);
        $this->assign('prevIndex', $prevIndex);
        $this->assign('prevId', $prevId);
        $this->assign('nextId', $nextId);
        $this->assign('noticeList', $noticeList);
        
        if(!$notices) {
            $this->error('未查询到该公告具体内容');
        }

        //将查询结果返回V层，同时渲染模版
        return $this->fetch('detail', ['notices' => $notices]);
    }

    public function delete() {
        //获取传入的id值
        $id = Request::instance()->param('id/d');
        if (is_null($id) || 0 === $id) {
            return $this->error('为获取到ID信息');
        }
        //获取要删除的对象
        $Notice = Notice::get($id);
        //要删除的对象不存在
        if (is_null($Notice)) {
            return $this->error('不存在id为' . $id . '的新闻，删除失败');
        }

        //删除对象
        if (!$Notice->delete()) {
            return $this->error('删除失败：' . $Notice->getError());
        }
        //跳转
        return $this->success('删除成功', url('upload'));
    }
    public function edit() {
        //获取传入id
        $id = Request::instance()->param('id/d');
        
        //在表模型中获取当前记录
        if (is_null( $Notice = Notice::get($id))) {
            return '未找到ID为' . $id . '的记录';
        }

        //将数据传给V层
        $this->assign('Notice', $Notice);
        //获取封装好的V层内容并返回给客户
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
            }
        }   

        //保存文字。接收传入数据
        $postData = Request::instance()->post();       
        //实例化空对象
        $Notice = new Notice();
        //插入信息
        $Notice->title = $postData['title'];
        $Notice->author = $postData['author'];
        $Notice->content = $postData['content'];
        $Notice->photo_path = $path;
        $Notice->time = $postData['time'];
        //保存                
        $Notice->save();
        return $this->success('新增成功', url('Notice/upload'));
    }
    
    public function setTop() {
        $postData = Request::instance()->post();
        $NoticeId = $postData['id'];
        if (is_null($NoticeId)) {
            return $this->error('请选择要置顶的新闻', url('upload'));
        }
        $Notice  = new Notice();
        //调用M层中的top方法
        $result = $Notice->top($NoticeId);
        if ($result) {
            return $this->success('置顶成功', url('upload'));
        } else {
            return $this->error('置顶失败请重试');
        }
    }

    public function update() {
        //接收数据
        $id = Request()->instance()->post('id/d');
        
        //获取当前对象
        $Notice = Notice::get($id);
        if (!is_null($Notice)) {
            $Notice->title = Request::instance()->post('title');
            $Notice->time = Request::instance()->post('time');
            //更新数据
            if (false === $Notice->save()) {
                return '更新失败' . $Notice->getError();
            } 
        } else {
            throw new \Exception("所更新的记录不存在", 1);
        }
        return $this->success('编辑成功', url('upload'));
    }

    public function upload() {
        $Notice = new Notice();
        $notice = Notice::order('Sort', 'desc')->select();
        $this->assign('notice', $notice);
        return $this->fetch();
    }
}
