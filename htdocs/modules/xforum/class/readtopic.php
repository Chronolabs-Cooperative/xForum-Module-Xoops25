<?php

// $Id: moderate.php,v 4.03 2008/06/05 16:23:33 wishcraft Exp $

include_once dirname(__FILE__).'/read.php';

/**
 * A handler for read/unread handling
 * 
 * @package     xforum/X-Forum
 * 
 * @author	    S.A.R. (wishcraft, http://www.chronolabs.org)
 * @copyright	copyright (c) 2005 XOOPS.org
 */

class Readtopic extends Read 
{
    function __construct()
    {
        parent::__construct("topic");
    }
}

class xforumReadtopicHandler extends xforumReadHandler
{
    /**
     * maximum records per forum for one user.
     * assigned from $GLOBALS['xforumModuleConfig']["read_items"]
     *
     * @var integer
     */
	var $items_per_forum;
	
    function __construct($db) {
        parent::__construct($db, "topic");
	    $xforumConfig = forum_load_config();
        $this->items_per_forum = isset($xforumConfig["read_items"])?intval($xforumConfig["read_items"]):100;
    }
    
    /**
     * clean orphan items from database
     * 
     * @return 	bool	true on success
     */
    function cleanOrphan()
    {
	    parent::cleanOrphan($this->db->prefix("xf_posts"), "post_id");
	    return parent::cleanOrphan($this->db->prefix("xf_topics"), "topic_id", "read_item");
    }    

    /**
     * Clear garbage
     * 
     * Delete all expired and duplicated records
     */
    function clearGarbage(){
	    parent::clearGarbage();
	    
	    // TODO: clearItemsExceedMaximumItemsPerForum
        return true;
    }
    
    function setRead_items($status = 0, $forum_id = 0, $uid = null)
    {
	    if(empty($this->mode)) return true;
	    
	    if($this->mode == 1) return $this->setRead_items_cookie($status, $forum_id);
	    else return $this->setRead_items_db($status, $forum_id, $uid);
    }
        
    function setRead_items_cookie($status, $forum_id)
    {
	    $cookie_name = "LT";
	    $cookie_vars = forum_getcookie($cookie_name, true);
	    
		$item_handler = xoops_getmodulehandler('topic', 'xforum');
		$criteria = new CriteriaCompo(new Criteria("forum_id", $forum_id));
		$criteria->setSort("topic_last_post_id");
		$criteria->setOrder("DESC");
		$criteria->setLimit($this->items_per_forum);
		$items = $item_handler->getIds($criteria);
	    
	    foreach($items as $var){
		    if(empty($status)){
			    if(isset($cookie_vars[$var])) unset($cookie_vars[$var]);
		    }else{
			    $cookie_vars[$var] = time() /*$items[$var]*/;
		    }
	    }
		forum_setcookie($cookie_name, $cookie_vars);
		return true;
    }
    
    function setRead_items_db($status, $forum_id, $uid)
    {
	    if(empty($uid)){
		    if(is_object($GLOBALS["xoopsUser"])){
			    $uid = $GLOBALS["xoopsUser"]->getVar("uid");
		    }else{
			    return false;
		    }
	    }
	    
		$item_handler = xoops_getmodulehandler('topic', 'xforum');
		$criteria_topic = new CriteriaCompo(new Criteria("forum_id", $forum_id));
		$criteria_topic->setSort("topic_last_post_id");
		$criteria_topic->setOrder("DESC");
		$criteria_topic->setLimit($this->items_per_forum);
		$criteria_sticky = new CriteriaCompo(new Criteria("forum_id", $forum_id));
		$criteria_sticky->add(new Criteria("topic_sticky", 1));
	
	    if(empty($status)){		    
			$items_id = $item_handler->getIds($criteria_topic);
			$sticky_id = $item_handler->getIds($criteria_sticky);
			$items =  $items_id+$sticky_id;
			$criteria = new CriteriaCompo(new Criteria("uid", $uid));
			$criteria->add(new Criteria("read_item", "(".implode(", ", $items).")", "IN"));
			$this->deleteAll($criteria, true);
		    return true;
	    }
		
		$items_obj = $item_handler->getAll($criteria_topic, array("topic_last_post_id"));
		$sticky_obj = $item_handler->getAll($criteria_sticky, array("topic_last_post_id"));
		$items_obj = $items_obj + $sticky_obj;
		$items = array();
		foreach(array_keys($items_obj) as $key){
			$items[$key] = $items_obj[$key]->getVar("topic_last_post_id");
		}
		unset($items_obj, $sticky_obj);
		foreach(array_keys($items) as $key){
			$this->setRead_db($key, $items[$key], $uid);
		}
		return true;
    }
}
?>