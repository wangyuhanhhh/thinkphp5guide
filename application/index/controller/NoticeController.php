<?php
namespace app\index\controller;

use think\Controller;
use app\common\model\Notice;
use think\Request;
use think\Db;

class NoticeController extends Controller
{
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
}
