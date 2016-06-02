<?php

// $Id: admin_report.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.chronolabs.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License 2.0 as published by //
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
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, http://www.chronolabs.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //

include('admin_header.php');
include_once XOOPS_ROOT_PATH."/class/pagenav.php";


$op = !empty($_GET['op'])? $_GET['op'] : (!empty($_POST['op'])?$_POST['op']:"default");
$item = !empty($_GET['op'])? $_GET['item'] : (!empty($_POST['item'])?$_POST['item']:"process");

$GLOBALS['start'] = (isset($_GET['start']))?$_GET['start']:0;
$report_handler = xoops_getmodulehandler('report', 'xforum');

xoops_cp_header();
switch($op){
	case "save":
		$report_ids = $_POST['report_id'];
		$report_memos = isset($_POST['report_memo'])?$_POST['report_memo']:array();
		foreach($report_ids as $rid => $value){
			if($value) {
				$report_obj = $report_handler->get($rid);
				if($item == 'processed') {
					$report_handler->delete($report_obj);
				}
				if($item == 'process'){
					$report_obj->setVar("report_result", 1);					
					$report_obj->setVar("report_memo", $report_memos[$rid]);					
					$report_handler->insert($report_obj);
				}
			}
		}
		redirect_header( "admin_report.php?item=$item", 1);

		break;

	case "process":
	default:
		include_once XOOPS_ROOT_PATH."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/class/xoopsformloader.php";

		if($item == 'process'){
			$process_result = 0;
			$item_other = 'processed';
			$title_other = _AM_XFORUM_PROCESSEDREPORT;
			$extra = _AM_XFORUM_REPORTEXTRA;
		}else{
			$process_result = 1;
			$item_other = 'process';
			$title_other = _AM_XFORUM_PROCESSREPORT;
			$extra = _DELETE;
		}

		$limit = 10;
		$indexAdmin = new ModuleAdmin();
		echo $indexAdmin->addNavigation(basename(__FILE__));
		echo "<fieldset><legend style='font-weight: bold; color: #900;'>" .  _AM_XFORUM_REPORTADMIN . "</legend>";
		echo"<br />";
		echo "<a style='border: 1px solid #5E5D63; color: #000000; font-family: verdana, tahoma, arial, helvetica, sans-serif; font-size: 1em; padding: 4px 8px; text-align:center;' href=\"admin_report.php?item=$item_other\">".$title_other."</a><br /><br />";

		echo '<form action="'.xoops_getenv('PHP_SELF').'" method="post">';
		echo "<table border='0' cellpadding='4' cellspacing='1' width='100%' class='outer'>";
		echo "<tr align='center'>";
		echo "<td class='bg3' width='80%'>"._AM_XFORUM_REPORTTITLE."</td>";
		echo "<td class='bg3' width='10%'>".$extra."</td>";
		echo "</tr>";

		$reports = $report_handler->getAllReports(0, "ASC", $limit, $GLOBALS['start'], $process_result);
		foreach($reports as $report){
			$post_link = "<a href=\"".XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar('dirname')."/viewtopic.php?post_id=". $report['post_id'] ."&amp;topic_id=". $report['topic_id'] ."&amp;forum=". $report['forum_id'] ."&amp;viewmode=thread\" target=\"checkreport\">".$GLOBALS['myts']->htmlSpecialChars($report['subject'])."</a>";
			$checkbox = '<input type="checkbox" name="report_id['.$report['report_id'].']" value="1" checked="checked" />';
			if($item == 'process'){
				$memo = '<input type="text" name="report_memo['.$report['report_id'].']" maxlength="255" size="80" />';
			}else{
				$memo = $GLOBALS['myts']->htmlSpecialChars($report['report_memo']);
			}

			echo "<tr class='odd' align='left'>";
			echo "<td>"._AM_XFORUM_REPORTPOST.': '. $post_link . "</td>";
			echo "<td align='center'>" . $report['report_id'] . "</td>";
			echo "</tr>";
			echo "<tr class='odd' align='left'>";
			echo "<td>"._AM_XFORUM_REPORTTEXT.': '. $GLOBALS['myts']->htmlSpecialChars($report['report_text']) . "</td>";
			$uid = intval($report['reporter_uid']);
			$reporter_name = forum_getUnameFromId( $uid, $GLOBALS['xforumModuleConfig']['show_realname']);
			$reporter = (!empty($uid))? "<a href='" . XOOPS_URL . "/userinfo.php?uid=".$uid."'>".$reporter_name."</a><br />":"";

			echo "<td align='center'>" . $reporter.long2ip($report['reporter_ip']) . "</td>";
			echo "</tr>";
			echo "<tr class='odd' align='left'>";
			echo "<td>"._AM_XFORUM_REPORTMEMO.': '. $memo . "</td>";
			echo "<td align='center' >" . $checkbox . "</td>";
			echo "</tr>";
			echo "<tr colspan='2'><td height='2'></td></tr>";
		}
		$submit = new XoopsFormButton('', 'submit', _SUBMIT, 'submit');
		echo "<tr colspan='2'><td align='center'>".$submit->render()."</td></tr>";
		$hidden = new XoopsFormHidden('op', 'save');
		echo $hidden->render();
		$hidden = new XoopsFormHidden('item', $item);
		echo $hidden->render()."</form>";

		echo "</table>";

		$nav = new XoopsPageNav($report_handler->getCount(new Criteria("report_result", $process_result)), $limit, $GLOBALS['start'], "start", "item=".$item);
		echo $nav->renderNav(4);

		echo "</fieldset>";

		break;
}
echo chronolabs_inline(false); 
xoops_cp_footer();

?>