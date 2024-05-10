<?php
namespace app\common\model;
use think\Model;

class News extends Model {
    protected $autoWriteTimestamp = false;
    public function top($id){
        //查询当前最大的sort值
        $maxSort = $this->where('NewsId', '<>', $id)->max('Sort');
        //设置待置顶的新闻sort值为当前最大sort值加一
        $data = [
            'Sort' => $maxSort === null ? 1 : ($maxSort + 1),//如果没有其它新闻，这条是第一条
        ];
        // 更新指定id的新闻纪录
        $result = $this->where('NewsId',$id)->update($data);
        return $result !== false;
    }  
}
