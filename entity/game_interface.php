<?php
/**
*
* Board Rules extension for the phpBB Forum Software package.
*
* @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace tacitus89\gamesmod\entity;

/**
* Interface for a single game
*
* This describes all of the methods we'll have for a single game
*/
interface game_interface
{
	/**
	* Load the data from the database for this game
	*
	* @param int $id game identifier
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\out_of_bounds
	*/
	public function load($id);

	/**
	* Import data for this game
	*
	* Used when the data is already loaded externally.
	* Any existing data on this game is over-written.
	* All data is validated and an exception is thrown if any data is invalid.
	*
	* @param array $data Data array, typically from the database
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\base
	*/
	public function import($data);

	/**
	* Insert the game for the first time
	*
	* Will throw an exception if the game was already inserted (call save() instead)
	*
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\out_of_bounds
	*/
	public function insert();

	/**
	* Save the current settings to the database
	*
	* This must be called before closing or any changes will not be saved!
	* If adding a game (saving for the first time), you must call insert() or an exeception will be thrown
	*
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\out_of_bounds
	*/
	public function save();

	/**
	* Get id
	*
	* @return int game identifier
	* @access public
	*/
	public function get_id();

	/**
	* Get title
	*
	* @return string title
	* @access public
	*/
	public function get_title();

	/**
	* Set title
	*
	* @param string $title
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_title($title);

	/**
	* Get description
	*
	* @return string description
	* @access public
	*/
	public function get_description();
	
	/**
	* Set description
	*
	* @param string $description
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_description($description);
	
	/**
	* Get image
	*
	* @return string image
	* @access public
	*/
	public function get_image();
	
	/**
	* Set image
	*
	* @param string $image
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_image($image);

	/**
	* Get the parent identifier
	*
	* @return int parent identifier
	* @access public
	*/
	public function get_parent_id();

	/**
	* Get the left identifier (for the tree)
	*
	* @return int left identifier
	* @access public
	*/
	public function get_left_id();

	/**
	* Get the right identifier (for the tree)
	*
	* @return int right identifier
	* @access public
	*/
	public function get_right_id();
}
