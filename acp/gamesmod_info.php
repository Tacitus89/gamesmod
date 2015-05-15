<?php
/**
*
* @package Games Mod for phpBB3.1
* @copyright (c) 2015 Marco Candian (tacitus@strategie-zone.de)
* @copyright (c) 2009-2011 Martin Eddy (mods@mecom.com.au)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tacitus89\gamesmod\acp;

class gamesmod_info
{
	function module()
	{
		return array(
			'filename'	=> '\tacitus89\gamesmod\acp\gamesmod_module',
			'title'		=> 'ACP_GAMES_INDEX',
			'modes'		=> array(
				'config'	=> array('title' => 'ACP_GAMES_SETTINGS', 	'auth' => 'ext_tacitus89/gamesmod && acl_a_board', 'cat' => array('ACP_GAMES_INDEX')),
				'management'=> array('title' => 'ACP_GAMES_TITLE', 		'auth' => 'ext_tacitus89/gamesmod && acl_a_board', 'cat' => array('ACP_GAMES_INDEX')),
			),
		);
	}
}
