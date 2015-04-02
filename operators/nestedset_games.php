<?php
/**
*
* Board Rules extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace tacitus89\gamesmod\operators;

/**
* Nestedset class for GamesMod
*/
class nestedset_games extends \phpbb\tree\nestedset
{
	/**
	* Construct
	*
	* @param \phpbb\db\driver\driver_interface $db Database connection
	* @param \phpbb\lock\db $lock Lock class used to lock the table when moving forums around
	* @param string $table_name Table name
	* @return \tacitus89\gamesmod\operators\nestedset_games
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\lock\db $lock, $table_name)
	{
		parent::__construct(
			$db,
			$lock,
			$table_name,
			'GAMES_NESTEDSET_',
			'',
			array(),
			array(
				'item_id'		=> 'game_id',
				'parent_id'		=> 'game_parent_id',
				'left_id'		=> 'game_left_id',
				'right_id'		=> 'game_right_id',
				'item_parents'	=> 'game_parents',
			)
		);
	}

	/**
	* Get the games data from the database
	*
	* @param int $parent_id Category to display games from, 0 for all
	* @return array Array of games data from the database
	* @access public
	*/
	public function get_games_data($parent_id)
	{
		return ($parent_id) ? $this->get_subtree_data($parent_id, true, false) : $this->get_all_tree_data();
	}

	/**
	* Update the tree for an item inserted in the database
	*
	* @param int $item_id The item to be added
	* @return array Array with updated data, if the item was added successfully
	*				Empty array otherwise
	* @access public
	*/
	public function add_to_nestedset($item_id)
	{
		return $this->add_item_to_nestedset($item_id);
	}
}
