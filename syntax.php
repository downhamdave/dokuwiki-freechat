<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Luigi Micco <l.micco@tiscali.it>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_freechat extends DokuWiki_Syntax_Plugin {

	function getInfo(){
		return array(
      'author' => 'Luigi micco',
      'email'  => 'l.micco@tiscali.it',
      'date'   => '2011-01-19',
      'name'   => 'Freechat Plugin (syntax component)',
      'desc'   => 'Allow to insert and use phpFreeChat on DokuWiki',
      'url'    => 'http://www.bitlibero.com/dokuwiki/freechat-19.01.2011.zip',
		);
	}

	function getType(){ return 'protected'; }
	function getAllowedTypes() { return array('substition','protected','disabled','formatting'); }
	function getSort(){ return 315; }
	function getPType(){ return 'block'; }
  function connectTo($mode) {
      $this->Lexer->addSpecialPattern('~~CHAT~~', $mode, 'plugin_freechat');
  }

  function handle($match, $state, $pos, &$handler) {
    
    $match = substr($match, 2, -2); // strip markup
    return array($match); 

  }
    
	function render($mode, &$renderer, $data){
		global $conf, $USERINFO, $ID;

    if(auth_quickaclcheck($ID) >= AUTH_READ) {
      if($mode == 'xhtml'){

        $renderer->info['cache'] = FALSE;
        ob_start();
                  
        require_once DOKU_INC.'lib/plugins/freechat/phpfreechat/src/phpfreechat.class.php';

        $params = array();
        $params['serverid'] = md5($conf['title']); 
        $params['focus_on_connect'] = true;
        
        $params['language'] = $this->getConf('language');
        $params['theme'] = $this->getConf('template');
        $params['height'] = $this->getConf('height').'px'; 
        $params["title"] = $this->getConf('title'); 
        $params["channels"] = explode(',', $this->getConf('channels'));
        $params['frozen_nick'] = $this->getConf('frozen_nick');
        if ($this->getConf('frozen_channels') != '') {
          $params['frozen_channels'] = explode(',', $this->getConf('frozen_channels'));
        }  
        
        $params['isadmin']  = false;
        if (($this->getConf('admin_group') != '') && isset($USERINFO['grps'])) {
          $temp = explode(',', $this->getConf('admin_group'));
          foreach($temp as $item) {
            if (in_array(trim($item),$USERINFO['grps'])) {
              $params['isadmin']  = true ;
            }
          }
        }  
        
        $params['startwithsound'] = false;
        $params['display_pfc_logo'] = true;
        $params['showsmileys'] = false;

        $params['nick'] = (isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] : "guest".rand(1,1000));
        if ($this->getConf('fullname')) {
          if (isset($USERINFO['name']) && !empty($USERINFO['name'])) {
            $params['nick'] = $USERINFO['name'];
          }
        }  
      
//          $params['channels'] =  array('Generale');

        $params['data_public_path']   = DOKU_INC.'data/cache/public';
        $params['data_public_url']    = DOKU_URL.'data/cache/public';
        
        $params['data_public_path']   = DOKU_INC.'lib/plugins/freechat/phpfreechat/data/public';
        $params['data_public_url']    = DOKU_URL.'lib/plugins/freechat/phpfreechat/data/public';

//        $params['data_public_path']   = DOKU_INC.'data/tmp';
//        $params['data_public_url']    = DOKU_URL.'data/tmp';

        $params['data_private_path']   = DOKU_INC.'data/cache/freechat/private';

        $params['server_script_path'] = DOKU_INC.'lib/plugins/freechat/backend.php';
        $params['server_script_url']  = DOKU_URL.'lib/plugins/freechat/backend.php';

//        $params['debug']  = true;

        // store in session the parameters list for the backend script
        @session_start();
        $_SESSION['freechat_params_list'] = $params;
         
        $pfc = new phpFreeChat($params);
        $pfc->printChat();
                   
        $content = ob_get_contents();
        ob_end_clean();
        $renderer->doc .= $content;      
/*
echo "<pre>";
print_r($params);
echo "</pre>";
*/
        return true;
      }
    }
		return false;
	}
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
