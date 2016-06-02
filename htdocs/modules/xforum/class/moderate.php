<?php

// $Id: moderate.php,v 4.03 2008/06/05 16:23:33 wishcraft Exp $

 
if (!defined("XOOPS_ROOT_PATH")) {
	exit();
}

/**
 * A handler for User moderation management
 * 
 * @package     xforum/X-Forum
 * 
 * @author	    S.A.R. (wishcraft, http://www.chronolabs.org)
 * @copyright	copyright (c) 2005 XOOPS.org
 */

class Moderate extends XoopsObject {

    function __construct()
    {
        $this->initVar('mod_id', XOBJ_DTYPE_INT);
        $this->initVar('mod_start', XOBJ_DTYPE_INT);
        $this->initVar('mod_end', XOBJ_DTYPE_INT);
        $this->initVar('mod_desc', XOBJ_DTYPE_TXTBOX);
        $this->initVar('uid', XOBJ_DTYPE_INT);
        $this->initVar('ip', XOBJ_DTYPE_TXTBOX);
        $this->initVar('forum_id', XOBJ_DTYPE_INT);
    }
}

class xforumModerateHandler extends XoopsPersistableObjectHandler
{
    function __construct($db) {
        parent::__construct($db, 'xf_moderates', 'Moderate', 'mod_id', 'uid');
    }

    /**
     * Clear garbage
     * 
     * Delete all moderation information that has expired
     * 
     * @param	int $expire Expiration time in UNIX, 0 for time()
     */
    function clearGarbage($expire=0){
	    $expire = time() - intval($expire);
		$sql = sprintf("DELETE FROM %s WHERE mod_end < %u", $this->db->prefix('xf_moderates'), $expire);
        $this->db->queryF($sql);
    }
    
    /**
     * Check if a user is moderated, according to his uid and ip
     * 
     * 
     * @param	int 	$uid user id
     * @param	string 	$ip user ip
     */
    function verifyUser($uid=-1, $ip="", $xforum = 0){	    
		if(!empty($GLOBALS['xforumModuleConfig']['cache_enabled'])){
			$xforums = $this->forumList($uid, $ip);
			return in_array($xforum, $xforums);
		}
	    $uid = ($uid<0)?(is_object($GLOBALS["xoopsUser"])?$GLOBALS["xoopsUser"]->getVar("uid"):0):$uid;
	    $uid_criteria = empty($uid)?"1=1":"uid=".intval($uid);
	    $ip = empty($ip)?forum_getIP(true):$ip;
	    if(!empty($ip)){
		    $ip_segs = explode(".", $ip);
		    for($i=1; $i<=4; $i++){
			    $ips[] = $this->db->quoteString(implode(".", array_slice($ip_segs, 0, $i)));
		    }
	    	$ip_criteria = "ip IN(".implode(",", $ips).")";
	    }else{
	    	$ip_criteria = "1=1";
    	}
	    $forum_criteria = empty($xforum)?"forum_id=0":"forum_id=0 OR forum_id=".intval($xforum);
	    $expire_criteria = "mod_end > ".time();
		$sql = sprintf("SELECT COUNT(*) AS count FROM %s WHERE (%s OR %s) AND (%s) AND (%s)", $this->db->prefix('xf_moderates'), $uid_criteria, $ip_criteria, $forum_criteria, $expire_criteria);
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        $row = $this->db->fetchArray($result);
		return $row["count"];
    }
    
    /**
     * Get a forum list that a user is suspended, according to his uid and ip
     * Store the list into session if module cache is enabled
     * 
     * 
     * @param	int 	$uid user id
     * @param	string 	$ip user ip
     */
    function forumList($uid=-1, $ip=""){
	    static $xforums = array();
	    $uid = ($uid<0)?(is_object($GLOBALS["xoopsUser"])?$GLOBALS["xoopsUser"]->getVar("uid"):0):$uid;
	    $ip = empty($ip)?forum_getIP(true):$ip;
	    if(isset($xforums[$uid][$ip])){
		    return $xforums[$uid][$ip];
	    }
		if(!empty($GLOBALS['xforumModuleConfig']['cache_enabled'])){
			$xforums[$uid][$ip] = forum_getsession("sf".$uid."_".ip2long($ip), true);
			if(is_array($xforums[$uid][$ip]) && count($xforums[$uid][$ip])){
		    	return $xforums[$uid][$ip];
			}
		}
	    $uid_criteria = empty($uid)?"1=1":"uid=".intval($uid);
	    if(!empty($ip)){
		    $ip_segs = explode(".", $ip);
		    for($i=1; $i<=4; $i++){
			    $ips[] = $this->db->quoteString(implode(".", array_slice($ip_segs, 0, $i)));
		    }
	    	$ip_criteria = "ip IN(".implode(",", $ips).")";
	    }else{
	    	$ip_criteria = "1=1";
    	}
	    $expire_criteria = "mod_end > ".time();
		$sql = sprintf("SELECT forum_id, COUNT(*) AS count FROM %s WHERE (%s OR %s) AND (%s) GROUP BY forum_id", $this->db->prefix('xf_moderates'), $uid_criteria, $ip_criteria, $expire_criteria);
        if (!$result = $this->db->query($sql)) {
            return $xforums[$uid][$ip] = array();
        }
        $_forums = array();
        while($row = $this->db->fetchArray($result)){
	        if($row["count"]>0){
	        	$_forums[$row["forum_id"]] = 1; 
        	}
        }
        $xforums[$uid][$ip] = count($_forums)?array_keys($_forums):array(-1);
		if(!empty($GLOBALS['xforumModuleConfig']['cache_enabled'])){
			forum_setsession("sf".$uid."_".ip2long($ip), $xforums[$uid][$ip]);
		}
        
		return $xforums[$uid][$ip];
    }
    
    /**
     * Get latest expiration for a user moderation
     * 
     * 
     * @param	mix 	$item	user id or ip
     */
    function getLatest($item, $isUid = true){
	    if($isUid){
	    	$criteria = "uid =".intval($item);
	    }else{
		    $ip_segs = explode(".",$item);
		    $segs = min(count($ip_segs), 4);
		    for($i=1; $i<=$segs; $i++){
			    $ips[] = $this->db->quoteString(implode(".", array_slice($ip_segs, 0, $i)));
		    }
	    	$criteria = "ip IN(".implode(",", $ips).")";
	    }
		$sql = "SELECT MAX(mod_end) AS expire FROM ".$this->db->prefix('xf_moderates')." WHERE ".$criteria;
        if (!$result = $this->db->query($sql)) {
            return -1;
        }
        $row = $this->db->fetchArray($result);
        return $row["expire"];
    }
    
    /**
     * clean orphan items from database
     * 
     * @return 	bool	true on success
     */
    function cleanOrphan()
    {
    	/* for MySQL 4.1+ */
    	if($this->mysql_major_version() >= 4):
        $sql = "DELETE FROM ".$this->table.
        		" WHERE (forum_id >0 AND forum_id NOT IN ( SELECT DISTINCT forum_id FROM ".$this->db->prefix("xf_forums").") )";
        else:
        // for 4.0 +
        /* */
        $sql = 	"DELETE ".$this->table." FROM ".$this->table.
        		" LEFT JOIN ".$this->db->prefix("xf_forums")." AS aa ON ".$this->table.".forum_id = aa.forum_id ".
        		" WHERE ".$this->table.".forum_id > 0 AND (aa.forum_id IS NULL)";
        /* */
        // for 4.1+
        /*
        $sql = 	"DELETE bb FROM ".$this->table." AS bb".
        		" LEFT JOIN ".$this->db->prefix("xf_forums")." AS aa ON bb.forum_id = aa.forum_id ".
        		" WHERE bb.forum_id > 0 AND (aa.forum_id IS NULL)";
        */
		endif;
        if (!$result = $this->db->queryF($sql)) {
	        forum_message("cleanOrphan:". $sql);
            return false;
        }
        return true;
    }
}
?>