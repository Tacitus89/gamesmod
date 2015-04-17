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
interface games_cat_interface
{
	/**
	* Get the games_cat
	*
	* @return array Array of game data entities
	* @access public
	*/
	public function get_games_cat();

	/**
	* Add a game
	*
	* @param object $entity game entity with new data to insert
	* @param int $parent_id Category to display games from; default: 0
	* @return game_interface Added game entity
	* @access public
	*/
	public function add_games_cat($entity);

	/**
	* Delete a game_cat
	*
	* @param int $games_cat_id The game_cat identifier to delete
	* @return null
	* @access public
	*/
	public function delete_games_cat($games_cat_id);

	/**
	* Move a game_cat up/down
	*
	* @param int $games_cat_id The game identifier to move
	* @param string $direction The direction (up|down)
	* @return null
	* @access public
	*/
	public function move($games_cat_id, $direction = 'up');
}
