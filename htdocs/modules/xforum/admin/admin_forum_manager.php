<?php

// $Id: admin_forum_manager.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System                      //
// Copyright (c) 2000 XOOPS.org                           //
// <http://www.chronolabs.org/>                             //
// ------------------------------------------------------------------------ //
// This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License 2.0 as published by //
// the Free Software Foundation; either version 2 of the License, or        //
// (at your option) any later version.                                      //
// //
// You may not change or alter any portion of this comment or credits       //
// of supporting developers from this source code or any supporting         //
// source code which is considered copyrighted (c) material of the          //
// original comment or credit authors.                                      //
// //
// This program is distributed in the hope that it will be useful,          //
// but WITHOUT ANY WARRANTY; without even the implied warranty of           //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
// GNU General Public License for more details.                             //
// //
// You should have received a copy of the GNU General Public License        //
// along with this program; if not, write to the Free Software              //
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------ //
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, http://www.chronolabs.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //
include 'admin_header.php';

$op = '';
$confirm = '';

if (isset($_GET['op'])) $op = $_GET['op'];
if (isset($_POST['op'])) $op = $_POST['op'];
if (isset($_POST['default'])) $op = 'default';
if (isset($_GET['forum'])) $xforum = $_GET['forum'];
if (isset($_POST['forum'])) $xforum = $_POST['forum'];

$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');

xoops_cp_header();
switch ($op) {
    case 'moveforum':
        $indexAdmin = new ModuleAdmin();
		echo $indexAdmin->addNavigation(basename(__FILE__).'?op=manage');
        if(!empty($_POST['dest'])) {
            if (!empty($_GET['forum'])) $forum_id = intval($_GET['forum']);
            if (!empty($_POST['forum'])) $forum_id = intval($_POST['forum']);

            $dest = $_POST['dest'];
            if($dest{0}=="f"){
	            $pid = substr($dest, 1);
            	$xforum = $forum_handler->get(intval($pid));
            	$cid = $xforum->getVar("cat_id");
            	unset($xforum);
            }else{
	            $cid = intval($dest);
	            $pid = 0;
            }
            $bMoved = 0;
            $errString = '';
            $value = "cat_id=" . $cid.", parent_forum=" . $pid;
            $sql_move = "UPDATE " . $GLOBALS['xoopsDB']->prefix('xf_forums') . " SET " . $value . " WHERE forum_id=$forum_id";
            if ($result = $GLOBALS['xoopsDB']->queryF($sql_move)){
                $bMoved = 1;
            	$sql = "UPDATE " . $GLOBALS['xoopsDB']->prefix('xf_forums') . " SET parent_forum = 0 WHERE parent_forum=$forum_id";
            	$result = $GLOBALS['xoopsDB']->queryF($sql);
            }

            if (!$bMoved) {
                redirect_header('./admin_forum_manager.php?op=manage', 2, _AM_XFORUM_MSG_ERR_FORUM_MOVED);
            } else {
                redirect_header('./admin_forum_manager.php?op=manage', 2, _AM_XFORUM_MSG_FORUM_MOVED);
            }
            exit();
        } else {
	        $indexAdmin = new ModuleAdmin();
			echo $indexAdmin->addNavigation(basename(__FILE__).'?op=manage');
	            
            if (!empty($_POST['forum'])) $forum_id = intval($_POST['forum']);
            if (!empty($_GET['forum'])) $forum_id = intval($_GET['forum']);
            //$xforum = $forum_handler->get($forum_id);
           
	        $box = '<select name="dest">';
            $box .= '<option value=0 selected>' . _AM_XFORUM_SELECT . '</option>';
	
			$category_handler = xoops_getmodulehandler('category', 'xforum');
		    $categories = $category_handler->getAllCats('', true);
		    $xforums = $GLOBALS['forum_handler']->getForumsByCategory(array_keys($categories), '', false);
		
			if(count($categories)>0 && count($xforums)>0){
				foreach(array_keys($xforums) as $key){
		            $box .= "<option value=".$key."'>".$categories[$key]->getVar('cat_title')."</option>";
		            foreach ($xforums[$key] as $xforumid=>$_forum) {
		                $box .= "<option value='f".$xforumid."'>-- ".$_forum['title']."</option>";
		            }
				}
		    }
	    	unset($xforums, $categories);
            $box .= '</select>';

            echo '<form action="./admin_forum_manager.php" method="post" name="forummove" id="forummove">';
            echo '<input type="hidden" name="op" value="moveforum" />';
            echo '<input type="hidden" name="forum" value=' . $forum_id . ' />';
            echo '<table border="0" cellpadding="1" cellspacing="0" align="center" valign="top" width="95%"><tr>';
            echo '<td class="bg2" align="center"><strong>' . _AM_XFORUM_MOVETHISFORUM . '</strong></td>';
            echo '</tr>';
            echo '<tr><td class="bg1" align="center">' . $box . '</td></tr>';
            echo '<tr><td align="center"><input type="submit" name="save" value=' . _GO . ' class="button" /></td></tr>';
            echo '</table></form>';
        }
        break;

    case 'mergeforum':

        $indexAdmin = new ModuleAdmin();
		echo $indexAdmin->addNavigation(basename(__FILE__).'?op=manage');
        
        if (!empty($_POST['dest_forum'])) {
            if (isset($_GET['forum'])) $forum_id = intval($_GET['forum']);
            if (isset($_POST['forum'])) $forum_id = intval($_POST['forum']);

            $sql = "UPDATE " . $GLOBALS['xoopsDB']->prefix('xf_posts') . " SET forum_id=" . $_POST['dest_forum'] . " WHERE forum_id=$xforum";
            $result = $GLOBALS['xoopsDB']->queryF($sql);
            $sql = "UPDATE " . $GLOBALS['xoopsDB']->prefix('xf_topics') . " SET forum_id=" . $_POST['dest_forum'] . " WHERE forum_id=$xforum";
            $result = $GLOBALS['xoopsDB']->queryF($sql);
            $sql = "UPDATE " . $GLOBALS['xoopsDB']->prefix('xf_forums') . " SET parent_forum = 0 WHERE parent_forum=$forum_id";
            $result = $GLOBALS['xoopsDB']->queryF($sql);

            $sql = "SELECT COUNT(*) AS count FROM " . $GLOBALS['xoopsDB']->prefix('xf_posts') . " WHERE WHERE forum_id=$forum_id";
            $result = $GLOBALS['xoopsDB']->query($sql);
            list($post_count) = $GLOBALS['xoopsDB']->fetchArray($result);
            $sql = "SELECT COUNT(*) AS count FROM " . $GLOBALS['xoopsDB']->prefix('xf_topics') . " WHERE WHERE forum_id=$forum_id";
            $result = $GLOBALS['xoopsDB']->query($sql);
            list($topic_count) = $GLOBALS['xoopsDB']->fetchArray($result);

            $xforum = $forum_handler->get($forum_id);
            $GLOBALS['forum_handler']->delete($xforum);

            if ($post_count || $topic_count) {
                redirect_header('./admin_forum_manager.php?op=manage', 2, _AM_XFORUM_MSG_ERR_FORUM_MERGED);
            } else {
                redirect_header('./admin_forum_manager.php?op=manage', 2, _AM_XFORUM_MSG_FORUM_MERGED);
            }
            exit();
        } else {
            $indexAdmin = new ModuleAdmin();
			echo $indexAdmin->addNavigation(basename(__FILE__).'?op=manage');
            

            if (isset($_GET['forum'])) $forum_id = intval($_GET['forum']);
            if (isset($_POST['forum'])) $forum_id = intval($_POST['forum']);
            //$xforum = $forum_handler->get($forum_id);
			
	        $box = '<select name="dest_forum">';
            $box .= '<option value=0 selected>' . _AM_XFORUM_SELECT . '</option>';
	
			//$category_handler = xoops_getmodulehandler('category', 'xforum');
		    $xforums = $GLOBALS['forum_handler']->getForumsByCategory(0, '', false);
		
			if(count($xforums)>0){
				foreach(array_keys($xforums) as $key){
		            foreach ($xforums[$key] as $f=>$_forum) {
		                $box .= "<option value='".$f."'>-- ".$_forum['title']."</option>";
						if( !isset($_forum["sub"]) || count($_forum["sub"])==0) continue; 
			            foreach (array_keys($_forum["sub"]) as $s) {
			                $box .= "<option value='".$s."'>---- ".$_forum["sub"][$s]['title']."</option>";
		                }
		            }
				}
		    }
	    	unset($xforums);

            echo '<form action="./admin_forum_manager.php" method="post" name="forummove" id="forummove">';
            echo '<input type="hidden" name="op" value="mergeforum" />';
            echo '<input type="hidden" name="forum" value=' . $forum_id . ' />';
            echo '<table border="0" cellpadding="1" cellspacing="0" align="center" valign="top" width="95%"><tr>';
            echo '<td class="bg2" align="center"><strong>' . _AM_XFORUM_MERGETHISFORUM . '</strong></td>';
            echo '</tr>';
            echo '<tr><td class="bg1" align="center">' . _AM_XFORUM_MERGETO_FORUM . '</td></tr>';
            echo '<tr><td class="bg1" align="center">' . $box . '</td></tr>';
            echo '<tr><td align="center"><input type="submit" name="save" value=' . _GO . ' class="button" /></td></tr>';
            echo '</form></table>';
        }
        break;

    case 'sync':
        $indexAdmin = new ModuleAdmin();
		echo $indexAdmin->addNavigation(basename(__FILE__).'?op=sync');
        if (isset($_POST['submit'])) {
			forum_synchronization();
			/*
			$topic_handler = xoops_getmodulehandler('topic', 'xforum');
			$topic_handler->synchronization();
			*/
            redirect_header("./index.php", 1, _AM_XFORUM_SYNCHING);
            exit();
        } else {
            echo '<fieldset><legend style="font-weight: bold; color: #900;">' . _AM_XFORUM_SYNCFORUM . '</legend>';
            echo '<br /><br /><table width="100%" border="0" cellspacing="1" class="outer"><tr><td class="odd">';
            echo '<table border="0" cellpadding="1" cellspacing="1" width="100%">';
            echo '<tr class="bg3" align="left">';
            echo '<td>' . _AM_XFORUM_CLICKBELOWSYNC . '</td>';
            echo '</tr>';
            echo '<tr class="bg1" align="center">';
            echo '<td><form action="admin_forum_manager.php" method="post">';
            echo '<input type="hidden" name="op" value="sync"><input type="submit" name="submit" value=' . _AM_XFORUM_SYNCFORUM . ' /></form></td>';
            echo '</td>';
            echo '</tr>';
            echo '</table></td></tr></table>';
        }

        echo "</fieldset>";
        break;

    case "save":

        if ($xforum) {
            $ff = $forum_handler->get($xforum);
            $message = _AM_XFORUM_FORUMUPDATE;
        } else {
            $ff = $forum_handler->create();
            $message = _AM_XFORUM_FORUMCREATED;
        }

        $ff->setVar('forum_name', $_POST['forum_name']);
        $ff->setVar('forum_desc', $_POST['forum_desc']);
        $ff->setVar('forum_order', $_POST['forum_order']);
        $ff->setVar('forum_moderator', isset($_POST['forum_moderator'])?$_POST['forum_moderator']:array());
        $ff->setVar('parent_forum', @$_POST['parent_forum']);
        $ff->setVar('cat_id', $_POST['cat_id']);
        $ff->setVar('forum_type', @$_POST['forum_type']);
        $ff->setVar('allow_html', @$_POST['allow_html']);
        $ff->setVar('allow_sig', @$_POST['allow_sig']);
        $ff->setVar('allow_polls', $_POST['allow_polls']);
        $ff->setVar('allow_subject_prefix', @$_POST['allow_subject_prefix']);
        $ff->setVar('allow_attachments', $_POST['allow_attachments']);
        $ff->setVar('attach_maxkb', $_POST['attach_maxkb']);
        $ff->setVar('attach_ext', $_POST['attach_ext']);
        $ff->setVar('hot_threshold', $_POST['hot_threshold']);
        
        $ff->setVar('domain', @$_POST['domain']);
        $ff->setVar('domains', @$_POST['domains']);
        $ff->setVar('languages', @$_POST['languages']);
        
        if ($forum_handler->insert($ff)) {
	        if(!empty($_POST["perm_template"])){
			    $groupperm_handler = xoops_getmodulehandler('permission', 'xforum');
			    $perm_template = $groupperm_handler->getTemplate();
			    $member_handler = xoops_gethandler('member');
			    $glist = $member_handler->getGroupList();
				$perms = array_map("trim",explode(',', FORUM_PERM_ITEMS));
				foreach(array_keys($glist) as $group){
				    foreach($perms as $perm){
					    $perm = "forum_".$perm;
						$ids = $groupperm_handler->getItemIds($perm, $group, $GLOBALS['xforumModule']->getVar("mid"));
						if(!in_array($ff->getVar("forum_id"), $ids)){
							if(empty($perm_template[$group][$perm])){
								$groupperm_handler->deleteRight($perm, $ff->getVar("forum_id"), $group, $GLOBALS['xforumModule']->getVar("mid"));
							}else{
								$groupperm_handler->addRight($perm, $ff->getVar("forum_id"), $group, $GLOBALS['xforumModule']->getVar("mid"));
							}
						}
				    }
				}
	        }
            redirect_header("admin_forum_manager.php?op=mod&amp;forum=" . $ff->getVar('forum_id') . "", 2, $message);
            exit();
        } else {
            redirect_header("admin_forum_manager.php?op=mod&amp;forum=" . $ff->getVar('forum_id') . "", 2, _AM_XFORUM_FORUM_ERROR);
            exit();
        }

    case "mod":
        $ff = $GLOBALS['forum_handler']->get($xforum);
        $indexAdmin = new ModuleAdmin();
		echo $indexAdmin->addNavigation(basename(__FILE__).'?op=manage');
        
        echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_XFORUM_EDITTHISFORUM . "</legend>";
        echo"<br /><br /><table width='100%' border='0' cellspacing='1' class='outer'><tr><td class='odd'>";

        editForum($ff);

        echo"</td></tr></table>";
        echo "</fieldset>";
        break;

    case "del":

        if (isset($_POST['confirm']) != 1) {
            xoops_confirm(array('op' => 'del', 'forum' => intval($_GET['forum']), 'confirm' => 1), 'admin_forum_manager.php', _AM_XFORUM_TWDAFAP);
            break;
        } else {
            $ff = $forum_handler->get($_POST['forum']);
            $GLOBALS['forum_handler']->delete($ff);
            redirect_header("admin_forum_manager.php?op=manage", 1, _AM_XFORUM_FORUMREMOVED);
            exit();
        }
        break;

    case 'manage':
        
        $indexAdmin = new ModuleAdmin();
		echo $indexAdmin->addNavigation(basename(__FILE__).'?op=manage');
        
        $echo = "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_XFORUM_FORUM_MANAGER . "</legend>";
        $echo .= "<br />";

        $echo .= "<table border='0' cellpadding='4' cellspacing='1' width='100%' class='outer'>";
        $echo .= "<tr align='center'>";
        $echo .= "<td class='bg3' colspan='2'>" . _AM_XFORUM_NAME . "</td>";
        $echo .= "<td class='bg3'>" . _AM_XFORUM_EDIT . "</td>";
        $echo .= "<td class='bg3'>" . _AM_XFORUM_DELETE . "</td>";
        $echo .= "<td class='bg3'>" . _AM_XFORUM_ADD . "</td>";
        $echo .= "<td class='bg3'>" . _AM_XFORUM_MOVE . "</td>";
        $echo .= "<td class='bg3'>" . _AM_XFORUM_MERGE . "</td>";
        $echo .= "</tr>";

		$category_handler = xoops_getmodulehandler('category', 'xforum');
    	$categories = $category_handler->getAllCats('', true, '', true);
		$xforums = $GLOBALS['forum_handler']->getForumsByCategory(array_keys($categories), '', false, '', true);
		foreach (array_keys($categories) as $c) {
            $category = $categories[$c];
            $cat_link = "<a href=\"" . $GLOBALS['xforumUrl']['root'] . "/index.php?viewcat=" . $category->getVar('cat_id') . "\">" . $category->getVar('cat_title') . "</a>";
            $cat_edit_link = "<a href=\"admin_cat_manager.php?op=mod&amp;cat_id=" . $category->getVar('cat_id') . "\">".forum_displayImage($GLOBALS['xforumImage']['edit'], _EDIT)."</a>";
            $cat_del_link = "<a href=\"admin_cat_manager.php?op=del&amp;cat_id=" . $category->getVar('cat_id') . "\">".forum_displayImage($GLOBALS['xforumImage']['delete'], _DELETE)."</a>";
            $forum_add_link = "<a href=\"admin_forum_manager.php?op=addforum&amp;cat_id=" . $category->getVar('cat_id') . "\">".forum_displayImage($GLOBALS['xforumImage']['new_forum'])."</a>";

            $echo .= "<tr class='even' align='left'>";
            $echo .= "<td width='100%' colspan='2'><strong>" . $cat_link . "</strong></td>";
            $echo .= "<td align='center'>" . $cat_edit_link . "</td>";
            $echo .= "<td align='center'>" . $cat_del_link . "</td>";
            $echo .= "<td align='center'>" . $forum_add_link . "</td>";
            $echo .= "<td></td>";
            $echo .= "<td></td>";
            $echo .= "</tr>";
            if(!isset($xforums[$c])) continue;
			foreach(array_keys($xforums[$c]) as $f){
                $f_link = "&nbsp;<a href=\"" . $GLOBALS['xforumUrl']['root'] . "/viewforum.php?forum=" . $f . "\">" . $xforums[$c][$f]["title"] . "</a>";
                $f_edit_link = "<a href=\"admin_forum_manager.php?op=mod&amp;forum=" . $f . "\">".forum_displayImage($GLOBALS['xforumImage']['edit'])."</a>";
                $f_del_link = "<a href=\"admin_forum_manager.php?op=del&amp;forum=" . $f . "\">".forum_displayImage($GLOBALS['xforumImage']['delete'])."</a>";
                $sf_add_link = "<a href=\"admin_forum_manager.php?op=addsubforum&amp;cat_id=" . $c . "&parent_forum=" . $f . "\">".forum_displayImage($GLOBALS['xforumImage']['new_subforum'])."</a>";
                $f_move_link = "<a href=\"admin_forum_manager.php?op=moveforum&amp;forum=" . $f . "\">".forum_displayImage($GLOBALS['xforumImage']['move_topic'])."</a>";
                $f_merge_link = "<a href=\"admin_forum_manager.php?op=mergeforum&amp;forum=" . $f . "\">".forum_displayImage($GLOBALS['xforumImage']['move_topic'])."</a>";

                $echo .= "<tr class='odd' align='left'><td></td>";
                $echo .= "<td><strong>" . $f_link . "</strong></td>";
                $echo .= "<td align='center'>" . $f_edit_link . "</td>";
                $echo .= "<td align='center'>" . $f_del_link . "</td>";
                $echo .= "<td align='center'>" . $sf_add_link . "</td>";
                $echo .= "<td align='center'>" . $f_move_link . "</td>";
                $echo .= "<td align='center'>" . $f_merge_link . "</td>";
                $echo .= "</tr>";
		        if(!isset($xforums[$c][$f]["sub"])) continue;
				foreach(array_keys($xforums[$c][$f]["sub"]) as $s){
                    $f_link = "&nbsp;<a href=\"" . $GLOBALS['xforumUrl']['root'] . "/viewforum.php?forum=" . $s . "\">-->" . $xforums[$c][$f]["sub"][$s]["title"] . "</a>";
                    $f_edit_link = "<a href=\"admin_forum_manager.php?op=mod&amp;forum=" . $s . "\">".forum_displayImage($GLOBALS['xforumImage']['edit'])."</a>";
                    $f_del_link = "<a href=\"admin_forum_manager.php?op=del&amp;forum=" . $s . "\">".forum_displayImage($GLOBALS['xforumImage']['delete'])."</a>";
                    $sf_add_link = "";
                    $f_move_link = "<a href=\"admin_forum_manager.php?op=moveforum&amp;forum=" . $s . "\">".forum_displayImage($GLOBALS['xforumImage']['move_topic'])."</a>";
                    $f_merge_link = "<a href=\"admin_forum_manager.php?op=mergeforum&amp;forum=" . $s . "\">".forum_displayImage($GLOBALS['xforumImage']['move_topic'])."</a>";
                    $echo .= "<tr class='odd' align='left'><td></td>";
                    $echo .= "<td><strong>" . $f_link . "</strong></td>";
                    $echo .= "<td align='center'>" . $f_edit_link . "</td>";
                    $echo .= "<td align='center'>" . $f_del_link . "</td>";
                    $echo .= "<td align='center'>" . $sf_add_link . "</td>";
                    $echo .= "<td align='center'>" . $f_move_link . "</td>";
                    $echo .= "<td align='center'>" . $f_merge_link . "</td>";
                    $echo .= "</tr>";
				}
			}
		}
    	unset($xforums, $categories);

        echo $echo;
        echo "</table>";
        echo "</fieldset>";
        break;

    case "addsubforum":
    case "default":
    default:
        $indexAdmin = new ModuleAdmin();
		echo $indexAdmin->addNavigation(basename(__FILE__).'?op=manage');
        
        echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_XFORUM_CREATENEWFORUM . "</legend>";
        echo "<br />";

        //$parent_forum = isset($_GET['parent_forum']) ? intval($_GET['parent_forum']) : null;
        newForum(@intval($_GET['parent_forum']));

        echo "</fieldset>";
        break;
}
echo chronolabs_inline(false); xoops_cp_footer();

?>