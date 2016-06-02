<?php

// $Id: index.php,v 4.03 2008/06/05 16:23:35 wishcraft Exp $
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

function transfer_email(&$data)
{
	
	$_config = require(dirname(__FILE__)."/config.php");
	
	include XOOPS_ROOT_PATH."/header.php";
	require_once(XOOPS_ROOT_PATH . "/class/xoopsformloader.php");
	$content  = str_replace("<br />", "\n", $data["content"]);
	$content  = str_replace("<br>", "\n", $content);
	$content  = forum_html2text($content);
	$content = $data["title"]."\n".$content."\n\n"._MORE."\n".$data["url"];
	$form_email = new XoopsThemeForm(_MD_TRANSFER_EMAIL, "formemail", XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/class/transfer/email/action.email.php");
	$form_email->addElement(new XoopsFormText(_MD_TRANSFER_EMAIL_ADDRESS, "email", 50, 100), true);
	$form_email->addElement(new XoopsFormText(_MD_TRANSFER_EMAIL_TITLE, "title", 50, 255, $data["title"]), true);
	$form_email->addElement(new XoopsFormTextArea(_MD_TRANSFER_EMAIL_CONTENT, "content", $content, 10, 60), true);
	$form_email->addElement(new XoopsFormButton("", "email_submit", _SUBMIT, "submit"));
	$form_email->display();
	$GLOBALS["xoopsOption"]['output_type'] = "plain";
	include XOOPS_ROOT_PATH."/footer.php";
	exit();
}
?>