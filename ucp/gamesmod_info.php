<?php

/**
*
* @package Games Mod for phpBB3.1
* @copyright (c) 2015 Marco Candian (tacitus@strategie-zone.de)
* @copyright (c) 2009-2011 Martin Eddy (mods@mecom.com.au)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tacitus89\gamesmod\ucp;

class gamesmod_info
{
	function module()
	{
		global $config;

		return array(
			'filename'	=> '\tacitus89\gamesmod\ucp\gamesmod_info',
			'title'		=> 'UCP_GAMES_INDEX',
			'version'	=> $config['games_version'],
			'modes'		=> array(
				'index'	=> array('title' => 'UCP_GAMES_INDEX_TITLE',	'auth' => '',	'cat' => array('UCP_GAMES_INDEX')),
				'add'	=> array('title' => 'UCP_GAMES_ADD_TITLE',		'auth' => '',	'cat' => array('UCP_GAMES_INDEX')),
			),
		);
	}
}
