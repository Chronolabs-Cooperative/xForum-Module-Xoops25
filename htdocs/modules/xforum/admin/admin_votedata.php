<?php

// $Id: admin_votedata.php,v 4.03 2008/06/05 15:58:12 wishcraft Exp $
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

$op = !empty($_GET['op'])? $_GET['op'] : (!empty($_POST['op'])?$_POST['op']:"");

switch ($op)
{
    case "delvotes":
        $rid = intval($_GET['rid']);
        $topic_id = intval($_GET['topic_id']);
        $sql = $GLOBALS['xoopsDB']->queryF("DELETE FROM " . $GLOBALS['xoopsDB']->prefix('xf_votedata') . " WHERE ratingid = $rid");
        $GLOBALS['xoopsDB']->query($sql);
        forum_updaterating($topic_id);
        redirect_header("admin_votedata.php", 1, _AM_XFORUM_VOTEDELETED);
        break;

    case 'main':
    default:

		$GLOBALS['start'] = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $useravgrating = '0';
        $uservotes = '0';

		$sql = "SELECT * FROM " . $GLOBALS['xoopsDB']->prefix('xf_votedata') . " ORDER BY ratingtimestamp DESC";
        $results = $GLOBALS['xoopsDB']->query($sql, 20, $GLOBALS['start']);
		$votes = $GLOBALS['xoopsDB']->getRowsNum($results);

        $sql = "SELECT rating FROM " . $GLOBALS['xoopsDB']->prefix('xf_votedata') . "";
        $result2 = $GLOBALS['xoopsDB']->query($sql, 20, $GLOBALS['start']);
		$uservotes = $GLOBALS['xoopsDB']->getRowsNum($result2);
        $useravgrating = 0;

        while (list($rating2) = $GLOBALS['xoopsDB']->fetchRow($result2))
        {
            $useravgrating = $useravgrating + $rating2;
        }
        if ($useravgrating > 0)
        {
            $useravgrating = $useravgrating / $uservotes;
            $useravgrating = number_format($useravgrating, 2);
        }

        xoops_cp_header();
		$indexAdmin = new ModuleAdmin();
		echo $indexAdmin->addNavigation(basename(__FILE__));
        

	echo "
		<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_XFORUM_VOTE_DISPLAYVOTES . "</legend>\n
		<div style='padding: 8px;'>\n
		<div><strong>" . _AM_XFORUM_VOTE_USERAVG . ": </strong>$useravgrating</div>\n
		<div><strong>" . _AM_XFORUM_VOTE_TOTALRATE . ": </strong>$uservotes</div>\n
		<div style='padding: 8px;'>\n
		<ul><li>".forum_displayImage($GLOBALS['xforumImage']['delete'], _DELETE)." " . _AM_XFORUM_VOTE_DELETEDSC . "</li></ul>
		<div>\n
		</fieldset>\n
		<br />\n

		<table width='100%' cellspacing='1' cellpadding='2' class='outer'>\n
		<tr>\n
		<th align='center'>" . _AM_XFORUM_VOTE_ID . "</th>\n
		<th align='center'>" . _AM_XFORUM_VOTE_USER . "</th>\n
		<th align='center'>" . _AM_XFORUM_VOTE_IP . "</th>\n
		<th align='center'>" . _AM_XFORUM_VOTE_FILETITLE . "</th>\n
		<th align='center'>" . _AM_XFORUM_VOTE_RATING . "</th>\n
		<th align='center'>" . _AM_XFORUM_VOTE_DATE . "</th>\n
		<th align='center'>" . _AM_XFORUM_ACTION . "</th></tr>\n";

        if ($votes == 0)
        {
            echo "<tr><td align='center' colspan='7' class='head'>" . _AM_XFORUM_VOTE_NOVOTES . "</td></tr>";
        }
        while (list($ratingid, $topic_id, $ratinguser, $rating, $ratinghostname, $ratingtimestamp) = $GLOBALS['xoopsDB']->fetchRow($results))
        {
            $sql = "SELECT topic_title FROM " . $GLOBALS['xoopsDB']->prefix('xf_topics') . " WHERE topic_id=" . $topic_id . "";
            $down_array = $GLOBALS['xoopsDB']->fetchArray($GLOBALS['xoopsDB']->query($sql));

            $formatted_date = formatTimestamp($ratingtimestamp, _DATESTRING);
            $ratinguname = forum_getUnameFromId($ratinguser, $GLOBALS['xforumModuleConfig']['show_realname']);
	echo "
		<tr>\n
		<td class='head' align='center'>$ratingid</td>\n
		<td class='even' align='center'>$ratinguname</td>\n
		<td class='even' align='center' >$ratinghostname</td>\n
		<td class='even' align='left'><a href='".XOOPS_URL."/modules/xforum/viewtopic.php?topic_id=".$topic_id."' target='topic'>".$GLOBALS['myts']->htmlSpecialChars($down_array['topic_title'])."</a></td>\n
		<td class='even' align='center'>$rating</td>\n
		<td class='even' align='center'>$formatted_date</td>\n
		<td class='even' align='center'><strong><a href='admin_votedata.php?op=delvotes&amp;topic_id=$topic_id&amp;rid=$ratingid'>".forum_displayImage($GLOBALS['xforumImage']['delete'], _DELETE)."</a></strong></td>\n
		</tr>\n";
        }
        echo "</table>";
		//Include page navigation
		include_once XOOPS_ROOT_PATH . '/class/pagenav.php';
        $page = ($votes > 20) ? _AM_XFORUM_MINDEX_PAGE : '';
        $pagenav = new XoopsPageNav($page, 20, $GLOBALS['start'], 'start');
        echo '<div align="right" style="padding: 8px;">' . $page . '' . $pagenav->renderImageNav(4) . '</div>';
        break;
}
echo chronolabs_inline(false);
xoops_cp_footer();
?>