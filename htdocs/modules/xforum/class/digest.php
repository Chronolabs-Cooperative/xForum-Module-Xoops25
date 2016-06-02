<?php

// $Id: digest.php,v 4.03 2008/06/05 15:58:14 wishcraft Exp $
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
//  Author: wishcraft (S.A.R., sales@chronolabs.org.au)                      //
//  URL: http://www.chronolabs.org.au/forums/X-Forum/0,17,0,0,100,0,DESC,0   //
//  Project: X-Forum 4                                                       //
// ------------------------------------------------------------------------ //
class Digest extends XoopsObject {
    var $digest_id;
    var $digest_time;
    var $digest_content;

    var $items;
    var $isHtml = false;
    var $isSummary = true;

    function Digest()
    {
        $this->initVar('digest_id', XOBJ_DTYPE_INT);
        $this->initVar('digest_time', XOBJ_DTYPE_INT);
        $this->initVar('digest_content', XOBJ_DTYPE_TXTAREA);
        $this->items = array();
    }

    function setHtml()
    {
        $this->isHtml = true;
    }

    function setSummary()
    {
        $this->isSummary = true;
    }

    function addItem($title, $link, $author, $summary = "")
    {
        $title = $this->cleanup($title);
        $author = $this->cleanup($author);
        if (!empty($summary)) {
            $summary = $this->cleanup($summary);
        }
        $this->items[] = array('title' => $title, 'link' => $link, 'author' => $author, 'summary' => $summary);
    }

    function cleanup($text)
    {
        
        $clean = stripslashes($text);
        $clean = $GLOBALS['myts']->displayTarea($clean, 1, 0, 1);
        $clean = strip_tags($clean);
        $clean = htmlspecialchars($clean, ENT_QUOTES);

        return $clean;
    }

    function buildContent($isSummary = true, $isHtml = false)
    {
        $digest_count = count($this->items);
        $content = "";
        if ($digest_count > 0) {
            $linebreak = ($isHtml)?"<br />":"\n";
            for($i = 0;$i < $digest_count;$i++) {
                if ($isHtml) {
                    $content .= ($i + 1) . ". <a href=" . $this->items[$i]['link'] . ">" . $this->items[$i]['title'] . "</a>";
                } else {
                    $content .= ($i + 1) . ". " . $this->items[$i]['title'] . $linebreak . $this->items[$i]['link'];
                }

                $content .= $linebreak . $this->items[$i]['author'];
                if ($isSummary) $content .= $linebreak . $this->items[$i]['summary'];
                $content .= $linebreak . $linebreak;
            }
        }
        $this->setVar('digest_content', $content);
        return true;
    }
}

class xforumDigestHandler extends XoopsObjectHandler {
    var $last_digest;

    function &create($isNew = true)
    {
        $digest = new Digest();
        if ($isNew) {
            $digest->setNew();
        }
        return $digest;
    }

    function &get($id)
    {
	    $digest = null;
	    $id = intval($id);
        if (!$id) {
	        return $digest;
        }
        $sql = 'SELECT * FROM ' . $this->db->prefix('xf_digest') . ' WHERE digest_id=' . $id;
        if($array = $this->db->fetchArray($this->db->query($sql))){
	        if ($var) return $array[$var];
	        $digest = $this->create(false);
	        $digest->assignVars($array);
        }
        return $digest;
    }

    function process($isForced = false)
    {
        $this->getLastDigest();
        if (!$isForced) {
            $status = $this->checkStatus();
            if ($status < 1) return 1;
        }
        $digest = $this->create();
        $status = $this->buildDigest($digest);
        if (!$status) return 2;
        $status = $this->insert($digest);
        if (!$status) return 3;
        $status = $this->notify($digest);
        if (!$status) return 4;
        return 0;
    }

    function notify($digest)
    {
        $content = $digest->getVar('digest_content');
        $notification_handler = xoops_gethandler('notification');
        $tags['DIGEST_ID'] = $digest->getVar('digest_id');
        $tags['DIGEST_CONTENT'] = $digest->getVar('digest_content', 'E');
        $notification_handler->triggerEvent('global', 0, 'digest', $tags);
        return true;
    }

    function &getAllDigests($start, $perpage = 5)
    {
        if (empty($start)) {
            $GLOBALS['start'] = 0;
        }

        $sql = "SELECT * FROM " . $this->db->prefix('xf_digest') . " ORDER BY digest_id DESC";
        $result = $this->db->query($sql, $perpage, $GLOBALS['start']);
        $ret = array();
        $report_handler = xoops_getmodulehandler('report', 'xforum');
        while ($myrow = $this->db->fetchArray($result)) {
            $ret[] = $myrow; // return as array
        }
        return $ret;
    }

    function getDigestCount()
    {
        $sql = 'SELECT COUNT(*) as count FROM ' . $this->db->prefix("xf_digest");
        $result = $this->db->query($sql);
        if (!$result) {
            return 0;
        } else {
            $array = $this->db->fetchArray($result);
            return $array['count'];
        }
    }

    function getLastDigest()
    {
        $sql = 'SELECT MAX(digest_time) as last_digest FROM ' . $this->db->prefix("xf_digest");
        $result = $this->db->query($sql);
        if (!$result) {
            $this->last_digest = 0;
            // echo "<br />no data:".$query;
        } else {
            $array = $this->db->fetchArray($result);
            $this->last_digest = (isset($array['last_digest']))?$array['last_digest']:0;
        }
    }

    function checkStatus()
    {
        
        if (!isset($this->last_digest)) $this->getLastDigest();
        $deadline = ($GLOBALS['xforumModuleConfig']['email_digest'] == 1)? 60 * 60 * 24:60 * 60 * 24 * 7;
        $time_diff = time() - $this->last_digest;
        return $time_diff - $deadline;
    }

    function insert($digest)
    {
        $content = $digest->getVar('digest_content', 'E');

        $id = $this->db->genId($digest->table . "_digest_id_seq");
        $sql = "INSERT INTO " . $digest->table . " (digest_id, digest_time, digest_content)	VALUES (" . $id . ", " . time() . ", " . $this->db->quoteString($content) . " )";

        if (!$this->db->queryF($sql)) {
            //echo "<br />digest insert error::" . $sql;
            return false;
        }
        if (empty($id)) {
            $id = $this->db->getInsertId();
        }
        $digest->setVar('digest_id', $id);
        return true;
    }

    function delete($digest)
    {
        if (is_object($digest)) $digest_id = $digest->getVar('digest_id');
        else $digest_id = $digest;
        if (!isset($this->last_digest)) $this->getLastDigest();
        if ($this->last_digest == $digest_id) return false; // It is not allowed to delete the last digest
        $sql = "DELETE FROM " . $this->db->prefix("xf_digest") . " WHERE digest_id=" . $digest_id;
        if (!$result = $this->db->queryF($sql)) {
            return false;
        }
        return true;
    }

    function buildDigest($digest)
    {
     
        if (!defined('SUMMARY_LENGTH')) define('SUMMARY_LENGTH', 100);

        $GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
        $thisUser = $GLOBALS['xoopsUser'];
        $GLOBALS['xoopsUser'] = null; // To get posts accessible by anonymous
        $access_forums = $GLOBALS['forum_handler']->getForums(0, 'access'); // get all accessible forums
        $GLOBALS['xoopsUser'] = $thisUser;

        $forum_criteria = ' AND t.forum_id IN (' . implode(',', array_keys($access_forums)) . ')';
        unset($access_forums);
        $approve_criteria = ' AND t.approved = 1 AND p.approved = 1';
        $time_criteria = ' AND t.digest_time > ' . $this->last_digest;

        $karma_criteria = ($GLOBALS['xforumModuleConfig']['enable_karma'])? " AND p.post_karma=0":"";
        $reply_criteria = ($GLOBALS['xforumModuleConfig']['allow_require_reply'])? " AND p.require_reply=0":"";

        $query = 'SELECT t.topic_id, t.forum_id, t.topic_title, t.topic_time, t.digest_time, p.uid, p.poster_name, pt.post_text FROM ' . $this->db->prefix('xf_topics') . ' t, ' . $this->db->prefix('xf_posts_text') . ' pt, ' . $this->db->prefix('xf_posts') . ' p WHERE t.topic_digest = 1 AND p.topic_id=t.topic_id AND p.pid=0 ' . $forum_criteria . $approve_criteria . $time_criteria . $karma_criteria . $reply_criteria . ' AND pt.post_id=p.post_id ORDER BY t.digest_time DESC';
        if (!$result = $this->db->query($query)) {
            //echo "<br />No result:<br />$query";
            return false;
        }
        $rows = array();
        $users = array();
        while ($row = $this->db->fetchArray($result)) {
            $users[$row['uid']] = 1;
            $rows[] = $row;
        }
        if (count($rows) < 1) {
            return false;
        }
        $uids = array_keys($users);
        if (count($uids) > 0) {
            $member_handler = xoops_gethandler('member');
            $user_criteria = new Criteria('uid', "(" . implode(',', $uids) . ")", 'IN');
            $users = $member_handler->getUsers(new Criteria('uid', "(" . implode(',', $uids) . ")", 'IN'), true);
        } else {
            $users = array();
        }

        foreach($rows as $topic) {
            if ($topic['uid'] > 0) {
                if (isset($users[$topic['uid']]) && (is_object($users[$topic['uid']])) && ($users[$topic['uid']]->isActive())) {
                    $topic['uname'] = $users[$topic['uid']]->getVar('uname');
                } else {
                    $topic['uname'] = $GLOBALS['xoopsConfig']['anonymous'];
                }
            } else {
                $topic['uname'] = $topic['poster_name']?$topic['poster_name']:$GLOBALS['xoopsConfig']['anonymous'];
            }
            $summary = xoops_substr(forum_html2text($topic['post_text']), 0, SUMMARY_LENGTH);
            $author = $topic['uname'] . " (" . formatTimestamp($topic['topic_time']) . ")";
            $link = XOOPS_URL . "/modules/" . $GLOBALS['xforumModule']->dirname() . '/viewtopic.php?topic_id=' . $topic['topic_id'] . '&amp;forum=' . $topic['forum_id'];
            $title = $topic['topic_title'];
            $digest->addItem($title, $link, $author, $summary);
        }
        $digest->buildContent();
        return true;
    }
}

?>