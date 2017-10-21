<?php
/**
 * 套餐订单模型
 * @author xiewei <master@xiew.net>
 * @version 1.0
 */
class ZySchoolOrderAlbumModel extends Model{

    static protected $albumIds = array();

    /**
     * 通过套餐订单ID取得套餐ID
     * @param integer $id 要查询的订单编号ID
     * return mixed 成功时返回套餐ID，失败时返回false
     */
    public function getAlbumIdById($id){
        if(!isset(self::$albumIds[$id])){
            self::$albumIds[$id] = self::$albumIds[$id] = $this->where(array('id'=>$id))->getField('album_id');
            if(!self::$albumIds[$id]) self::$albumIds[$id] = false;
        }
        return self::$albumIds[$id];
    }


    /**
     * 取得套餐订单ID，根据用户ID和套餐ID
     * @param integer $uid 用户UID
     * @param integer $albumId 套餐ID
     * @return integer|false 返回对应的套餐订单ID，如果失败则返回false
     */
    public function getAlbumOrderId($uid, $albumId){
        $id = $this->where(array('uid'=>$uid, 'album_id'=>$albumId))->getField('id');
        return $id ? $id : false;
    }
}