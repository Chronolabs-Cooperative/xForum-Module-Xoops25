<?php

// $Id: action.email.php,v 4.03 2008/06/05 16:23:35 wishcraft Exp $
// ------------------------------------------------------------------------ //
// This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License 2.0 as published by //
// the Free Software Foundation; either version 2 of the License, or        //
// (at your option) any later version.                                      //
//                                                                          //
// You may not change or alter any portion of this comment or credits       //
// of supporting developers from this source code or any supporting         //
// source code which is considered copyrighted (c) material of the          //
// original comment or credit authors.                                      //
//                                                                          //
// This program is distributed in the hope that it will be useful,          //
// but WITHOUT ANY WARRANTY; without even the implied warranty of           //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
// GNU General Public License for more details.                             //
//                                                                          //
// You should have received a copy of the GNU General Public License        //
// along with this program; if not, write to the Free Software              //
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------ //
//  Author: wishcraft (S.A.R., sales@chronolabs.org.au)                      //
//  URL: http://www.chronolabs.org.au/forums/X-Forum/0,17,0,0,100,0,DESC,0   //
//  Project: X-Forum 4                                                       //
// ------------------------------------------------------------------------ //

include '../../../../../mainfile.php';
require_once("config.php");

$GLOBALS['myts'] = MyTextSanitizer::getInstance();

$email_to = $GLOBALS['myts']->stripSlashesGPC($_POST["email"]);
if(!checkEmail($email_to)) {
	include XOOPS_ROOT_PATH."/header.php";
	echo "<div class=\"resultMsg\">"."Invalid email";
	echo "<br clear=\"all\" /><br /><input type=\"button\" value=\""._CLOSE."\" onclick=\"window.close()\"></div>";
	
	include XOOPS_ROOT_PATH."/footer.php";
    exit();
}
$title = $GLOBALS['myts']->stripSlashesGPC($_POST["title"]);
$content = $GLOBALS['myts']->stripSlashesGPC($_POST["content"]);
$xoopsMailer = getMailer();
$xoopsMailer->useMail();
$xoopsMailer->setToEmails($email_to);
if(is_object($GLOBALS['xoopsUser'])){
	$xoopsMailer->setFromEmail($GLOBALS['xoopsUser']->getVar("email", "E"));
	$xoopsMailer->setFromName($GLOBALS['xoopsUser']->getVar("uname", "E"));
}else{
	$xoopsMailer->setFromName(forum_getIP(true));				
}
$xoopsMailer->setSubject($title);
$xoopsMailer->setBody($content);
$xoopsMailer->send();

include XOOPS_ROOT_PATH."/header.php";
echo "<div class=\"resultMsg\">".$config["title"];
echo "<br clear=\"all\" /><br /><input type=\"button\" value=\""._CLOSE."\" onclick=\"window.close()\"></div>";

include XOOPS_ROOT_PATH."/footer.php";
?>