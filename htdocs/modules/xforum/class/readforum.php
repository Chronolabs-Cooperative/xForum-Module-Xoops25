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

class Readforum extends Read 
{
    function __construct()
    {
        parent::__construct("forum");
    }
}

class xforumReadforumHandler extends xforumReadHandler
{
    function __construct($db) {
        parent::__construct($db, "forum");
    }
    
    /**
     * clean orphan items from database
     * 
     * @return 	bool	true on success
     */
    function cleanOrphan()
    {
	    parent::cleanOrphan($this->db->prefix("xf_posts"), "post_id");
		return parent::cleanOrphan($this->db->prefix("xf_forums"), "forum_id", "read_item");
    }    
    
    function setRead_items($status = 0, $uid = null)
    {
	    if(empty($this->mode)) return true;
	    
	    if($this->mode == 1) return $this->setRead_items_cookie($status);
	    else return $this->setRead_items_db($status, $uid);
    }
        
    function setRead_items_cookie($status, $items)
    {
	    $cookie_name = "LF";
		$items = array();
		if(!empty($status)):
		$item_handler = xoops_getmodulehandler('forum', 'xforum');
		$items_id = $item_handler->getIds();
		foreach($items_id as $key){
			$items[$key] = time();
		}
		endif;
		forum_setcookie($cookie_name, $items);
		return true;
    }
    
    function setRead_items_db($status, $uid)
    {
	    if(empty($uid)){
		    if(is_object($GLOBALS["xoopsUser"])){
			    $uid = $GLOBALS["xoopsUser"]->getVar("uid");
		    }else{
			    return false;
		    }
	    }
	    if(empty($status)){
			$this->deleteAll(new Criteria("uid", $uid));
		    return true;
	    }

		$item_handler = xoops_getmodulehandler('forum', 'xforum');
		$items_obj = $item_handler->getAll(null, array("forum_last_post_id"));
		foreach(array_keys($items_obj) as $key){
			$this->setRead_db($key, $items_obj[$key]->getVar("forum_last_post_id"), $uid);
		}
		unset($items_obj);
		
		return true;
    }
}
?>