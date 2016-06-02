<?php

// $Id: action.topic.php,v 4.04 2008/06/05 16:23:24 wishcraft Exp $

include 'header.php';

$forum_id = isset($_POST['forum_id']) ? intval($_POST['forum_id']) : 0;
$topic_id = !empty($_POST['topic_id']) ? $_POST['topic_id'] : null;
$op = !empty($_POST['op']) ? $_POST['op']:"";
$op = in_array($op, array("approve", "delete", "restore", "move"))? $op : "";


if ( empty($topic_id) || empty($op)) {
	redirect_header("javascript:history.go(-1);", 2, _MD_NORIGHTTOACCESS);
    exit();
}

$topic_id = array_values($topic_id);
$topic_handler = xoops_getmodulehandler('topic', 'xforum');
$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
/*
$topicid = is_array($topic_id)?$topic_id[0]:$topic_id;
$GLOBALS['xforumtopic'] = $topic_handler->get($topicid);
$forum_id = $GLOBALS['xforumtopic']->getVar('forum_id');
$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
$GLOBALS['viewtopic_forum'] = $GLOBALS['forum_handler']->get($forum_id);
*/

$GLOBALS['isadmin'] = forum_isAdmin($forum_id);

if(!$isadmin){
    redirect_header(XOOPS_URL."/index.php", 2, _MD_NORIGHTTOACCESS);
    exit();
}

switch($op){
	case "restore":
		$xforums = array();
		$topics_obj = $topic_handler->getAll(new Criteria("topic_id", "(".implode(",", $topic_id).")", "IN"));
		foreach(array_keys($topics_obj) as $id){
			$topic_obj = $topics_obj[$id];
			$topic_handler->approve($id);
			$topic_handler->synchronization($topic_obj);
			$xforums[$topic_obj->getVar("forum_id")] = 1;
		}
		$criteria_forum = new Criteria("forum_id", "(".implode(",", array_keys($xforums)).")", "IN");
		$xforums_obj = $GLOBALS['forum_handler']->getAll($criteria_forum);
		foreach(array_keys($xforums_obj) as $id){
			$GLOBALS['forum_handler']->synchronization($xforums_obj[$id]);
		}
		unset($topics_obj, $xforums_obj);
		break;
	case "approve":
		$xforums = array();
		$topics_obj = $topic_handler->getAll(new Criteria("topic_id", "(".implode(",", $topic_id).")", "IN"));
		foreach(array_keys($topics_obj) as $id){
			$topic_obj = $topics_obj[$id];
			$topic_handler->approve($id);
			$topic_handler->synchronization($topic_obj);
			$xforums[$topic_obj->getVar("forum_id")] = 1;
		}
		
		$criteria_forum = new Criteria("forum_id", "(".implode(",", array_keys($xforums)).")", "IN");
		$xforums_obj = $GLOBALS['forum_handler']->getAll($criteria_forum);
		foreach(array_keys($xforums_obj) as $id){
			$GLOBALS['forum_handler']->synchronization($xforums_obj[$id]);
		}
		
		if(empty($GLOBALS['xforumModuleConfig']['notification_enabled'])) break;
			
		include_once 'include/notification.inc.php';
		$notification_handler = xoops_gethandler('notification');
		foreach(array_keys($topics_obj) as $id){
			$topic_obj = $topics_obj[$id];
		    $tags = array();
		    $tags['THREAD_NAME'] = $topic_obj->getVar("topic_title");
		    $tags['THREAD_URL'] = XOOPS_URL . '/modules/' . $GLOBALS['xforumModule']->getVar('dirname') . '/viewtopic.php?topic_id=' . $id.'&amp;forum=' . $topic_obj->getVar('forum_id');
		    $tags['FORUM_NAME'] = $xforums_obj[$topic_obj->getVar("forum_id")]->getVar("forum_name");
		    $tags['FORUM_URL'] = XOOPS_URL . '/modules/' . $GLOBALS['xforumModule']->getVar('dirname') . '/viewforum.php?forum=' . $topic_obj->getVar('forum_id');
	        $notification_handler->triggerEvent('global', 0, 'new_thread', $tags);
	        $notification_handler->triggerEvent('forum', $topic_obj->getVar('forum_id'), 'new_thread', $tags);
	        $post_obj = $topic_handler->getTopPost($id);
		    $tags['POST_URL'] = $tags['THREAD_URL'].'#forumpost' . $post_obj->getVar("post_id");
	        $notification_handler->triggerEvent('thread', $id, 'new_post', $tags);
	        $notification_handler->triggerEvent('forum', $topic_obj->getVar('forum_id'), 'new_post', $tags);
	        $notification_handler->triggerEvent('global', 0, 'new_post', $tags);
	        $tags['POST_CONTENT'] = $post_obj->getVar("post_text");
	        $tags['POST_NAME'] = $post_obj->getVar("subject");
	        $notification_handler->triggerEvent('global', 0, 'new_fullpost', $tags);
	        $notification_handler->triggerEvent('forum', $topic_obj->getVar('forum_id'), 'new_fullpost', $tags);
	        unset($post_obj);
		}
		unset($topics_obj, $xforums_obj);
		break;
	case "delete":
		$xforums = array();
		$topics_obj = $topic_handler->getAll(new Criteria("topic_id", "(".implode(",", $topic_id).")", "IN"));
		foreach(array_keys($topics_obj) as $id){
			$topic_obj = $topics_obj[$id];
			$topic_handler->approve($id);
			$topic_handler->synchronization($topic_obj);
			$xforums[$topic_obj->getVar("forum_id")] = 1;
		}
		
		$criteria_forum = new Criteria("forum_id", "(".implode(",", array_keys($xforums)).")", "IN");
		$xforums_obj = $GLOBALS['forum_handler']->getAll($criteria_forum);
		foreach(array_keys($xforums_obj) as $id){
			$GLOBALS['forum_handler']->synchronization($xforums_obj[$id]);
		}
		unset($topics_obj, $xforums_obj);
		break;
	case "move":
		if(!empty($_POST["newforum"]) && $_POST["newforum"] != $forum_id
			&& $GLOBALS['forum_handler']->getPermission($_POST["newforum"], 'post')
		){
        	$criteria = new Criteria('topic_id', "(".implode(",", $topic_id).")", "IN");
			$post_handler = xoops_getmodulehandler('post', 'xforum');
            $post_handler->updateAll("forum_id", intval($_POST["newforum"]), $criteria, true);
            $topic_handler->updateAll("forum_id", intval($_POST["newforum"]), $criteria, true);
            
			$GLOBALS['forum_handler']->synchronization($_POST["newforum"]);
			$GLOBALS['forum_handler']->synchronization($forum_id);
		}else{
			$category_handler = xoops_getmodulehandler('category', 'xforum');
		    $categories = $category_handler->getAllCats('access', true);
		    $xforums = $GLOBALS['forum_handler']->getForumsByCategory(array_keys($categories), 'post', false);
		
	        $box = '<select name="newforum" size="1">';
			if(count($categories)>0 && count($xforums)>0){
				foreach(array_keys($xforums) as $key){
		            $box .= "<option value='-1'>[".$categories[$key]->getVar('cat_title')."]</option>";
		            foreach ($xforums[$key] as $xforumid=>$_forum) {
		                $box .= "<option value='".$xforumid."'>-- ".$_forum['title']."</option>";
						if( !isset($_forum["sub"])) continue; 
			            foreach (array_keys($_forum["sub"]) as $fid) {
			                $box .= "<option value='".$fid."'>---- ".$_forum["sub"][$fid]['title']."</option>";
		                }
		            }
				}
		    } else {
		        $box .= "<option value='-1'>"._MD_NOFORUMINDB."</option>";
		    }
		    $box .="</select>";
	    	unset($xforums, $categories);
	          
		    echo "<form action='".$_SERVER['PHP_SELF']."' method='post'>";
		    echo "<table border='0' cellpadding='1' cellspacing='0' align='center' width='95%'>";
		    echo "<tr><td class='bg2'>";
		    echo "<table border='0' cellpadding='1' cellspacing='1' width='100%'>";
	        echo '<tr><td class="bg3">'._MD_MOVETOPICTO.'</td><td class="bg1">';
	    	echo $box;      
	        echo '</td></tr>';
		    echo '<tr class="bg3"><td colspan="2" align="center">';
		    echo "<input type='hidden' name='op' value='move' />";
		    echo "<input type='hidden' name='forum_id' value='{$forum_id}' />";
		    foreach($topic_id as $id){
		    	echo "<input type='hidden' name='topic_id[]' value='".$id."' />";
	    	}
		    echo "<input type='submit' name='submit' value='". _SUBMIT."' />";
		    echo "</td></tr></table></td></tr></table>";
		    echo "</form>";
			include XOOPS_ROOT_PATH.'/footer.php';
			exit();
	    }
	    break;
}
if(empty($forum_id)){
	redirect_header("viewall.php", 2, _MD_DBUPDATED);
}else{
	redirect_header("viewforum.php?forum=$forum_id", 2, _MD_DBUPDATED);
}

include XOOPS_ROOT_PATH.'/footer.php';
?>