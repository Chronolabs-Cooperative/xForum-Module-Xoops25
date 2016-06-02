<?php

// $Id: online.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $
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
//  Author: wishcraft (S.A.R., sales@chronolabs.org.au)                      //
//  URL: http://www.chronolabs.org.au/forums/X-Forum/0,17,0,0,100,0,DESC,0   //
//  Project: X-Forum 4                                                       //
//  ------------------------------------------------------------------------ //
class xforumOnlineHandler
{
    var $xforum;
    var $forum_object;
    var $xforumtopic;
	var $user_ids = array();
	
    function init($xforum = null, $xforumtopic = null)
    {
        if (is_object($xforum)) {
            $this->forum = $xforum->getVar('forum_id');
            $this->forum_object = $xforum;
        } else {
            $this->forum = intval($xforum);
            $this->forum_object = $xforum;
        }
        if (is_object($GLOBALS['xforumtopic'])) {
            $this->forumtopic = $GLOBALS['xforumtopic']->getVar('topic_id');
            if(empty($this->forum))  $this->forum = $GLOBALS['xforumtopic']->getVar('forum_id');
        } else {
            $this->forumtopic = intval($GLOBALS['xforumtopic']);
        }

        $this->update();
    }

    function update()
    {
        mt_srand((double)microtime() * 1000000);
        // set gc probabillity to 10% for now..
        if (mt_rand(1, 100) < 11) {
            $this->gc(300);
        }
        if (is_object($GLOBALS['xoopsUser'])) {
            $uid = $GLOBALS['xoopsUser']->getVar('uid');
            $uname = $GLOBALS['xoopsUser']->getVar('uname');
            $name = $GLOBALS['xoopsUser']->getVar('name');
        } else {
            $uid = 0;
            $uname = '';
            $name = '';
        }

        $xoops_online_handler = xoops_gethandler('online');
		$xoopsupdate = $xoops_online_handler->write($uid, $uname, time(), $GLOBALS['xforumModule']->getVar('mid'), $_SERVER['REMOTE_ADDR']);
		if(!$xoopsupdate){
			forum_message("xforum online upate error");
		}

		$uname = (empty($GLOBALS['xforumModuleConfig']['show_realname'])||empty($name))?$uname:$name;
        $this->write($uid, $uname, time(), $this->forum, $_SERVER['REMOTE_ADDR'], $this->forumtopic);
    }

    function &show_online()
    {
        
		$user_handler = xoops_gethandler('user');
		
        if ($this->forumtopic) {
	        $criteria = new Criteria('online_topic', $this->forumtopic);
        } elseif ($this->forum) {
	        $criteria = new Criteria('online_forum', $this->forum);
        } else {
	        $criteria = null;
        }
        $users = $this->getAll($criteria);
        $num_total = count($users);

		$num_user = 0;
		$users_id = array();
		$users_online = array();
        for ($i = 0; $i < $num_total; $i++) {
	        if(empty($users[$i]['online_uid'])) continue;
	        $user = $user_handler->get($users[$i]['online_uid']);
	        $show = false;
	        if (is_object($user))
		        foreach($user->getGroups() as $groupid)
		        	if (in_array($groupid, $GLOBALS['xforumModuleConfig']['show_groups']))
		        		$show = true;
	        if ($show==true) {
		        $users_id[] = $users[$i]['online_uid'];
		        $users_online[$users[$i]['online_uid']] = array(
		        	"link" => XOOPS_URL . "/userinfo.php?uid=" . $users[$i]['online_uid'],
		        	"uname" => $users[$i]['online_uname'],
		        );
		        $num_user ++;
	        }
        }
        $num_anonymous = $num_total - $num_user;
        $GLOBALS['online'] = array();
        $GLOBALS['online']['image'] = forum_displayImage($GLOBALS['xforumImage']['whosonline']);
		$GLOBALS['online']['num_total'] = $num_total;
		$GLOBALS['online']['num_user'] = $num_user;
		$GLOBALS['online']['num_anonymous'] = $num_anonymous;
        $administrator_list = forum_isModuleAdministrators($users_id, $GLOBALS['xforumModule']->getVar("mid"));
        foreach ($users_online as $uid=>$user) {
            if(!empty($administrator_list[$uid])){
                $user['level']= 2;
            }
            elseif(forum_isModerator($this->forum_object, $uid)){
                $user['level']= 1;
            }
            else{
                $user['level']= 0;
            }
            $GLOBALS['online']["users"][] = $user;
        }

        return $GLOBALS['online'];
    }

    /**
     * Write online information to the database
     *
     * @param int $uid UID of the active user
     * @param string $uname Username
     * @param string $timestamp
     * @param string $xforum Current forum
     * @param string $ip User's IP adress
     * @return bool TRUE on success
     */
    function write($uid, $uname, $time, $xforum, $ip, $xforumtopic)
    {
	    

    	$uid = intval($uid);
        if ($uid > 0) {
            $sql = "SELECT COUNT(*) FROM " . $GLOBALS["xoopsDB"]->prefix('xf_online') . " WHERE online_uid=" . $uid;
        } else {
            $sql = "SELECT COUNT(*) FROM " . $GLOBALS["xoopsDB"]->prefix('xf_online') . " WHERE online_uid=" . $uid . " AND online_ip='" . $ip . "'";
        }
		list($count) = $GLOBALS["xoopsDB"]->fetchRow($GLOBALS["xoopsDB"]->queryF($sql));
        if ($count > 0) {
            $sql = "UPDATE " . $GLOBALS["xoopsDB"]->prefix('xf_online') . " SET online_updated= '" . $time . "', online_forum = '" . $xforum . "', online_topic = '" . $GLOBALS['xforumtopic'] . "' WHERE online_uid = " . $uid;
            if ($uid == 0) {
                $sql .= " AND online_ip='" . $ip . "'";
            }
        } else {
            $sql = sprintf("INSERT INTO %s (online_uid, online_uname, online_updated, online_ip, online_forum, online_topic) VALUES (%u, %s, %u, %s, %u, %u)", $GLOBALS["xoopsDB"]->prefix('xf_online'), $uid, $GLOBALS["xoopsDB"]->quoteString($uname), $time, $GLOBALS["xoopsDB"]->quoteString($ip), $xforum, $GLOBALS['xforumtopic']);
        }
        if (!$GLOBALS["xoopsDB"]->queryF($sql)) {
	        forum_message("can not update online info: ".$sql);
            return false;
        }
        
    	$mysql_version = substr(trim(mysql_get_server_info()), 0, 3);
    	/* for MySQL 4.1+ */
    	if($mysql_version >= "4.1"):

		$sql = 	"DELETE FROM ".$GLOBALS["xoopsDB"]->prefix('xf_online').
				" WHERE".
				" ( online_uid > 0 AND online_uid NOT IN ( SELECT online_uid FROM ".$GLOBALS["xoopsDB"]->prefix('online')." WHERE online_module =".$GLOBALS['xforumModule']->getVar('mid')." ) )".
				" OR ( online_uid = 0 AND online_ip NOT IN ( SELECT online_ip FROM ".$GLOBALS["xoopsDB"]->prefix('online')." WHERE online_module =".$GLOBALS['xforumModule']->getVar('mid')." AND online_uid = 0 ) )";
        
		if($result = $GLOBALS["xoopsDB"]->queryF($sql)){
	        return true;
        }else{
	        forum_message("clean xoops online error: ".$sql);
	        return false;
        }

        
        else: 
        $sql = 	"DELETE ".$GLOBALS["xoopsDB"]->prefix('xf_online')." FROM ".$GLOBALS["xoopsDB"]->prefix('xf_online').
        		" LEFT JOIN ".$GLOBALS["xoopsDB"]->prefix('online')." AS aa ".
        		" ON ".$GLOBALS["xoopsDB"]->prefix('xf_online').".online_uid = aa.online_uid WHERE ".$GLOBALS["xoopsDB"]->prefix('xf_online').".online_uid > 0 AND aa.online_uid IS NULL";
        $result = $GLOBALS["xoopsDB"]->queryF($sql);
        $sql = 	"DELETE ".$GLOBALS["xoopsDB"]->prefix('xf_online')." FROM ".$GLOBALS["xoopsDB"]->prefix('xf_online').
        		" LEFT JOIN ".$GLOBALS["xoopsDB"]->prefix('online')." AS aa ".
        		" ON ".$GLOBALS["xoopsDB"]->prefix('xf_online').".online_ip = aa.online_ip WHERE ".$GLOBALS["xoopsDB"]->prefix('xf_online').".online_uid = 0 AND aa.online_ip IS NULL";
        $result = $GLOBALS["xoopsDB"]->queryF($sql);
        return true;
        endif;
    }

    /**
     * Garbage Collection
     *
     * Delete all online information that has not been updated for a certain time
     *
     * @param int $expire Expiration time in seconds
     */
    function gc($expire)
    {
	    
        $sql = "DELETE FROM ".$GLOBALS["xoopsDB"]->prefix('xf_online')." WHERE online_updated < ".(time() - intval($expire));
        $GLOBALS["xoopsDB"]->queryF($sql);

        $xoops_online_handler = xoops_gethandler('online');
		$xoops_online_handler->gc($expire);
    }

    /**
     * Get an array of online information
     *
     * @param object $criteria {@link CriteriaElement}
     * @return array Array of associative arrays of online information
     */
    function &getAll($criteria = null)
    {
        $ret = array();
        $limit = $GLOBALS['start'] = 0;
        $sql = 'SELECT * FROM ' . $GLOBALS["xoopsDB"]->prefix('xf_online');
        if (is_object($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' . $criteria->renderWhere();
            $limit = $criteria->getLimit();
            $GLOBALS['start'] = $criteria->getStart();
        }
        $result = $GLOBALS["xoopsDB"]->query($sql, $limit, $GLOBALS['start']);
        if (!$result) {
            return false;
        }
        while ($myrow = $GLOBALS["xoopsDB"]->fetchArray($result)) {
            $ret[] = $myrow;
            if( $myrow["online_uid"] > 0 ){
            	$this->user_ids[] = $myrow["online_uid"];
        	}
            unset($myrow);
        }
        $this->user_ids = array_unique($this->user_ids);
        return $ret;
    }

    function checkStatus($uids)
    {
	    $online_users = array();
        $ret = array();
        if(!empty($this->user_ids)) {
	        $online_users = $this->user_ids;
        }
        else{
        	$sql = 'SELECT online_uid FROM ' . $GLOBALS["xoopsDB"]->prefix('xf_online');
        	if(!empty($uids)) {
        		$sql .= ' WHERE online_uid IN ('.implode(", ",array_map("intval", $uids)).')';
    		}
        			
	        $result = $GLOBALS["xoopsDB"]->query($sql);
	        if (!$result) {
	            return false;
	        }
	        while (list($uid) = $GLOBALS["xoopsDB"]->fetchRow($result)) {
		        $online_users[] = $uid;
	        }
        }
        foreach($uids as $uid){
	        if(in_array($uid, $online_users)){
		        $ret[$uid] = 1;
	        }
        }
        return $ret;
    }
    
    /**
     * Count the number of online users
     *
     * @param object $criteria {@link CriteriaElement}
     */
    function getCount($criteria = null)
    {
        $sql = 'SELECT COUNT(*) FROM ' . $GLOBALS["xoopsDB"]->prefix('xf_online');
        if (is_object($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $GLOBALS["xoopsDB"]->query($sql)) {
            return false;
        }
        list($ret) = $GLOBALS["xoopsDB"]->fetchRow($result);
        return $ret;
    }
}

?>