<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
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
