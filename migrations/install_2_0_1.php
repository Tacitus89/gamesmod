<?php

/**
*
* @package Games Mod for phpBB3.1
* @copyright (c) 2015 Marco Candian (tacitus@strategie-zone.de)
* @copyright (c) 2009-2011 Martin Eddy (mods@mecom.com.au)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tacitus89\gamesmod\migrations;

class install_2_0_1 extends \phpbb\db\migration\migration
{
	var $games_version = '2.0.1';

	public function effectively_installed()
	{
		return isset($this->config['games_version']) && version_compare($this->config['games_version'], $this->games_version, '>=');
	}

	static public function depends_on()
	{
		return array('\tacitus89\gamesmod\migrations\install_2_0_0');
	}

	public function update_data()
	{
		return array(
			// Set the current version
			array('config.update', array('games_version', $this->games_version)),
		);
	}
}
