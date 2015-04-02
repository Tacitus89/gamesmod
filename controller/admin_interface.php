<?php
/**
*
* @package GamesMod
* @copyright (c) 2015
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tacitus89\gamesmod\controller;

/**
* Interface for our admin controller
*
* This describes all of the methods we'll use for the admin front-end of this extension
*/
interface admin_interface
{
	/**
	* Display the options a user can configure for this extension
	*
	* @return null
	* @access public
	*/
	public function display_options();
	
	/**
	* Display the games in cat for this extension
	*
	* @return null
	* @access public
	*/
	public function display_games();
	
	/**
	* Add a game
	*
	* @param int $parent_id Category to display rules from; default: 0
	* @return null
	* @access public
	*/
	public function add_game($parent_id = 0);
	
	/**
	* Edit a game
	*
	* @param int $game_id The game identifier to edit
	* @return null
	* @access public
	*/
	public function edit_game($game_id);
	
	/**
	* Delete a game
	*
	* @param int $game_id The game identifier to delete
	* @return null
	* @access public
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
	*/
	public function move_game($game_id, $direction, $amount = 1);
	
	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action);
}