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

class install_2_0_2 extends \phpbb\db\migration\migration
{
	var $games_version = '2.0.2';

	public function effectively_installed()
	{
		return isset($this->config['games_version']) && version_compare($this->config['games_version'], $this->games_version, '>=');
	}

	static public function depends_on()
	{
		return array('\tacitus89\gamesmod\migrations\install_2_0_1');
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'games_cats'		=> array(
					'number'			=> array('UINT:8', 0),
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			// Set the current version
			array('config.remove', array('games_version', $this->games_version)),
			array('config.add', array('games_mod_version', $this->games_version)),
			array('config.update', array('games_mod_version', $this->games_version)),
			array('custom', array(array($this, 'update_number'))),
		);
	}

	public function update_number()
	{
		$cats = array();

		$sql = 'SELECT id
			FROM ' . $this->table_prefix . 'games_cats';
		$result = $this->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$cats[] = $row['id'];
		}
		$this->db->sql_freeresult($result);

		if(!empty($cats))
		{
			foreach ($cats as $id)
			{
				//count the games
				$sql= 'SELECT COUNT(id) AS number
					FROM ' . $this->table_prefix . 'games
					WHERE ' . $this->db->sql_in_set('parent', $id);
				$result = $this->sql_query($sql);
				$number = (int) $this->db->sql_fetchfield('number');
				$this->db->sql_freeresult($result);

				//update the cat
				$sql = 'UPDATE ' . $this->table_prefix . 'games_cats
				SET '. $this->db->sql_in_set('number', $number) .'
				WHERE '. $this->db->sql_in_set('id', $id);
				$this->sql_query($sql);
			}
		}
	}

}
