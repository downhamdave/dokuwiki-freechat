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
      $this->Lexer->addSpecialPattern('\{\{chat>[^}]*\}\}', $mode, 'plugin_freechat');
  }

  /* parse paramters (if used as {{chat>[chatid=...|rooms=...[,...]]}}) */
  function handle($match, $state, $pos, &$handler) {
    if ( $match == '~~CHAT~~' )
       return array();

    $match = substr($match, 7, -2); // strip markup
    $params = explode('|',$match);
    // provide easy access to parameters param=value
    $data = array();
    foreach ($params as $p) {
        $splitparam = explode('=',$p);
        if ( isset($splitparam[1]) )
           $data[$splitparam[0]] = $splitparam[1];
        else
           $data[$splitparam[0]] = '';
    }
    return $data;
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
        if ( isset ($data['chatid'])  && $data['chatid'] != '' )
           $params['serverid'] = md5($data['chatid']); 

        $params['focus_on_connect'] = true;
        
        $params['language'] = $this->getConf('language');
        $params['theme'] = $this->getConf('template');
        $params['height'] = $this->getConf('height').'px'; 
        $params["title"] = $this->getConf('title'); 
        if ( isset ($data['rooms']) )
           $params["channels"] = explode(',', $data['rooms']);
        else
           $params["channels"] = explode(',', $this->getConf('channels'));
        $params['frozen_nick'] = $this->getConf('frozen_nick');
        $params['frozen_nick'] = !($params['frozen_nick'] == 'off'  ||  $params['frozen_nick'] == 0);
        if ( isset ($data['locked']) )
          $params['frozen_channels'] = explode(',', $data['locked']);
        else if ($this->getConf('frozen_channels') != '')
          $params['frozen_channels'] = explode(',', $this->getConf('frozen_channels'));
        
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
