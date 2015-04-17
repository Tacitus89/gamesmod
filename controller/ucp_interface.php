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
interface ucp_interface
{
	/**
	* Display the games in cat for this extension
	*
	* @return null
	* @access public
	*/
	public function display_games();
	
	/**
	* Display the games
	*
	* @param int $parent_id Category to display games from; default: 0
	* @return null
	* @access public
	*/
	public function display_owned_games($parent_id = 0);
	
	/**
	* Adding a list of owned_games
	*
	* @param int $parent_id Category to display games from; default: 0
	* @return null
	* @access public
	*/
	public function add_user_game($parent_id);
	
	/**
	* Remove a list of owned_games
	*
	* @param int $parent_id Category to display games from; default: 0
	* @return null
	* @access public
	*/
	public function remove_user_game($parent_id);
	
	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action);
}