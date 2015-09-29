<?php

$meta['title'] = array('string');
$meta['height'] = array('numeric');
$meta['template'] = array('dirchoice','_dir' => DOKU_INC.'lib/plugins/freechat/phpfreechat/themes/');
$meta['language'] = array('dirchoice','_dir' => DOKU_INC.'lib/plugins/freechat/phpfreechat/i18n/');
$meta['frozen_nick'] = array('onoff');
$meta['channels'] = array('string');
$meta['frozen_channels'] = array('string');
$meta['admin_group'] = array('string');
$meta['fullname'] = array('onoff');
