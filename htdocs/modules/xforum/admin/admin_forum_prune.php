<?php

// $Id: admin_forum_prune.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $
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
include("admin_header.php");
include_once XOOPS_ROOT_PATH."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/class/xoopsformloader.php";

xoops_cp_header();
$indexAdmin = new ModuleAdmin();
echo $indexAdmin->addNavigation(basename(__FILE__));
echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_XFORUM_PRUNE_TITLE . "</legend>";
echo"<br /><br /><table width='100%' border='0' cellspacing='1' class='outer'>"
 . "<tr><td class='odd'>";

if (!empty($_POST['submit'])) {
    $post_list = null;
    $topic_list = null;
    $topics_number = 0;
    $posts_number = 0;

    if (empty($_POST["forums"]))redirect_header("./admin_forum_prune.php", 1, _AM_XFORUM_PRUNE_FORUMSELERROR);
    elseif (is_array($_POST["forums"]))$selected_forums = implode(",", $_POST["forums"]);
    $prune_days = $GLOBALS['myts']->addSlashes($_POST["days"]);
    $prune_ddays = time() - $prune_days;
    $archive = $GLOBALS['myts']->addSlashes($_POST["archive"]);
    $sticky = $GLOBALS['myts']->addSlashes($_POST["sticky"]);
    $digest = $GLOBALS['myts']->addSlashes($_POST["digest"]);
    $lock = $GLOBALS['myts']->addSlashes($_POST["lock"]);
    $hot = $GLOBALS['myts']->addSlashes($_POST["hot"]);
    if (!empty($_POST["store"]))$store = $GLOBALS['myts']->addSlashes($_POST["store"]);

    $sql = "SELECT t.topic_id FROM " . $GLOBALS['xoopsDB']->prefix("xf_topics") . " t, " . $GLOBALS['xoopsDB']->prefix("xf_posts") . "  p
					WHERE t.forum_id IN (" . $selected_forums . ")
					AND p.post_id =t.topic_last_post_id ";

    if ($sticky) $sql .= " AND t.topic_sticky <> 1 ";
    if ($digest) $sql .= " AND t.topic_digest <> 1 ";
    if ($lock) $sql .= " AND t.topic_status <> 1 ";
    if ($hot != 0) $sql .= " AND t.topic_replies < " . $hot . " ";

    $sql .= " AND p.post_time<= " . $prune_ddays . " ";
    // Ok now we have the sql query completed, go for topic_id's and posts_id's
    $topics = array();
    if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
        return _MD_ERROR;
    }
    // Dave_L code
    while ($row = $GLOBALS['xoopsDB']->fetchArray($result)) {
        $topics[] = $row['topic_id'];
    }
    $topics_number = count($topics);
    $topic_list = implode(',', $topics);

    if ($topic_list != null) {
        $sql = "SELECT post_id FROM " . $GLOBALS['xoopsDB']->prefix("xf_posts") . "
					WHERE topic_id IN (" . $topic_list . ")";

        $posts = array();
        if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
            return _MD_ERROR;
        }
        // Dave_L code
        while ($row = $GLOBALS['xoopsDB']->fetchArray($result)) {
            $posts[] = $row['post_id'];
        }
        $posts_number = count($posts);
        $post_list = implode(',', $posts);
    }
    // OKZ Now we have al posts id and topics id
    if ($post_list != null) {
        // COPY POSTS TO OTHER FORUM
        if ($store != null) {
            $sql = "UPDATE " . $GLOBALS['xoopsDB']->prefix("xf_posts") . " SET forum_id=$store WHERE topic_id IN ($topic_list)";
            if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
                return _AM_XFORUM_ERROR;
            }

            $sql = "UPDATE " . $GLOBALS['xoopsDB']->prefix("xf_topics") . " SET forum_id=$store WHERE topic_id IN ($topic_list)";
            if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
                return _MD_ERROR;
            }
        } else {
            // ARCHIVING POSTS
            if ($archive == 1) {
                $result = $GLOBALS['xoopsDB']->query("SELECT p.topic_id, p.post_id, t.post_text FROM " . $GLOBALS['xoopsDB']->prefix("xf_posts") . " p, " . $GLOBALS['xoopsDB']->prefix("xf_posts_text") . " t WHERE p.post_id IN ($post_list) AND p.post_id=t.post_id");
                while (list($topic_id, $post_id, $post_text) = $GLOBALS['xoopsDB']->fetchRow($result)) {
                    $sql = $GLOBALS['xoopsDB']->query("INSERT INTO " . $GLOBALS['xoopsDB']->prefix("xf_archive") . " (topic_id, post_id, post_text) VALUES ($topic_id, $post_id, '$post_text')");
                }
            }
            // DELETE POSTS
            $sql = "DELETE FROM " . $GLOBALS['xoopsDB']->prefix("xf_posts") . " WHERE topic_id IN ($topic_list)";
            if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
                return _MD_ERROR;
            }
            // DELETE TOPICS
            $sql = "DELETE FROM " . $GLOBALS['xoopsDB']->prefix("xf_topics") . " WHERE topic_id IN ($topic_list)";
            if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
                return _MD_ERROR;
            }
            // DELETE POSTS_TEXT
            $sql = "DELETE FROM " . $GLOBALS['xoopsDB']->prefix("xf_posts_text") . " WHERE post_id IN ($post_list)";
            if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
                return _MD_ERROR;
            }
            // SYNC FORUMS AFTER DELETE
			$GLOBALS['forum_handler']->synchronization();
            // I THINK POSTS AND TOPICS HAVE BEEN DESTROYED :LOL:
        }
    }

    $tform = new XoopsThemeForm(_AM_XFORUM_PRUNE_RESULTS_TITLE, "prune_results", xoops_getenv('PHP_SELF'));
    $tform->addElement(new XoopsFormLabel(_AM_XFORUM_PRUNE_RESULTS_FORUMS, $selected_forums));
    $tform->addElement(new XoopsFormLabel(_AM_XFORUM_PRUNE_RESULTS_TOPICS, $topics_number));
    $tform->addElement(new XoopsFormLabel(_AM_XFORUM_PRUNE_RESULTS_POSTS, $posts_number));
    $tform->display();
} else {
    $sform = new XoopsThemeForm(_AM_XFORUM_PRUNE_TITLE, "prune", xoops_getenv('PHP_SELF'));
    $sform->setExtra('enctype="multipart/form-data"');

    /* Let User select the number of days
	$sform->addElement( new XoopsFormText(_AM_XFORUM_PRUNE_DAYS , 'days', 5, 10,100 ), true );
	*/
    // $sql="SELECT p.topic_id, p.post_id t.post_text FROM ".$GLOBALS['xoopsDB']->prefix("xf_posts")." p, ".$GLOBALS['xoopsDB']->prefix("xf_posts_text")." t WHERE p.post_id IN ($post_list) AND p.post_id=t.post_id";
    // $result = $GLOBALS['xoopsDB']->query();
    // Days selected by selbox (better error control :lol:)
    $days = new XoopsFormSelect(_AM_XFORUM_PRUNE_DAYS, 'days', null , 1, false);
    $days->addOptionArray(array(604800 => _AM_XFORUM_PRUNE_WEEK, 1209600 => _AM_XFORUM_PRUNE_2WEEKS, 2592000 => _AM_XFORUM_PRUNE_MONTH, 5184000 => _AM_XFORUM_PRUNE_2MONTH, 10368000 => _AM_XFORUM_PRUNE_4MONTH, 31536000 => _AM_XFORUM_PRUNE_YEAR , 63072000 => _AM_XFORUM_PRUNE_2YEARS));
    $sform->addElement($days);

    $checkbox = new XoopsFormCheckBox(_AM_XFORUM_PRUNE_FORUMS, 'forums');
    $radiobox = new XoopsFormRadio(_AM_XFORUM_PRUNE_STORE, 'store');
    // PUAJJ I HATE IT, please tidy up
    $sql = "SELECT forum_name, forum_id FROM " . $GLOBALS['xoopsDB']->prefix("xf_forums") . " ORDER BY forum_id";
    if ($result = $GLOBALS['xoopsDB']->query($sql)) {
        if ($myrow = $GLOBALS['xoopsDB']->fetchArray($result)) {
            do {
                $checkbox->addOption($myrow['forum_id'], $myrow['forum_name']);
                $radiobox->addOption($myrow['forum_id'], $myrow['forum_name']);
            } while ($myrow = $GLOBALS['xoopsDB']->fetchArray($result));
        } else {
            echo "NO FORUMS";
        }
    } else {
        echo "DB ERROR";
    }

    $sform->addElement($checkbox);

    $sticky_confirmation = new XoopsFormRadio(_AM_XFORUM_PRUNE_STICKY, 'sticky', 1);
    $sticky_confirmation->addOption(1, _AM_XFORUM_PRUNE_YES);
    $sticky_confirmation->addOption(0, _AM_XFORUM_PRUNE_NO);
    $sform->addElement($sticky_confirmation);

    $digest_confirmation = new XoopsFormRadio(_AM_XFORUM_PRUNE_DIGEST, 'digest', 1);
    $digest_confirmation->addOption(1, _AM_XFORUM_PRUNE_YES);
    $digest_confirmation->addOption(0, _AM_XFORUM_PRUNE_NO);
    $sform->addElement($digest_confirmation);

    $lock_confirmation = new XoopsFormRadio(_AM_XFORUM_PRUNE_LOCK, 'lock', 0);
    $lock_confirmation->addOption(1, _AM_XFORUM_PRUNE_YES);
    $lock_confirmation->addOption(0, _AM_XFORUM_PRUNE_NO);
    $sform->addElement($lock_confirmation);

    $hot_confirmation = new XoopsFormSelect(_AM_XFORUM_PRUNE_HOT, 'hot', null , 1, false);
    $hot_confirmation->addOptionArray(array('0' => 0, '5' => 5, '10' => 10, '15' => 15, '20' => 20, '25' => 25, '30' => 30));
    $sform->addElement($hot_confirmation);

    $sform->addElement($radiobox);

    $archive_confirmation = new XoopsFormRadio(_AM_XFORUM_PRUNE_ARCHIVE, 'archive', 1);
    $archive_confirmation->addOption(1, _AM_XFORUM_PRUNE_YES);
    $archive_confirmation->addOption(0, _AM_XFORUM_PRUNE_NO);
    $sform->addElement($archive_confirmation);

    $button_tray = new XoopsFormElementTray('', '');
    $button_tray->addElement(new XoopsFormButton('', 'submit', _AM_XFORUM_PRUNE_SUBMIT, 'submit'));
    $button_tray->addElement(new XoopsFormButton('', 'reset', _AM_XFORUM_PRUNE_RESET, 'reset'));
    $sform->addElement($button_tray);

    $sform->display();
}

echo"</td></tr></table>";
echo "</fieldset>";
echo chronolabs_inline(false);
xoops_cp_footer();

?>