<?php

// $Id: dl_attachment.php,v 4.04 2008/06/05 15:35:59 wishcraft Exp $


ob_start();
include "header.php";
include XOOPS_ROOT_PATH.'/header.php';

$attach_id = isset($_GET['attachid']) ? strval($_GET['attachid']) : '';
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

if(!$post_id||!$attach_id) die(_MD_NO_SUCH_FILE.': post_id:'.$post_id.'; attachid'.$attachid);

$post_handler = xoops_getmodulehandler('post', 'xforum');
$xforumpost = $post_handler->get($post_id);
if(!$approved = $xforumpost->getVar('approved'))    die(_MD_NORIGHTTOVIEW);
$topic_handler = xoops_getmodulehandler('topic', 'xforum');
$GLOBALS['xforumtopic'] = $topic_handler->getByPost($post_id);
$topic_id = $GLOBALS['xforumtopic']->getVar('topic_id');
if(!$approved = $GLOBALS['xforumtopic']->getVar('approved'))    die(_MD_NORIGHTTOVIEW);
$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
$GLOBALS['viewtopic_forum'] = $GLOBALS['forum_handler']->get($GLOBALS['xforumtopic']->getVar('forum_id'));
if (!$forum_handler->getPermission($GLOBALS['viewtopic_forum']))    die(_MD_NORIGHTTOACCESS);
if (!$topic_handler->getPermission($GLOBALS['viewtopic_forum'], $GLOBALS['xforumtopic']->getVar('topic_status'), "view"))   die(_MD_NORIGHTTOVIEW);

$attachments = $xforumpost->getAttachment();
$attach = $attachments[$attach_id];
if (!$attach) die(_MD_NO_SUCH_FILE);
$file_saved = XOOPS_ROOT_PATH.'/'.$GLOBALS['xforumModuleConfig']['dir_attachments'].'/'.$attach['name_saved'];
if(!file_exists($file_saved)) die(_MD_NO_SUCH_FILE);
if($down = $xforumpost->incrementDownload($attach_id)) {
	$xforumpost->saveAttachment();
}
unset($xforumpost);
$msg = ob_get_contents();
ob_end_clean();

if(!empty($GLOBALS['xforumModuleConfig']["download_direct"])):

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false); 
header("Pragma: no-cache"); 
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("location: ".XOOPS_URL.'/'.$GLOBALS['xforumModuleConfig']['dir_attachments'].'/'.$attach['name_saved']);

else:
$file_display = $attach['name_display'];
$mimetype = $attach['mimetype'];
if (function_exists('mb_http_output')) {
	mb_http_output('pass');
}
header('Expires: 0');
header('Content-Type: '.$mimetype);
if (preg_match("/MSIE ([0-9]\.[0-9]{1,2})/", $HTTP_USER_AGENT)) {
	header('Content-Disposition: inline; filename="'.$file_display.'"');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
} else {
	header('Content-Disposition: attachment; filename="'.$file_display.'"');
	header('Pragma: no-cache');
}
header("Content-Type: application/force-download");
header("Content-Transfer-Encoding: binary");

$handle = fopen($file_saved, "rb");
while (!feof($handle)) {
   $buffer = fread($handle, 4096);
   echo $buffer;
}
fclose($handle);

endif;
?>