<?php
 
// necessary to open the dokuwiki session
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__)).'/../../../');
require_once(DOKU_INC.'inc/init.php');
require_once DOKU_INC.'lib/plugins/freechat/phpfreechat/src/phpfreechat.class.php';

$plist = $_SESSION['freechat_params_list'];
$chat = new phpFreeChat( $plist );
 
?>