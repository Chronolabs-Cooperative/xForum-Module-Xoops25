<?php

// $Id: admin_cat_manager.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $
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
include('admin_header.php');
xoops_cp_header();

$op = !empty($_GET['op'])? $_GET['op'] : (!empty($_POST['op'])?$_POST['op']:"");
$cat_id = intval( !empty($_GET['cat_id'])? $_GET['cat_id'] : (!empty($_POST['cat_id'])?$_POST['cat_id']:0) );

$category_handler = xoops_getmodulehandler('category', 'xforum');

switch ($op) {
    case "manage":
        $categories = $category_handler->getAllCats(false, true, '', true);
        if (count($categories)==0) {
            $indexAdmin = new ModuleAdmin();
			echo $indexAdmin->addNavigation(basename(__FILE__).'?op=manage');
            echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_XFORUM_CREATENEWCATEGORY . "</legend>";
            echo "<br />";
            newCategory();
            echo "</fieldset>";

            break;
        }

        $indexAdmin = new ModuleAdmin();
		echo $indexAdmin->addNavigation(basename(__FILE__).'?op=manage');
        
        echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_XFORUM_CATADMIN . "</legend>";
        echo"<br />";
        echo "<a style='border: 1px solid #5E5D63; color: #000000; font-family: verdana, tahoma, arial, helvetica, sans-serif; font-size: 1em; padding: 4px 8px; text-align:center;' href='admin_cat_manager.php'>" . _AM_XFORUM_CREATENEWCATEGORY . "</a><br /><br />";

        echo "<table border='0' cellpadding='4' cellspacing='1' width='100%' class='outer'>";
        echo "<tr align='center'>";
        echo "<td class='bg3'>" . _AM_XFORUM_CATEGORY1 . "</td>";
        //echo "<td class='bg3' width='10%'>" . _AM_XFORUM_STATE . "</td>";
        echo "<td class='bg3' width='10%'>" . _AM_XFORUM_EDIT . "</td>";
        echo "<td class='bg3' width='10%'>" . _AM_XFORUM_DELETE . "</td>";
        echo "</tr>";

        foreach($categories as $key => $onecat) {
            $cat_edit_link = "<a href=\"admin_cat_manager.php?op=mod&cat_id=" . $onecat->getVar('cat_id') . "\">".forum_displayImage($GLOBALS['xforumImage']['edit'], _EDIT)."</a>";
            $cat_del_link = "<a href=\"admin_cat_manager.php?op=del&cat_id=" . $onecat->getVar('cat_id') . "\">".forum_displayImage($GLOBALS['xforumImage']['delete'], _DELETE)."</a>";
            $cat_title_link = "<a href=\"".XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/index.php?cat=" . $onecat->getVar('cat_id') . "\">".$onecat->getVar('cat_title')."</a>";

            echo "<tr class='odd' align='left'>";
            echo "<td>" . $cat_title_link . "</td>";
            echo "<td align='center'>" . $cat_edit_link . "</td>";
            echo "<td align='center'>" . $cat_del_link . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</fieldset>";
        break;

    case "mod":
        $fc = $category_handler->get($cat_id);
        $indexAdmin = new ModuleAdmin();
		echo $indexAdmin->addNavigation(basename(__FILE__).'?op=manage');
        
        echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_XFORUM_EDITCATEGORY . "</legend>";
        echo"<br />";

        editCategory($cat_id);

        echo "</fieldset>";
        break;

    case "del":
        if (empty($_POST['confirm'])) {
            xoops_confirm(array('op' => 'del', 'cat_id' => intval($_GET['cat_id']), 'confirm' => 1), 'admin_cat_manager.php', _AM_XFORUM_WAYSYWTDTTAL);
            break;
        } else {
            $fc = $category_handler->create(false);
            $fc->setVar('cat_id', $_POST['cat_id']);
            $category_handler->delete($fc);

            redirect_header("admin_cat_manager.php", 2, _AM_XFORUM_CATEGORYDELETED);
        }
        break;

    case "save":

        if ($cat_id) {
            $fc = $category_handler->get($cat_id);
            $message = _AM_XFORUM_CATEGORYUPDATED;
        } else {
            $fc = $category_handler->create();
            $message = _AM_XFORUM_CATEGORYCREATED;
        }

        $fc->setVar('cat_title', @$_POST['title']);
        $fc->setVar('cat_image', $_POST['indeximage']);
        $fc->setVar('cat_order', $_POST['cat_order']);
        $fc->setVar('cat_description', @$_POST['catdescript']);
		$fc->setVar('cat_domain', @$_POST['cat_domain']);
        $fc->setVar('cat_domains', @$_POST['cat_domains']);
        $fc->setVar('cat_languages', @$_POST['cat_languages']);
        //$fc->setVar('cat_state', $_POST['state']);
        $fc->setVar('cat_url', @$_POST['sponurl']);
        //$fc->setVar('cat_showdescript', @$_POST['show']);

        if (!$category_handler->insert($fc)) {
            $message = _AM_XFORUM_DATABASEERROR;
        }
        if($cat_id=$fc->getVar("cat_id") && $fc->isNew()){
		    $gperm_handler = xoops_gethandler("groupperm");
		    $group_list = array(XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS, XOOPS_GROUP_ANONYMOUS);
		    foreach ($group_list as $group_id) {
		        $gperm_handler->addRight("category_access", $cat_id, $group_id, $GLOBALS['xforumModule']->getVar("mid"));
	        }
        }
        redirect_header("admin_cat_manager.php", 2, $message);
        exit();

    case "default":
    default:
        $indexAdmin = new ModuleAdmin();
		echo $indexAdmin->addNavigation(basename(__FILE__).'?op=manage');
        echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_XFORUM_CREATENEWCATEGORY . "</legend>";
        echo "<br />";
        newCategory();
        echo "</fieldset>";
}

echo chronolabs_inline(false); 
xoops_cp_footer();
?>