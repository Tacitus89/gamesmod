<?php
/**
*
* Board Rules extension for the phpBB Forum Software package.
*
* @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace tacitus89\gamesmod\operators;

/**
* Interface for our game operator
*
* This describes all of the methods we'll have for working with a set of game
*/
interface games_interface
{
	/**
	* Get the games
	*
	* @param int $parent_id Category to display games from
	* @param int $start Start position in the table; default = 0
	* @param int $end End position at table; default = 15
	* @return array Array of game data entities
	* @access public
	*/
	public function get_games($parent_id, $start = 0, $end = 15);
	
	/**
	* Get the number of games
	*
	* @param int $parent_id Category to display games from
	* @return int Number of games
	* @access public
	*/
	public function get_number_games($parent_id);

	/**
	* Add a game
	*
	* @param object $entity game entity with new data to insert
	* @return game_interface Added game entity
	* @access public
	*/
	public function add_game($entity);

	/**
	* Delete a game_cat
	*
	* @param int $games_cat_id The game_cat identifier to delete
	* @return null
	* @access public
	*/
	public function delete_game($game_id);
}
