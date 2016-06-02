<?php 
// $Id: menu.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $
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
$module_handler = xoops_gethandler('module');
$GLOBALS['xforumModule'] = $module_handler->getByDirname('xforum');
$i=0;
$adminmenu[$i]['title'] = _MI_XFORUM_ADMENU_DASHBOARD;
$adminmenu[$i]['icon'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/home.png';
$adminmenu[$i]['image'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/home.png';
$adminmenu[$i]['link'] = "admin/admin_dashboard.php";
//$i++;
//$adminmenu[$i]['title'] = _MI_XFORUM_ADMENU_INDEX;
//$adminmenu[$i]['link'] = "admin/index.php";
$i++;
$adminmenu[$i]['title'] = _MI_XFORUM_ADMENU_CATEGORY;
$adminmenu[$i]['icon'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.category.png';
$adminmenu[$i]['image'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.category.png';
$adminmenu[$i]['link'] = "admin/admin_cat_manager.php?op=manage";
$i++;
$adminmenu[$i]['title'] = _MI_XFORUM_ADMENU_FORUM;
$adminmenu[$i]['icon'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.forum.png';
$adminmenu[$i]['image'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.forum.png';
$adminmenu[$i]['link'] = "admin/admin_forum_manager.php?op=manage";
$i++;
$adminmenu[$i]['title'] = _MI_XFORUM_ADMENU_PERMISSION;
$adminmenu[$i]['icon'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.permissions.png';
$adminmenu[$i]['image'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.permissions.png';
$adminmenu[$i]['link'] = "admin/admin_permissions.php";
$i++;
$adminmenu[$i]['title'] = _MI_XFORUM_ADMENU_BLOCK;
$adminmenu[$i]['icon'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.block.png';
$adminmenu[$i]['image'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.block.png';
$adminmenu[$i]['link'] = "admin/admin_blocks.php";
$i++;
$adminmenu[$i]['title'] = _MI_XFORUM_ADMENU_SYNC;
$adminmenu[$i]['icon'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.sync.png';
$adminmenu[$i]['image'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.sync.png';
$adminmenu[$i]['link'] = "admin/admin_forum_manager.php?op=sync";
$i++;
$adminmenu[$i]['title'] = _MI_XFORUM_ADMENU_ORDER;
$adminmenu[$i]['icon'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.reorder.png';
$adminmenu[$i]['image'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.reorder.png';
$adminmenu[$i]['link'] = "admin/admin_forum_reorder.php";
$i++;
$adminmenu[$i]['title'] = _MI_XFORUM_ADMENU_PRUNE;
$adminmenu[$i]['icon'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.prune.png';
$adminmenu[$i]['image'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.prune.png';
$adminmenu[$i]['link'] = "admin/admin_forum_prune.php";
$i++;
$adminmenu[$i]['title'] = _MI_XFORUM_ADMENU_REPORT;
$adminmenu[$i]['icon'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.report.png';
$adminmenu[$i]['image'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.report.png';
$adminmenu[$i]['link'] = "admin/admin_report.php";
$i++;
$adminmenu[$i]['title'] = _MI_XFORUM_ADMENU_DIGEST;
$adminmenu[$i]['icon'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.digest.png';
$adminmenu[$i]['image'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.digest.png';
$adminmenu[$i]['link'] = "admin/admin_digest.php";
$i++;
$adminmenu[$i]['title'] = _MI_XFORUM_ADMENU_VOTE;
$adminmenu[$i]['icon'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.vote.png';
$adminmenu[$i]['image'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.vote.png';
$adminmenu[$i]['link'] = "admin/admin_votedata.php";
$i++;
$adminmenu[$i]['title'] = _MI_XFORUM_ADMENU_FIELDS;
$adminmenu[$i]['icon'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.fields.png';
$adminmenu[$i]['image'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.fields.png';
$adminmenu[$i]['link'] = "admin/admin_field.php";
$i++;
$i++;
$adminmenu[$i]['title'] = _MI_XFORUM_ADMENU_FIELDSPERMS;
$adminmenu[$i]['icon'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.field.permissions.png';
$adminmenu[$i]['image'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/xforum.field.permissions.png';
$adminmenu[$i]['link'] = "admin/admin_field_permissions.php";
$i++;
$adminmenu[$i]['title'] = _MI_XFORUM_ADMENU_ABOUT;
$adminmenu[$i]['icon'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/about.png';
$adminmenu[$i]['image'] = '../../'.$GLOBALS['xforumModule']->getInfo('icons32').'/about.png';
$adminmenu[$i]['link'] = "admin/admin_about.php";

?>