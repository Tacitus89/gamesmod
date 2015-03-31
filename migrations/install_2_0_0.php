<?php

/**
*
* @package GamesMod
* @copyright (c) 2015
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tacitus89\gamesmod\migrations;

class install_2_0_0 extends \phpbb\db\migration\migration
{
	var $games_version = '2.0.0';
	
	public function effectively_installed()
	{
		return isset($this->config['games_version']) && version_compare($this->config['games_version'], $this->games_version, '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_data()
	{
		return array(
			// Set the current version
			array('config.add', array('games_version', $this->games_version))
		);
	}
}
