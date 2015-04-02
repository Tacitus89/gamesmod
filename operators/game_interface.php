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
interface game_interface
{
	/**
	* Get the game
	*
	* @param int $parent_id Category to display game from; default: 0
	* @return array Array of game data entities
	* @access public
	*/
	public function get_games($parent_id = 0);

	/**
	* Add a game
	*
	* @param object $entity game entity with new data to insert
	* @param int $parent_id Category to display game from; default: 0
	* @return game_interface Added game entity
	* @access public
	*/
	public function add_game($entity, $parent_id = 0);

	/**
	* Delete a game
	*
	* @param int $game_id The game identifier to delete
	* @return null
	* @access public
	* @throws \tacitus89\gamesmod\exception\out_of_bounds
	*/
	public function delete_game($game_id);

	/**
	* Move a game up/down
	*
	* @param int $game_id The game identifier to move
	* @param string $direction The direction (up|down)
	* @param int $amount The number of places to move the game
	* @return null
	* @access public
	* @throws \tacitus89\gamesmod\exception\out_of_bounds
	*/
	public function move($game_id, $direction, $amount = 1);

	/**
	* Change game parent
	*
	* @param int $game_id The current game identifier
	* @param int $new_parent_id The new game parent identifier
	* @return null
	* @access public
	* @throws \tacitus89\gamesmod\exception\out_of_bounds
	*/
	public function change_parent($game_id, $new_parent_id);

	/**
	* Get a game's parent game (for use in breadcrumbs)
	*
	* @param int $parent_id Category to display game from
	* @return array Array of game data for a game's parent game
	* @access public
	*/
	public function get_game_parents($parent_id);
}
