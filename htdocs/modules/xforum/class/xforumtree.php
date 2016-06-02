<?php 
// $Id: xforumtree.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $
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
 
if (!defined("XOOPS_ROOT_PATH")) {
	exit();
}
include_once XOOPS_ROOT_PATH . "/class/xoopstree.php";

class xforumTree extends XoopsTree {
    var $prefix = '&nbsp;&nbsp;';
    var $increment = '&nbsp;&nbsp;';
    var $postArray = '';

    function xforumTree($table_name, $id_name = "post_id", $pid_name = "pid")
    {
        $this->XoopsTree($table_name, $id_name, $pid_name);
    } 

    function setPrefix($val = '')
    {
        $this->prefix = $val;
        $this->increment = $val;
    } 

    function getAllPostArray($sel_id, $order = '')
    {
        $this->postArray = $this->getAllChild($sel_id, $order);
    } 

    function setPostArray($postArray)
    {
        $this->postArray = $postArray;
    } 
    // returns an array of first child objects for a given id($sel_id)
    function getPostTree($postTree_array, $pid = 0, $prefix = '&nbsp;&nbsp;')
    {
        if (!is_array($post_array)) $postTree_array = array();
        foreach($this->postArray as $post) {
            if ($post->getVar('pid') == $pid) {
                $postTree_array[] = array('prefix' => $prefix,
                    'icon' => $post->getVar('icon'),
                    'post_time' => date(_DATESTRING, $post->getVar('post_time')),
                    'post_id' => $post->getVar('post_id'),
                    'forum_id' => $post->getVar('forum_id'),
                    'subject' => $post->getVar('subject'),
                    'poster_name' => $post->getVar('poster_name'),
                    'uid' => $post->getVar('uid')
                    );                
                $postTree_array = array_merge($postTree_array, $this->getPostTree($postTree_array, $post->getVar('post_id'), $prefix.$this->increment));
              } 
        } 
        return $postTree_array;
    } 
} 

?>