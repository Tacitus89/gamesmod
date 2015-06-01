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

class install_2_0_4 extends \phpbb\db\migration\migration
{
	var $games_version = '2.0.4';

	public function effectively_installed()
	{
		return isset($this->config['games_mod_version']) && version_compare($this->config['games_mod_version'], $this->games_version, '>=');
	}

	static public function depends_on()
	{
		return array('\tacitus89\gamesmod\migrations\install_2_0_3');
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'games_cats'		=> array(
					'route'			=> array('VCHAR:100', ''),
				),
				$this->table_prefix . 'games'			=> array(
					'route'			=> array('VCHAR:100', ''),
					'genre'			=> array('VCHAR:255', ''),
					'developer'		=> array('VCHAR:255', ''),
					'publisher'		=> array('VCHAR:255', ''),
					'release'		=> array('UINT:11', 0),
					'platform'		=> array('VCHAR:255', ''),
					'meta_desc'		=> array('VCHAR:255', ''),
					'meta_keywords'	=> array('VCHAR:255', ''),
					'description_bbcode_uid'		=> array('VCHAR:8', ''),
					'description_bbcode_bitfield'	=> array('VCHAR:255', ''),
					'description_bbcode_options'	=> array('UINT:11', 7),
				),
			),
			'change_columns'	=> array(
				$this->table_prefix . 'games'			=> array(
					'description'	=> array('MTEXT_UNI', ''),
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			// Set the current version
			array('config.update', array('games_mod_version', $this->games_version)),
			array('config.add', array('game_seo_url', 0)),
		);
	}

}
