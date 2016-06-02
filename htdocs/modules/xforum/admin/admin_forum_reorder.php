<?php

// $Id: admin_forum_reorder.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $
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

if (isset($_POST['cat_orders'])) $cat_orders = $_POST['cat_orders'];
if (isset($_POST['orders'])) $orders = $_POST['orders'];
if (isset($_POST['cat'])) $cat = $_POST['cat'];
if (isset($_POST['forum'])) $xforum = $_POST['forum'];

if (!empty($_POST['submit'])) {
    for ($i = 0; $i < count($cat_orders); $i++) {
        $sql = "update " . $GLOBALS['xoopsDB']->prefix("xf_categories") . " set cat_order = " . $cat_orders[$i] . " WHERE cat_id=$cat[$i]";
        if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
    		redirect_header("admin_forum_reorder.php", 1, _AM_XFORUM_FORUM_ERROR);
        }
    }

    for ($i = 0; $i < count($orders); $i++) {
        $sql = "update " . $GLOBALS['xoopsDB']->prefix("xf_forums") . " set forum_order = " . $orders[$i] . " WHERE forum_id=".$xforum[$i];
        if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
    		redirect_header("admin_forum_reorder.php", 1, _AM_XFORUM_FORUM_ERROR);
        }
    }
    redirect_header("admin_forum_reorder.php", 1, _AM_XFORUM_BOARDREORDER);
} else {
	include_once XOOPS_ROOT_PATH."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/class/xoopsformloader.php";
    $orders = array();
    $cat_orders = array();
    $xforum = array();
    $cat = array();

    xoops_cp_header();
    $indexAdmin = new ModuleAdmin();
	echo $indexAdmin->addNavigation(basename(__FILE__));
    echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_XFORUM_SETFORUMORDER . "</legend>";
    echo"<br /><br /><table width='100%' border='0' cellspacing='1' class='outer'>"
     . "<tr><td class='odd'>";
    $tform = new XoopsThemeForm(_AM_XFORUM_SETFORUMORDER, "", "");
    $tform->display();
    echo "<form name='reorder' method='post'>";
    echo "<table border='0' width='100%' cellpadding='2' cellspacing='1' class='outer'>";
    echo "<tr>";
    echo "<td class='head' align='center' width='3%' height='16'><strong>" . _AM_XFORUM_REORDERID . "</strong>";
    echo "</td><td class='head' align='left' width='30%'><strong>" . _AM_XFORUM_REORDERTITLE . "</strong>";
    echo "</td><td class='head' align='center' width='5%'><strong>" . _AM_XFORUM_REORDERWEIGHT . "</strong>";
    echo "</td></tr>";
    $category_handler = xoops_getmodulehandler('category', 'xforum');
    $categories = $category_handler->getAllCats();
	$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
	$xforums = $GLOBALS['forum_handler']->getForumsByCategory();

	$xforums_array = array();
	foreach ($xforums as $xforumid => $xforum) {
	    $xforums_array[$xforum->getVar('parent_forum')][] = array(
	    	'forum_order' => intval($xforum->getVar('forum_order')),
		    'forum_id' => $xforumid,
		    'forum_cid' => $xforum->getVar('cat_id'),
		    'forum_name' => $xforum->getVar('forum_name')
		);
	}
	unset($xforums);
	if(count($xforums_array)>0){
        foreach ($xforums_array[0] as $key => $xforum) {
            if (isset($xforums_array[$xforum['forum_id']])) {
                $xforum['subforum'] = $xforums_array[$xforum['forum_id']];
            }
            $xforumsByCat[$xforum['forum_cid']][] = $xforum;
        }
	}

    foreach($categories as $key => $onecat) {
        echo "<tr>";
        echo "<td align='left' class='head'>" . $onecat->getVar('cat_id') . "</td>";
        echo "<input type='hidden' name='cat[]' value='" . $onecat->getVar('cat_id') . "' />";
        echo "<td align='left' nowrap='nowrap' class='head' >" . $onecat->getVar('cat_title') . "</td>";
        echo "<td align='right' class='head'>";
        echo "<input type='text' name='cat_orders[]' value='" . $onecat->getVar('cat_order') . "' size='5' maxlength='5' />";
        echo "</td>";
        echo "</tr>";

	    $xforums = (!empty($xforumsByCat[$onecat->getVar('cat_id')]))?$xforumsByCat[$onecat->getVar('cat_id')]:array();
        if (count($xforums)>0) {
            foreach ($xforums as $key => $xforum) {
                echo "<tr>";
                echo "<td align='right' class='even'>" . $xforum['forum_id'] . "</td>";
                echo "<input type='hidden' name='forum[]' value='" . $xforum['forum_id'] . "' />";
                echo "<td align='left' nowrap='nowrap' class='odd'>" . $xforum['forum_name'] . "</td>";
                echo "<td align='left' class='even'>";
                echo "<input type='text' name='orders[]' value='" . $xforum['forum_order'] . "' size='5' maxlength='5' />";
                echo "</td>";
                echo "</tr>";

                if(isset($xforum['subforum'])){
            		foreach ($xforum['subforum'] as $key => $subforum) {
	                    echo "<tr>";
	                    echo "<td align='right' class='even'></td>";
	                    echo "<input type='hidden' name='forum[]' value='" . $subforum['forum_id'] . "' />";
	                    echo "<td align='left' nowrap='nowrap' class='odd'>";
	                    echo "<table width='100%'><tr>";
	                    echo "<td width='3%' align='right' nowrap='nowrap' class='even'>" . $subforum['forum_id'] . "</td>";
	                    echo "<td width='80%' align='left' nowrap='nowrap' class='odd'>-->&nbsp;" . $subforum['forum_name'] . "</td>";
	                    echo "<td width= '5%' align='right' nowrap='nowrap' class='odd'>";
	                    echo "<input type='text' name='orders[]' value='" . $subforum['forum_order'] . "' size='5' maxlength='5' /></td>";
	                    echo "</td></tr></table>";
	                    echo "<td align='left' class='even'>";
	                    echo "</td>";
	                    echo "</tr>";
                	}
                }
            }
        }
    }
    echo "<tr><td class='even' align='center' colspan='6'>";

    echo "<input type='submit' name='submit' value='" . _SUBMIT . "' />";

    echo "</td></tr>";
    echo "</table>";
    echo "</form>";
}

echo"</td></tr></table>";
echo "</fieldset>";
echo chronolabs_inline(false); 
xoops_cp_footer();

?>