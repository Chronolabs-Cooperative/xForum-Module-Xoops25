<?php

// $Id: moderate.php,v 4.03 2008/06/05 16:23:33 wishcraft Exp $

 
if (!defined("XOOPS_ROOT_PATH")) {
	exit();
}

/**
 * A handler for read/unread handling
 * 
 * @package     xforum/X-Forum
 * 
 * @author	    S.A.R. (wishcraft, http://www.chronolabs.org)
 * @copyright	copyright (c) 2005 XOOPS.org
 */

class Read extends XoopsObject 
{
    function __construct($type)
    {
        $this->initVar('read_id', XOBJ_DTYPE_INT);
        $this->initVar('uid', XOBJ_DTYPE_INT);
        $this->initVar('read_item', XOBJ_DTYPE_INT);
        $this->initVar('post_id', XOBJ_DTYPE_INT);
        $this->initVar('read_time', XOBJ_DTYPE_INT);
    }
}

class xforumReadHandler extends XoopsPersistableObjectHandler
{
    /**
     * Object type.
     * <ul>
     *  <li>forum</li>
     *  <li>topic</li>
     * </ul>
     *
     * @var string
     */
	var $type;
	
    /**
     * seconds records will persist.
     * assigned from $GLOBALS['xforumModuleConfig']["read_expire"]
     * <ul>
     *  <li>0 = never records</li>
     *  <li>-1 = never expires</li>
     * </ul>
     *
     * @var integer
     */
	var $lifetime;
	
    /**
     * storage mode for records.
     * assigned from $GLOBALS['xforumModuleConfig']["read_mode"]
     * <ul>
     *  <li>0 = never records</li>
     *  <li>1 = uses cookie</li>
     *  <li>2 = stores in database</li>
     * </ul>
     *
     * @var integer
     */
	var $mode;
	
    function xforumReadHandler($db, $type) {
	    $type = ("forum" == $type) ? "forum" : "topic";
        parent::__construct($db, 'xf_reads_'.$type, 'Read'.$type, 'read_id', 'post_id');
        $this->type = $type;
	    $xforumConfig = forum_load_config();
        $this->lifetime = !empty($xforumConfig["read_expire"]) ? $xforumConfig["read_expire"] *24*3600 : 30*24*3600;
        $this->mode = isset($xforumConfig["read_mode"]) ? $xforumConfig["read_mode"] : 2;
    }

    /**
     * Clear garbage
     * 
     * Delete all expired and duplicated records
     */
    function clearGarbage(){
	    $expire = time() - intval($this->lifetime);
		$sql = "DELETE FROM ".$this->table." WHERE read_time < ". $expire;
        $this->db->queryF($sql);
	    
    	/* for MySQL 4.1+ */
    	if($this->mysql_major_version() >= 4):
        $sql = 	"DELETE bb FROM ".$this->table." AS bb".
        		" LEFT JOIN ".$this->table." AS aa ON bb.read_item = aa.read_item ".
        		" WHERE aa.post_id > bb.post_id";
        else:
        // for 4.0+
        $sql = 	"DELETE ".$this->table." FROM ".$this->table.
        		" LEFT JOIN ".$this->table." AS aa ON ".$this->table.".read_item = aa.read_item ".
        		" WHERE aa.post_id > ".$this->table.".post_id";
		endif;
        if (!$result = $this->db->queryF($sql)) {
            xoops_error($this->db->error());
            return false;
        }
        return true;
    }

    function getRead($read_item, $uid = null)
    {
	    if(empty($this->mode)) return null;
	    if($this->mode == 1) return $this->getRead_cookie($read_item);
	    else return $this->getRead_db($read_item, $uid);
    }
        
    function getRead_cookie($item_id)
    {
	    $cookie_name = ($this->type == "forum")?"LF":"LT";
	    $cookie_var = $item_id;
		$lastview = forum_getcookie($cookie_name);
		return @$lastview[$cookie_var];
    }
    
    function getRead_db($read_item, $uid)
    {
	    if(empty($uid)){
		    if(is_object($GLOBALS["xoopsUser"])){
			    $uid = $GLOBALS["xoopsUser"]->getVar("uid");
		    }else{
			    return false;
		    }
	    }
	    $sql = "SELECT post_id ".
	    		" FROM ".$this->table.
	    		" WHERE read_item = ".intval($read_item).
	    		" 	AND uid = ".intval($uid);
	    if(!$result = $this->db->queryF($sql, 1)){
		    return null;
	    }
	    list($post_id) = $this->db->fetchRow($result);
	    return $post_id;
    }
    
    function setRead($read_item, $post_id, $uid = null)
    {
	    if(empty($this->mode)) return true;
	    if($this->mode == 1) return $this->setRead_cookie($read_item, $post_id);
	    else return $this->setRead_db($read_item, $post_id, $uid);
    }
        
    function setRead_cookie($read_item, $post_id)
    {
	    $cookie_name = ($this->type == "forum") ? "LF" : "LT";
		$lastview = forum_getcookie($cookie_name, true);
		$lastview[$read_item] = time();
		forum_setcookie($cookie_name, $lastview);
    }
    
    function setRead_db($read_item, $post_id, $uid)
    {
	    if(empty($uid)){
		    if(is_object($GLOBALS["xoopsUser"])){
			    $uid = $GLOBALS["xoopsUser"]->getVar("uid");
		    }else{
			    return false;
		    }
	    }
	    
	    $sql = "UPDATE ".$this->table.
	    		" SET post_id = ".intval($post_id).",".
	    		" 	read_time =".time().
	    		" WHERE read_item = ".intval($read_item).
	    		" 	AND uid = ".intval($uid);
	    if($this->db->queryF($sql) && $this->db->getAffectedRows()){
		    return true;
	    }
	    $object = $this->create();
	    $object->setVar("read_item", $read_item, true);
	    $object->setVar("post_id", $post_id, true);
	    $object->setVar("uid", $uid, true);
	    $object->setVar("read_time", time(), true);
	    return parent::insert($object);
    }
    
    function isRead_items($items, $uid = null)
    {
	    $ret = null;
	    if(empty($this->mode)) return $ret;
	    
	    if($this->mode == 1) $ret = $this->isRead_items_cookie($items);
	    else $ret = $this->isRead_items_db($items, $uid);
	    return $ret;
    }
        
    function isRead_items_cookie($items)
    {
	    $cookie_name = ($this->type == "forum")?"LF":"LT";
	    $cookie_vars = forum_getcookie($cookie_name, true);
	    
	    $ret = array();
	    foreach($items as $key => $last_update){
            $ret[$key] = (max(@$GLOBALS['last_visit'], @$cookie_vars[$key]) >= $last_update);
	    }
	    return $ret;
    }
    
    function isRead_items_db($items, $uid)
    {
	    $ret = array();
	    if(empty($items)) return $ret;
	    
	    if(empty($uid)){
		    if(is_object($GLOBALS["xoopsUser"])){
			    $uid = $GLOBALS["xoopsUser"]->getVar("uid");
		    }else{
			    return $ret;
		    }
	    }
	    
		$criteria = new CriteriaCompo(new Criteria("uid", $uid));
		$criteria->add(new Criteria("read_item", "(".implode(", ", array_map("intval", array_keys($items))).")", "IN"));
		$items_obj = $this->getAll($criteria, array("read_item", "post_id"));
	    
		$items_list = array();
	    foreach(array_keys($items_obj) as $key){
            $items_list[$items_obj[$key]->getVar("read_item")] = $items_obj[$key]->getVar("post_id");
	    }
		unset($items_obj);
	    
	    foreach($items as $key => $last_update){
            $ret[$key] = (@$items_list[$key] >= $last_update);
	    }
		return $ret;
    }
    
}
?>