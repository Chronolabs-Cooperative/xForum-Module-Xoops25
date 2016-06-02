<?php

// $Id: topic.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $

 
if (!defined("XOOPS_ROOT_PATH")) {
	exit();
}

class Topic extends XoopsObject 
{
	var $_ModConfig = NULL;
	var $_Mod = NULL;
	
    function __construct()
    {
        $this->initVar('topic_id', XOBJ_DTYPE_INT);
        $this->initVar('topic_title', XOBJ_DTYPE_TXTBOX);
        $this->initVar('topic_poster', XOBJ_DTYPE_INT);
        $this->initVar('topic_time', XOBJ_DTYPE_INT);
        $this->initVar('topic_views', XOBJ_DTYPE_INT);
        $this->initVar('topic_replies', XOBJ_DTYPE_INT);
        $this->initVar('topic_last_post_id', XOBJ_DTYPE_INT);
        $this->initVar('forum_id', XOBJ_DTYPE_INT);
        $this->initVar('topic_status', XOBJ_DTYPE_INT);
        $this->initVar('topic_subject', XOBJ_DTYPE_INT);
        $this->initVar('topic_sticky', XOBJ_DTYPE_INT);
        $this->initVar('topic_digest', XOBJ_DTYPE_INT);
        $this->initVar('digest_time', XOBJ_DTYPE_INT);
        $this->initVar('approved', XOBJ_DTYPE_INT);
        $this->initVar('poster_name', XOBJ_DTYPE_TXTBOX);
        $this->initVar('rating', XOBJ_DTYPE_OTHER);
        $this->initVar('votes', XOBJ_DTYPE_INT);
        $this->initVar('topic_haspoll', XOBJ_DTYPE_INT);
        $this->initVar('poll_id', XOBJ_DTYPE_INT);
        
        $config_handler = xoops_gethandler('config');
		$module_handler = xoops_gethandler('module');
		$this->_Mod = $module_handler->getByDirname('xforum');
		$this->_ModConfig = $config_handler->getConfigList($this->_Mod->getVar('mid'));
    }
    
    function incrementCounter()
    {
        $sql = 'UPDATE ' . $GLOBALS["xoopsDB"]->prefix('xf_topics') . ' SET topic_views = topic_views + 1 WHERE topic_id =' . $this->getVar('topic_id');
        $GLOBALS["xoopsDB"]->queryF($sql);
    }
    
    function getSUBPath()
    {
		static $subpath;
		if (!isset($subpath[$this->getVar('forum_id')])) {
			$subpath[$this->getVar('forum_id')]='/';
			$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
			$forum = $GLOBALS['forum_handler']->get($this->getVar('forum_id'));
	    	$categories_handler = xoops_getmodulehandler('category', 'xforum');
	    	$category = $categories_handler->get($forum->getVar('cat_id'));
	    	$subpath[$this->getVar('forum_id')] .= xoops_sef($category->getVar('cat_title'));
	    	$subpath[$this->getVar('forum_id')] .= '/';
	    	$subpath[$this->getVar('forum_id')] .= xoops_sef($forum->getVar('forum_name'));
		}
    	return $subpath[$this->getVar('forum_id')];
    }
    
    function domain() {
    	static $domains;
		if (!isset($domains[$this->getVar('forum_id')])) {
    		$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
			$forum = $GLOBALS['forum_handler']->get($this->getVar('forum_id'));
			$domains[$this->getVar('forum_id')] = urldecode($forum->getVar('domain'));
		}
	    return $domains[$this->getVar('forum_id')];
    }

	function getARCHIVEURL()
    {	
		if ($this->_ModConfig['htaccess']) {
    		return (!$GLOBALS['xforumModuleConfig']['multisite'] ? XOOPS_URL : $this->domain()) . '/' . $this->_ModConfig['baseurl'] . $this->getSUBPath() . '/archive,' . $this->getVar('forum_id') . ',' . $this->getVar('topic_id') . $this->_ModConfig['endofurl'];
    	} else {
    		return (!$GLOBALS['xforumModuleConfig']['multisite'] ? XOOPS_URL : $this->domain()) . '/modules/xforum/archive.php?forum='.$this->getVar('forum_id').'&topic_id='.$this->getVar('topic_id');
    	}
    }
    
    function getURL($post_id=0) {
    	
    	$topic_id = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;
		$forum_id = !empty($_GET['forum']) ? intval($_GET['forum']) : 0;
		$move = isset($_GET['move'])? strtolower($_GET['move']) : '0';
		$GLOBALS['start'] = !empty($_GET['start']) ? intval($_GET['start']) : 0;
		$type = (!empty($_GET['type']) && in_array($_GET['type'], array("active", "pending", "deleted")))? $_GET['type'] : "0";
		$mode = !empty($_GET['mode']) ? intval($_GET['mode']) : (!empty($type)?2:0);
    	$GLOBALS['order'] = (isset($_GET['order']) && in_array(strtoupper($_GET['order']),array("DESC","ASC")))?$_GET['order']:"ASC";
    	$GLOBALS['viewmode'] = !empty($_GET['viewmode']) ? $_GET['viewmode'] : '';
    	
    	if ($post_id>0||($start>0&&!isset($_GET['sortname']))) {
    		if ($start>0) {
	    		if ($this->_ModConfig['htaccess']) {
		    		return (!$GLOBALS['xforumModuleConfig']['multisite'] ? XOOPS_URL : $this->domain()) . '/' . $this->_ModConfig['baseurl'] . $this->getSUBPath() . '/' . xoops_sef($this->getVar('topic_title')) . '/' . $this->getVar('forum_id') . ',' . $this->getVar('topic_id')  . ",0,$start,$move,$type,$order" . $this->_ModConfig['endofurl'];
		    	} else {
		    		return (!$GLOBALS['xforumModuleConfig']['multisite'] ? XOOPS_URL : $this->domain()).'/modules/xforum/'.'viewtopic.php?forum=' . $this->getVar('forum_id') . '&amp;topic_id=' . $this->getVar('topic_id') . "&amp;start=$start&amp;move=$move&amp;mode=$mode&amp;type=$type&amp;order=$order";
		    	}
    		} elseif ($post_id>0) {
    			if ($this->_ModConfig['htaccess']) {
		    		return (!$GLOBALS['xforumModuleConfig']['multisite'] ? XOOPS_URL : $this->domain()) . '/' . $this->_ModConfig['baseurl'] . $this->getSUBPath() . '/' . xoops_sef($this->getVar('topic_title')) . '/' . $this->getVar('forum_id') . ',' . $this->getVar('topic_id')  . ",$post_id,$start,$move,$type,$order" . $this->_ModConfig['endofurl'] . '#forumpost' . $post_id;
		    	} else {
		    		return (!$GLOBALS['xforumModuleConfig']['multisite'] ? XOOPS_URL : $this->domain()).'/modules/xforum/'.'viewtopic.php?forum=' . $this->getVar('forum_id') . '&amp;topic_id=' . $this->getVar('topic_id') . "&amp;post_id=$post_id&amp;start=$start&amp;move=$move&amp;mode=$mode&amp;type=$type&amp;order=$order".'#forumpost'.$post_id;
		    	}
    		}
    	} else {
    		if (strlen($viewmode)>0) {
    			if ($this->_ModConfig['htaccess']) {
			    	return (!$GLOBALS['xforumModuleConfig']['multisite'] ? XOOPS_URL : $this->domain()) . '/' . $this->_ModConfig['baseurl'] . $this->getSUBPath() . '/' . xoops_sef($this->getVar('topic_title')) . '/' . $this->getVar('forum_id') . ',' . $this->getVar('topic_id')  . ",$viewmode" . $this->_ModConfig['endofurl'];
			    } else {
			    	return (!$GLOBALS['xforumModuleConfig']['multisite'] ? XOOPS_URL : $this->domain()).'/modules/xforum/'.'viewtopic.php?forum=' . $this->getVar('forum_id') . '&amp;topic_id=' . $this->getVar('topic_id') . "&amp;order=$order&amp;viewmode=$viewmode";
			    }
    		} else {
	    		if ($this->_ModConfig['htaccess']) {
			    	return (!$GLOBALS['xforumModuleConfig']['multisite'] ? XOOPS_URL : $this->domain()) . '/' . $this->_ModConfig['baseurl'] . $this->getSUBPath() . '/' . xoops_sef($this->getVar('topic_title')) . '/' . $this->getVar('forum_id') . ',' . $this->getVar('topic_id')  . "" . $this->_ModConfig['endofurl'];
			    } else {
			    	return (!$GLOBALS['xforumModuleConfig']['multisite'] ? XOOPS_URL : $this->domain()).'/modules/xforum/'.'viewtopic.php?forum=' . $this->getVar('forum_id') . '&amp;topic_id=' . $this->getVar('topic_id') . "&amp;order=$order";
			    }
    		}
    	}
    }
    
    function getURL_JUMPBOX($start=0, $post_id=0) {
    	
    	$topic_id = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;
		$forum_id = !empty($_GET['forum']) ? intval($_GET['forum']) : 0;
		$move = isset($_GET['move'])? strtolower($_GET['move']) : '0';
		$type = (!empty($_GET['type']) && in_array($_GET['type'], array("active", "pending", "deleted")))? $_GET['type'] : "0";
		$mode = !empty($_GET['mode']) ? intval($_GET['mode']) : (!empty($type)?2:0);
    	$GLOBALS['order'] = (isset($_GET['order']) && in_array(strtoupper($_GET['order']),array("DESC","ASC")))?$_GET['order']:"ASC";
    	
		if ($post_id>0) {   		
	        if ($this->_ModConfig['htaccess']) {
	    		return (!$GLOBALS['xforumModuleConfig']['multisite'] ? XOOPS_URL : $this->domain()) . '/' . $this->_ModConfig['baseurl'] . $this->getSUBPath() . '/' . xoops_sef($this->getVar('topic_title')) . '/' . $this->getVar('forum_id') . ',' . $this->getVar('topic_id')  . ",$post_id,$start,$move,$type,$order" . $this->_ModConfig['endofurl'] . '#forumpost' . $post_id;
	    	} else {
	    		return (!$GLOBALS['xforumModuleConfig']['multisite'] ? XOOPS_URL : $this->domain()).'/modules/xforum/'.'viewtopic.php?forum=' . $this->getVar('forum_id') . '&amp;topic_id=' . $this->getVar('topic_id') . "&amp;post_id=$post_id&amp;start=$start&amp;move=$move&amp;mode=$mode&amp;type=$type&amp;order=$order".'#forumpost'.$post_id;
	    	}
		} else {
			if ($this->_ModConfig['htaccess']) {
	    		return (!$GLOBALS['xforumModuleConfig']['multisite'] ? XOOPS_URL : $this->domain()) . '/' . $this->_ModConfig['baseurl'] . $this->getSUBPath() . '/' . xoops_sef($this->getVar('topic_title')) . '/' . $this->getVar('forum_id') . ',' . $this->getVar('topic_id')  . ",0,$start,$move,$type,$order" . $this->_ModConfig['endofurl'] ;
	    	} else {
	    		return (!$GLOBALS['xforumModuleConfig']['multisite'] ? XOOPS_URL : $this->domain()).'/modules/xforum/'.'viewtopic.php?forum=' . $this->getVar('forum_id') . '&amp;topic_id=' . $this->getVar('topic_id') . "&amp;start=$start&amp;move=$move&amp;mode=$mode&amp;type=$type&amp;order=$order".'#forumpost'.$post_id;
	    	}
		}
    		    		
    }
}

class xforumTopicHandler extends XoopsPersistableObjectHandler
{
    function __construct($db) {
        parent::__construct($db, 'xf_topics', 'Topic', 'topic_id', 'topic_title');
    }
    
    function get($id, $var = null)
    {
	    $ret = null;
	    if(!empty($var) && is_string($var)) {
		    $tags = array($var);
	    }else{
		    $tags = $var;
	    }
	    if(!$topic_obj = parent::get($id, $tags)){
		    return $ret;
	    }
	    if(!empty($var) && is_string($var)) {
		    $ret = @$topic_obj->getVar($var);
	    }else{
		    $ret = $topic_obj;
	    }
	    return $ret;
    }

    function approve($topic_id)
    {
        $sql = "UPDATE " . $this->db->prefix("xf_topics") . " SET approved = 1 WHERE topic_id = $topic_id";
        if (!$result = $this->db->queryF($sql)) {
            forum_message("xforumTopicHandler::approve error:" . $sql);
            return false;
        }
		$post_handler = xoops_getmodulehandler('post', 'xforum');
        $posts_obj = $post_handler->getAll(new Criteria('topic_id', $topic_id));
        foreach(array_keys($posts_obj) as $post_id){
	        $post_handler->approve($posts_obj[$post_id]);
        }
        unset($posts_obj);
        return true;
    }

    /**
     * get previous/next topic
     *
     * @param	integer	$topic_id	current topic ID
     * @param	integer	$action
     * <ul>
     *		<li> -1: previous </li>
     *		<li> 0: current </li>
     *		<li> 1: next </li>
     * </ul>
     * @param	integer	$forum_id	the scope for moving
     * <ul>
     *		<li> >0 : inside the forum </li>
     *		<li> <= 0: global </li>
     * </ul>
     * @access public
     */
    function getByMove($topic_id, $action, $forum_id = 0)
    {
	    $topic = null;
	    if(!empty($action)):
        $sql = "SELECT * FROM " . $this->table.
               	" WHERE 1=1".
               	(($forum_id>0)?" AND forum_id=".intval($forum_id):"").
               	" AND topic_id ".(($action>0)?">":"<").intval($topic_id).
				" ORDER BY topic_id ".(($action>0)?"ASC":"DESC")." LIMIT 1";
        if($result = $this->db->query($sql)){
        	if($row = $this->db->fetchArray($result)):
	        $topic = $this->create(false);
	        $topic->assignVars($row);
	        return $topic;
	        endif;
        }
        endif;
        $topic = $this->get($topic_id);
        return $topic;
    }

    function getByPost($post_id)
    {
	    $topic = null;
        $sql = "SELECT t.* FROM " . $this->db->prefix('xf_topics') . " t, " . $this->db->prefix('xf_posts') . " p
                WHERE t.topic_id = p.topic_id AND p.post_id = " . intval($post_id);
        $result = $this->db->query($sql);
        if (!$result) {
            forum_message("xforumTopicHandler::getByPost error:" . $sql);
            return $topic;
        }
        $row = $this->db->fetchArray($result);
        $topic = $this->create(false);
        $topic->assignVars($row);
        return $topic;
    }

    function getPostCount($topic, $type ="")
    {
        switch($type){
	        case "pending":
				$approved = 0;	        	
	        	break;
	        case "deleted":
				$approved = -1;	        	
	        	break;
	        default:
				$approved = 1;	        	
	        	break;
        }
	    $criteria = new CriteriaCompo(new Criteria("topic_id", $topic->getVar('topic_id')));
	    $criteria->add(new Criteria("approved", $approved));
	    $post_handler = xoops_getmodulehandler("post", "xforum");
	    $count = $post_handler->getCount($criteria);
        return $count;
    }

    function getTopPost($topic_id)
    {
	    $post = null;
        $sql = "SELECT p.*, t.* FROM " . $this->db->prefix('xf_posts') . " p,
	        " . $this->db->prefix('xf_posts_text') . " t
	        WHERE
	        p.topic_id = " . $topic_id . " AND p.pid = 0
	        AND t.post_id = p.post_id";

        $result = $this->db->query($sql);
        if (!$result) {
            forum_message("xforumTopicHandler::getTopPost error:" . $sql);
            return $post;
        }
        $post_handler = xoops_getmodulehandler('post', 'xforum');
        $myrow = $this->db->fetchArray($result);
        $post = $post_handler->create(false);
        $post->assignVars($myrow);
        return $post;
    }

    function getTopPostId($topic_id)
    {
        $sql = "SELECT MIN(post_id) AS post_id FROM " . $this->db->prefix('xf_posts') . " WHERE topic_id = " . $topic_id . " AND pid = 0";
        $result = $this->db->query($sql);
        if (!$result) {
            forum_message("xforumTopicHandler::getTopPostId error:" . $sql);
            return false;
        }
        list($post_id) = $this->db->fetchRow($result);
        return $post_id;
    }

    function getAllPosts($topic, $order = "ASC", $perpage = 10, $start, $post_id = 0, $type = "")
    {
	    

        $ret = array();
        $perpage = (intval($perpage)>0) ? intval($perpage) : (empty($GLOBALS['xforumModuleConfig']['posts_per_page']) ? 10 : $GLOBALS['xforumModuleConfig']['posts_per_page']);
        $GLOBALS['start'] = intval($start);
        switch($type){
	        case "pending":
	        	$approve_criteria = ' AND p.approved = 0';
	        	break;
	        case "deleted":
	        	$approve_criteria = ' AND p.approved = -1';
	        	break;
	        default:
	        	$approve_criteria = ' AND p.approved = 1';
	        	break;
        }

        if ($post_id) {
	        if ($order == "DESC") {
	            $operator_for_position = '>' ;
	        } else {
	            $GLOBALS['order'] = "ASC" ;
	            $operator_for_position = '<' ;
	        }
        	//$approve_criteria = ' AND approved = 1'; // any others?
            $sql = "SELECT COUNT(*) FROM " . $this->db->prefix('xf_posts') . " AS p WHERE p.topic_id=" . intval($topic->getVar('topic_id')) . $approve_criteria . " AND p.post_id $operator_for_position $post_id";
            $result = $this->db->query($sql);
	        if (!$result) {
	            forum_message("xforumTopicHandler::getAllPosts:post-count error:" . $sql);
	            return $ret;
	        }
            list($position) = $this->db->fetchRow($result);
            $GLOBALS['start'] = intval($position / $perpage) * $perpage;
        }

        $sql = 'SELECT p.*, t.* FROM ' . $this->db->prefix('xf_posts') . ' p, ' . $this->db->prefix('xf_posts_text') . " t WHERE p.topic_id=" . $topic->getVar('topic_id') . " AND p.post_id = t.post_id" . $approve_criteria . " ORDER BY p.post_id $order";
        $result = $this->db->query($sql, $perpage, $start);
        if (!$result) {
            forum_message("xforumTopicHandler::getAllPosts error:" . $sql);
            return $ret;
        }
        $post_handler = xoops_getmodulehandler('post', 'xforum');
        while ($myrow = $this->db->fetchArray($result)) {
            $post = $post_handler->create(false);
            $post->assignVars($myrow);
            $ret[$myrow['post_id']] = $post;
            unset($post);
        }
        return $ret;
    }

    function getPostTree($postArray, $pid=0)
    {
		include_once XOOPS_ROOT_PATH . "/modules/xforum/class/xforumtree.php";
        $xforumTree = new xforumTree('xf_posts');
        $xforumTree->setPrefix('&nbsp;&nbsp;');
        $xforumTree->setPostArray($postArray);
        return $xforumTree->getPostTree($postsArray, $pid);
    }

    function showTreeItem($topic, $postArray)
    {
        
        $postArray['post_time'] = forum_formatTimestamp($postArray['post_time']);

        if (!empty($postArray['icon'])){
            $postArray['icon'] = '<img src="' . XOOPS_URL . "/images/subject/" . htmlspecialchars($postArray['icon']) . '" alt="" />';
        }else{
            $postArray['icon'] = '<a name="' . $postArray['post_id'] . '"><img src="' . XOOPS_URL . '/images/icons/no_posticon.gif" alt="" /></a>';
        }

        if (isset($GLOBALS['viewtopic_users'][$postArray['uid']]['is_forumadmin'])){
            $postArray['subject'] = $GLOBALS['myts']->undoHtmlSpecialChars($postArray['subject']);
        }
        $postArray['subject'] = '<a href="'.XOOPS_URL.'/modules/xforum/'.'viewtopic.php?viewmode=thread&amp;topic_id=' . $topic->getVar('topic_id') . '&amp;forum=' . $postArray['forum_id'] . '&amp;post_id=' . $postArray['post_id'] . '">' . $postArray['subject'] . '</a>';

        $isActiveUser = false;
        if (isset($GLOBALS['viewtopic_users'][$postArray['uid']]['name'])) {
	        $postArray['poster'] = $GLOBALS['viewtopic_users'][$postArray['uid']]['name'];
	        if($postArray['uid']>0)
	        $postArray['poster'] = "<a href=\"".XOOPS_URL . "/userinfo.php?uid=" . $postArray['uid'] ."\">".$GLOBALS['viewtopic_users'][$postArray['uid']]['name']."</a>";
        }else{
            $postArray['poster'] = (empty($postArray['poster_name']))?$GLOBALS['myts']->HtmlSpecialChars($GLOBALS['xoopsConfig']['anonymous']):$postArray['poster_name'];
        }

        return $postArray;
    }

    function getAllPosters($topic, $isApproved = true)
    {
        $sql = 'SELECT DISTINCT uid FROM ' . $this->db->prefix('xf_posts') . "  WHERE topic_id=" . $topic->getVar('topic_id')." AND uid>0";
        if($isApproved) $sql .= ' AND approved = 1';
        $result = $this->db->query($sql);
        if (!$result) {
            forum_message("xforumTopicHandler::getAllPosters error:" . $sql);
            return array();
        }
        $ret = array();
        while ($myrow = $this->db->fetchArray($result)) {
            $ret[] = $myrow['uid'];
        }
        return $ret;
    }

    function delete($topic, $force = true){
	    $topic_id = is_object($topic)?$topic->getVar("topic_id"):intval($topic);
	    if(empty($topic_id)){
		    return false;
	    }
        $post_obj = $this->getTopPost($topic_id);
		$post_handler = xoops_getmodulehandler('post', 'xforum');
	    $post_handler->delete($post_obj, false, $force);
	    return true;
    }
    
    // get permission
    function getPermission($xforum, $topic_locked = 0, $type = "view")
    {
        
        static $_cachedTopicPerms;

        if(forum_isAdmin($xforum)) return 1;

        $xforum = is_object($xforum)?$xforum->getVar('forum_id'):intval($xforum);
	    if($xforum<1) return false;

        if (!isset($_cachedTopicPerms)){
            $getpermission = xoops_getmodulehandler('permission', 'xforum');
            $_cachedTopicPerms = $getpermission->getPermissions("forum", $xforum);
        }

        $type = strtolower($type);
        $perm_item = 'forum_' . $type;
        $permission = (isset($_cachedTopicPerms[$xforum][$perm_item])) ? 1 : 0;

        if ($topic_locked && 'view' != $type) $permission = 0;

        return $permission;
    }
    
    /**
     * clean orphan items from database
     * 
     * @return 	bool	true on success
     */
    function cleanOrphan()
    {
	    parent::cleanOrphan($this->db->prefix("xf_forums"), "forum_id");
	    parent::cleanOrphan($this->db->prefix("xf_posts"), "topic_id");
	    
	    return true;
    }

    /**
     * clean expired objects from database
     * 
     * @param 	int 	$expire 	time limit for expiration
     * @return 	bool	true on success
     */
    function cleanExpires($expire = 0)
    {
	    $crit_expire = new CriteriaCompo(new Criteria("approved", 0, "<="));
	    //if(!empty($expire)){
	    	$crit_expire->add(new Criteria("topic_time", time()-intval($expire), "<"));
    	//}
	    return $this->deleteAll($crit_expire, true/*, true*/);
    }
    
    function synchronization($object = null, $force = true)
    {
	        
	    if(empty($object)) {
	    	/* for MySQL 4.1+ */
	    	if($this->mysql_major_version() >= 4):
		    // Set topic_last_post_id
	        $sql = "UPDATE ".$this->table.
	        		" SET ".$this->table.".topic_last_post_id = @last_post =(".
	        		"	SELECT MAX(post_id) AS last_post ".
	        		" 	FROM " . $this->db->prefix("xf_posts") . 
	        		" 	WHERE approved=1 AND topic_id = ".$this->table.".topic_id".
	        		" )".
	        		" WHERE ".$this->table.".topic_last_post_id <> @last_post";
			$this->db->queryF($sql);
		    // Set topic_replies
	        $sql = "UPDATE ".$this->table.
	        		" SET ".$this->table.".topic_replies = @replies =(".
	        		"	SELECT count(*) AS total ".
	        		" 	FROM " . $this->db->prefix("xf_posts") . 
	        		" 	WHERE approved=1 AND topic_id = ".$this->table.".topic_id".
	        		" )".
	        		" WHERE ".$this->table.".topic_replies <> @replies";
			$this->db->queryF($sql);
	        else:
	        // for 4.0+
		    $topics = $this->getIds();
		    foreach($topics as $id){
			    if(!$obj = $this->get($id)) continue;
			    $this->synchronization($obj);
			    unset($obj);
		    }
		    unset($topics);
	        endif;

		    /*
		    // MYSQL syntax error
		    // Set post pid for top post
	        $sql = "UPDATE ".$this->db->prefix("xf_posts").
	        		" SET ".$this->db->prefix("xf_posts").".pid = 0".
	        		" LEFT JOIN ".$this->db->prefix("xf_posts")." AS aa ON aa.topic_id = ".$this->db->prefix("xf_posts").".topic_id".
	        		" WHERE ".$this->db->prefix("xf_posts").".pid <> 0 ".
	        		" 	AND ".$this->db->prefix("xf_posts").".post_id = MIN(aa.post_id)";
			$this->db->queryF($sql);
		    // Set post pid for non-top post
	        $sql = "UPDATE ".$this->db->prefix("xf_posts").
	        		" SET ".$this->db->prefix("xf_posts").".pid = MIN(aa.post_id)".
	        		" LEFT JOIN ".$this->db->prefix("xf_posts")." AS aa ON aa.topic_id = ".$this->db->prefix("xf_posts").".topic_id".
	        		" WHERE ".$this->db->prefix("xf_posts").".pid <> 0 ".
	        		" 	AND ".$this->db->prefix("xf_posts").".post_id <> @top_post";
			$this->db->queryF($sql);
			*/			
			return;
	    }
	    if(!is_object($object)){
		    $object = $this->get(intval($object));
	    }
	    if(!$object->getVar("topic_id")) return false;

        if($force):
        $sql = "SELECT MAX(post_id) AS last_post, COUNT(*) AS total ".
        		" FROM " . $GLOBALS['xoopsDB']->prefix("xf_posts") . 
        		" WHERE approved=1 AND topic_id = ".$object->getVar("topic_id");
        if ( $result = $GLOBALS['xoopsDB']->query($sql) )
        if ( $row = $GLOBALS['xoopsDB']->fetchArray($result) ) {
	        if($object->getVar("topic_last_post_id") != $row['last_post']){
            	$object->setVar("topic_last_post_id", $row['last_post']);
        	}
	        if($object->getVar("topic_replies") != $row['total'] -1 ){
            	$object->setVar("topic_replies", $row['total'] -1);
        	}
        }
        $this->insert($object, true);
        endif;
        
	    $time_synchronization = 30 * 24 * 3600; // in days; this should be counted since last synchronization
        if($force || $object->getVar("topic_time") > (time() - $time_synchronization)):
        $sql = "SELECT MIN(post_id) AS top_post FROM ".$GLOBALS['xoopsDB']->prefix("xf_posts")." WHERE approved = 1 AND topic_id = ".$object->getVar("topic_id");
        if ( $result = $GLOBALS['xoopsDB']->query($sql) ) {
        	list($top_post) = $GLOBALS['xoopsDB']->fetchRow($result);
        	if(empty($top_post)) return false;
	        $sql = 	"UPDATE ".$GLOBALS['xoopsDB']->prefix("xf_posts").
	        		" SET pid = 0 ".
	        		" WHERE post_id = ".$top_post.
	        		" AND pid <> 0";
	        if ( !$result = $GLOBALS['xoopsDB']->queryF($sql) ) {
	            //forum_message("Could not set top post $top_post for topic: ".$sql);
	            //return false;
	        }
	        $sql = 	"UPDATE ".$GLOBALS['xoopsDB']->prefix("xf_posts").
	        		" SET pid = ".$top_post.
	        		" WHERE".
	        		" 	topic_id = ".$object->getVar("topic_id").
	        		" 	AND post_id <> ".$top_post.
	        		" 	AND pid = 0";
	        if ( !$result = $GLOBALS['xoopsDB']->queryF($sql) ) {
	            //forum_message("Could not set post parent ID for topic: ".$sql);
	            //return false;
	        }
	        
	        /*
		    // MYSQL syntax error
	        $sql = 	"UPDATE ".$GLOBALS['xoopsDB']->prefix("xf_posts").
	        		" SET ".$GLOBALS['xoopsDB']->prefix("xf_posts"). ".pid = ".$top_post.
	        		" LEFT JOIN ".$GLOBALS['xoopsDB']->prefix("xf_posts"). " AS bb".
	        		" 	ON bb.post_id = ".$GLOBALS['xoopsDB']->prefix("xf_posts"). ".pid".
	        		" WHERE".
	        		" 	".$GLOBALS['xoopsDB']->prefix("xf_posts"). ".topic_id = ".$object->getVar("topic_id").
	        		" 	AND ".$GLOBALS['xoopsDB']->prefix("xf_posts"). ".post_id <> ".$top_post.
	        		" 	AND bb.topic_id <>".$object->getVar("topic_id");
	        if ( !$result = $GLOBALS['xoopsDB']->queryF($sql) ) {
	            //forum_message("Could not concile posts for topic: ".$sql);
	            //return false;
	        }
	        */
        }
        endif;
		return true;
    }
}

?>