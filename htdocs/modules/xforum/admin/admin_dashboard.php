<?php
// $Id: directory.php 5204 2010-09-06 20:10:52Z mageg $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
// Author: XOOPS Foundation                                                  //
// URL: http://www.xoops.org/                                                //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //

	include ('admin_header.php');
	xoops_loadLanguage('admin', 'profile');
	
	xoops_cp_header();		

	$op = (!empty($_GET['op']) ? $_GET['op'] : (!empty($_POST['op']) ? $_POST['op'] : "default"));
	
	switch ($op) {
	    case "createdir":
			if (isset($_GET['path'])) $path = $_GET['path'];
	        $res = forum_admin_mkdir($path);
	        $msg = ($res)?_AM_XFORUM_DIRCREATED:_AM_XFORUM_DIRNOTCREATED;
	        redirect_header('admin_dashboard.php', 2, $msg . ': ' . $path);
	        exit();
	        break;
	
	    case "setperm":
			if (isset($_GET['path'])) $path = $_GET['path'];
	        $res = forum_admin_chmod($path, 0777);
	        $msg = ($res)?_AM_XFORUM_PERMSET:_AM_XFORUM_PERMNOTSET;
	        redirect_header('admin_dashboard.php', 2, $msg . ': ' . $path);
	        exit();
	        break;
	
	    case "senddigest":
	        $digest_handler = xoops_getmodulehandler('digest', 'xforum');
	        $res = $digest_handler->process(true);
	        $msg = ($res)?_AM_XFORUM_DIGEST_FAILED:_AM_XFORUM_DIGEST_SENT;
	        redirect_header('admin_dashboard.php', 2, $msg);
	        exit();
	        break;
		case "saveapprovals":
			$post_handler = xoops_getmodulehandler('post', 'xforum');
			$topic_handler = xoops_getmodulehandler('topic', 'xforum');
			
			foreach($_POST['approval'] as $post_id => $value) {
				$post = $post_handler->get($post_id);
				$topic = $topic_handler->get($post->getVar('topic_id'));
				switch($value) {
					case 1:
						$post->setVar('approved', 1);
						$topic->setVar('approved', 1);
						$post_handler->insert($post);
						$topic_handler->insert($topic);
						break;
					case 2:
						if ($post_handler->getCount(new Criteria('topic_id', $post->getVar('topic_id')))==1)
							$topic_handler->delete($topic, true);
						$post_handler->delete($post, true);
						break;
				}
			}
			redirect_header('admin_dashboard.php');
			break;
	    case "postapprovals":
	    	
			$indexAdmin = new ModuleAdmin();
			echo $indexAdmin->addNavigation(basename(__FILE__));
	    	
			$post_handler = xoops_getmodulehandler('post', 'xforum');
			$topic_handler = xoops_getmodulehandler('topic', 'xforum');
			$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
			$user_handler = xoops_gethandler('user');
			
			$criteria = new Criteria('approved', 0);
			$ttl = $post_handler->getCount($criteria);
			
			$limit = !empty($_REQUEST['limit'])?intval($_REQUEST['limit']):15;
			$GLOBALS['start'] = !empty($_REQUEST['start'])?intval($_REQUEST['start']):0;
			$GLOBALS['order'] = !empty($_REQUEST['order'])?$_REQUEST['order']:'DESC';
			$sort = !empty($_REQUEST['sort'])?''.$_REQUEST['sort'].'':'post_time';
			
			$pagenav = new XoopsPageNav($ttl, $limit, $GLOBALS['start'], 'start', 'limit='.$limit.'&sort='.$sort.'&order='.$order.'&op='.$op);
			echo "<div style='height:35px;'><div style='float:right;'>".$pagenav->renderNav()."</div></div>";
			
			$criteria->setStart($start);
			$criteria->setLimit($limit);
			$criteria->setSort('`'.$sort.'`');
			$criteria->setOrder($order);
			$posts = $post_handler->getObjects($criteria, true);
			
			echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'>";
			echo "<table>";
			echo "<tr class='head'>";
			echo "<td>"._AM_XFORUM_APPROVAL."</td>";
			echo "<td>"._AM_XFORUM_FORUM."</td>";
			echo "<td>"._AM_XFORUM_TOPIC."</td>";
			echo "<td>"._AM_XFORUM_SUBJECT."</td>";
			echo "<td>"._AM_XFORUM_USER."</td>";
			echo "<td>"._AM_XFORUM_CONTENT."</td>";
			echo "</tr>";
			foreach($posts as $post_id => $post) {
				$radio[$post_id] = new XoopsFormRadio('', 'approval['.$post_id.']');
				$radio[$post_id]->addOption('1', _YES);
				$radio[$post_id]->addOption('0', _NO);
				$radio[$post_id]->addOption('2', _DELETE);
				$class=($class=='even'?'odd':'even');
				echo "<tr class='".$class."'>";
				echo "<td>".$radio[$post_id]->render()."</td>";
				$topic = $topic_handler->get($post->getVar('topic_id'));
				$forum = $GLOBALS['forum_handler']->get($topic->getVar('forum_id'));
				echo "<td>".$forum->getVar('forum_name')."</td>";
				echo "<td>".$topic->getVar('topic_title')."</td>";
				echo "<td>".$post->getVar('subject')."</td>";
				if ($post->getVar('uid')>0) {
					$user = $user_handler->get($post->getVar('uid'));
					echo "<td>".$user->getVar('uname')."</td>"; 
				} else {
					echo "<td>".$GLOBALS['xoopsConfig']['anonymous']."</td>";
				}
				
				echo "<td>".$GLOBALS['myts']->displayTarea($post->getPostBody(), true, true, true, true, true)."</td>";
				echo "</tr>";
			}
			echo "<tr class='head'>";
			echo "<td colspan='6'><input type='submit' value='"._SUBMIT."' name='submit'><input type='hidden' value='saveapprovals' name='op'></td>";
			echo "</tr>";
			echo "</table>";
			xoops_cp_footer();
	        break;
	        
	    case "default":
	    default:
    	
			$indexAdmin = new ModuleAdmin();
			echo $indexAdmin->addNavigation(basename(__FILE__));
			
	        $imageLibs = forum_getImageLibs();

			$indexAdmin = new ModuleAdmin();	
		    $indexAdmin->addInfoBox(_AM_XFORUM_PREFERENCES);
		    
		    $module_handler = xoops_gethandler('module');
	        $GLOBALS['xoopsPoll'] = $module_handler->getByDirname('xoopspoll');
	        if (is_object($GLOBALS['xoopsPoll'])) $isOK = $GLOBALS['xoopsPoll']->getVar('isactive');
	        else $isOK = false;
	        	        
		    $indexAdmin->addInfoBoxLine(_AM_XFORUM_PREFERENCES, "<label>"._AM_XFORUM_POLLMODULE."</label>", ($isOK)?_AM_XFORUM_AVAILABLE:_AM_XFORUM_NOTAVAILABLE, ($isOK)?'Green':'Red');
		    
		    if(array_key_exists('imagemagick',$imageLibs)) {
		    	$indexAdmin->addInfoBoxLine(_AM_XFORUM_PREFERENCES, "<label>"._AM_XFORUM_IMAGEMAGICK."</label>", _AM_XFORUM_AUTODETECTED.$imageLibs['imagemagick'], 'Green');
		    } else { 
		    	$indexAdmin->addInfoBoxLine(_AM_XFORUM_PREFERENCES, "<label>"._AM_XFORUM_IMAGEMAGICK."</label>", _AM_XFORUM_NOTAVAILABLE, 'Red');
			}
			if(array_key_exists('netpbm',$imageLibs)) {
		    	$indexAdmin->addInfoBoxLine(_AM_XFORUM_PREFERENCES, "<label>"._AM_XFORUM_NETPDM."</label>", _AM_XFORUM_AUTODETECTED.$imageLibs['netpbm'], 'Green');
		    } else { 
		    	$indexAdmin->addInfoBoxLine(_AM_XFORUM_PREFERENCES, "<label>"._AM_XFORUM_NETPDM."</label>", _AM_XFORUM_NOTAVAILABLE, 'Red');
			}
			if(array_key_exists('gd1',$imageLibs)) {
		    	$indexAdmin->addInfoBoxLine(_AM_XFORUM_PREFERENCES, "<label>"._AM_XFORUM_GDLIB1."</label>", _AM_XFORUM_AUTODETECTED.$imageLibs['gd1'], 'Green');
		    } else { 
		    	$indexAdmin->addInfoBoxLine(_AM_XFORUM_PREFERENCES, "<label>"._AM_XFORUM_GDLIB1."</label>", _AM_XFORUM_NOTAVAILABLE, 'Red');
			}
			if(array_key_exists('gd2',$imageLibs)) {
		    	$indexAdmin->addInfoBoxLine(_AM_XFORUM_PREFERENCES, "<label>"._AM_XFORUM_GDLIB2."</label>", _AM_XFORUM_AUTODETECTED.$imageLibs['gd2'], 'Green');
		    } else { 
		    	$indexAdmin->addInfoBoxLine(_AM_XFORUM_PREFERENCES, "<label>"._AM_XFORUM_GDLIB2."</label>", _AM_XFORUM_NOTAVAILABLE, 'Red');
			}
				      
	        $attach_path = XOOPS_ROOT_PATH . '/' . $GLOBALS['xforumModuleConfig']['dir_attachments'] . '/';
	        $path_status = forum_admin_getPathStatus($attach_path);
	        $indexAdmin->addInfoBox(_AM_XFORUM_PATHS);
			$indexAdmin->addInfoBoxLine(_AM_XFORUM_PATHS, "<label>"._AM_XFORUM_ATTACHPATH."</label>", $attach_path . ' ( ' . $path_status . ' )', 'Green');
	        $thumb_path = $attach_path . 'thumbs/'; // be careful
	        $path_status = forum_admin_getPathStatus($thumb_path);
	        $indexAdmin->addInfoBoxLine(_AM_XFORUM_PATHS, "<label>"._AM_XFORUM_THUMBPATH."</label>", $thumb_path . ' ( ' . $path_status . ' )', 'Green');

	        $indexAdmin->addInfoBox(_AM_XFORUM_BOARDSUMMARY);
			$indexAdmin->addInfoBoxLine(_AM_XFORUM_BOARDSUMMARY, "<label>"._AM_XFORUM_TOTALTOPICS."</label>", get_total_topics(), 'Green');
			$indexAdmin->addInfoBoxLine(_AM_XFORUM_BOARDSUMMARY, "<label>"._AM_XFORUM_TOTALPOSTS."</label>", get_total_posts(), 'Green');
			$indexAdmin->addInfoBoxLine(_AM_XFORUM_BOARDSUMMARY, "<label>"._AM_XFORUM_TOTALVIEWS."</label>", get_total_views(), 'Green');
			$criteria = new Criteria('approved', 0);
			$post_handler = xoops_getmodulehandler('post', 'xforum');
			$topic_handler = xoops_getmodulehandler('topic', 'xforum');
			
			$indexAdmin->addInfoBoxLine(_AM_XFORUM_BOARDSUMMARY, "<label>"._AM_XFORUM_POSTSWAITINGAPPROVAL."</label>", $post_handler->getCount($criteria), 'Green');
			$indexAdmin->addInfoBoxLine(_AM_XFORUM_BOARDSUMMARY, "<label>"._AM_XFORUM_TOPICWAITINGAPPROVAL."</label>", $topic_handler->getCount($criteria), 'Green');
			
	        $report_handler = xoops_getmodulehandler('report', 'xforum');
	        $indexAdmin->addInfoBox(_AM_XFORUM_REPORT);
			$indexAdmin->addInfoBoxLine(_AM_XFORUM_REPORT, "<label>"._AM_XFORUM_REPORT_PENDING."</label>", $report_handler->getCount(new Criteria("report_result", 0)), 'Green');
			$indexAdmin->addInfoBoxLine(_AM_XFORUM_REPORT, "<label>"._AM_XFORUM_REPORT_PROCESSED."</label>", $report_handler->getCount(new Criteria("report_result", 1)), 'Green');
	
	        if ($GLOBALS['xforumModuleConfig']['email_digest'] > 0) {
	            $digest_handler = xoops_getmodulehandler('digest', 'xforum');
	           	$due = ($digest_handler->checkStatus()) / 60; // minutes
	            $prompt = ($due > 0)? sprintf(_AM_XFORUM_DIGEST_PAST, $due):sprintf(_AM_XFORUM_DIGEST_NEXT, abs($due));
	            $indexAdmin->addInfoBox(_AM_XFORUM_DIGEST);
				$indexAdmin->addInfoBoxLine(_AM_XFORUM_DIGEST, "<label>"._AM_XFORUM_DIGEST_SEND."</label>", $prompt, 'Green');
				$indexAdmin->addInfoBoxLine(_AM_XFORUM_DIGEST, "<label>"._AM_XFORUM_DIGEST_ARCHIVE."</label>", $digest_handler->getDigestCount(), 'Green');
	        }
	
	    	if (!empty($GLOBALS['xforumModuleConfig']['enable_usermoderate'])){
				$moderate_handler = xoops_getmodulehandler('moderate', 'xforum');
				$moderate_handler->clearGarbage();
			}
    		echo $indexAdmin->renderIndex();
			
	        echo chronolabs_inline(false); 
	        xoops_cp_footer();
	        break;
	}

?>