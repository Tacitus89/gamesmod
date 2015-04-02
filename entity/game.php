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
* Entity for a single game
*/
class game implements game_interface
{
	/**
	* Data for this entity
	*
	* @var array
	*	game_id
	*	game_left_id
	*	game_right_id
	*	game_parent_id
	*	game_title
	*	game_description
	*	game_image
	*	game_parents
	* @access protected
	*/
	protected $data;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/**
	* The database table the games are stored in
	*
	* @var string
	*/
	protected $games_table;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface    $db              Database object
	* @param string                               $games_table     Name of the table used to store game data
	* @return \tacitus89\gamesmod\entity\game
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, $games_table)
	{
		$this->db = $db;
		$this->games_table = $games_table;
	}

	/**
	* Load the data from the database for this game
	*
	* @param int $id game identifier
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\out_of_bounds
	*/
	public function load($id)
	{
		$sql = 'SELECT *
			FROM ' . $this->games_table . '
			WHERE game_id = ' . (int) $id;
		$result = $this->db->sql_query($sql);
		$this->data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($this->data === false)
		{
			// A game does not exist
			throw new \tacitus89\gamesmod\exception\out_of_bounds('game_id');
		}

		return $this;
	}

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
	public function import($data)
	{
		// Clear out any saved data
		$this->data = array();

		// All of our fields
		$fields = array(
			// column							=> data type (see settype())
			'game_id'							=> 'integer',
			'game_left_id'						=> 'integer',
			'game_right_id'						=> 'integer',
			'game_parent_id'					=> 'integer',
			'game_title'						=> 'set_title', // call set_title()
			'game_description'					=> 'string',
			'game_image'						=> 'string',
			'game_parents'						=> 'string',
		);

		// Go through the basic fields and set them to our data array
		foreach ($fields as $field => $type)
		{
			// If the data wasn't sent to us, throw an exception
			if (!isset($data[$field]))
			{
				throw new \tacitus89\gamesmod\exception\invalid_argument(array($field, 'FIELD_MISSING'));
			}

			// If the type is a method on this class, call it
			if (method_exists($this, $type))
			{
				$this->$type($data[$field]);
			}
			else
			{
				// settype passes values by reference
				$value = $data[$field];

				// We're using settype to enforce data types
				settype($value, $type);

				$this->data[$field] = $value;
			}
		}

		// Some fields must be unsigned (>= 0)
		$validate_unsigned = array(
			'game_id',
			'game_left_id',
			'game_right_id',
			'game_parent_id',
		);

		foreach ($validate_unsigned as $field)
		{
			// If the data is less than 0, it's not unsigned and we'll throw an exception
			if ($this->data[$field] < 0)
			{
				throw new \tacitus89\gamesmod\exception\out_of_bounds($field);
			}
		}

		return $this;
	}

	/**
	* Insert the game for the first time
	*
	* Will throw an exception if the game was already inserted (call save() instead)
	*
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\out_of_bounds
	*/
	public function insert()
	{
		if (!empty($this->data['game_id']))
		{
			// The game already exists
			throw new \tacitus89\gamesmod\exception\out_of_bounds('game_id');
		}

		// Resets values required for the nested set system
		$this->data['game_parent_id'] = 0;
		$this->data['game_left_id'] = 0;
		$this->data['game_right_id'] = 0;
		$this->data['game_parents'] = '';

		// Make extra sure there is no game_id set
		unset($this->data['game_id']);

		// Insert the game data to the database
		$sql = 'INSERT INTO ' . $this->games_table . ' ' . $this->db->sql_build_array('INSERT', $this->data);
		$this->db->sql_query($sql);

		// Set the game_id using the id created by the SQL insert
		$this->data['game_id'] = (int) $this->db->sql_nextid();

		return $this;
	}

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
	public function save()
	{
		if (empty($this->data['game_id']))
		{
			// The game does not exist
			throw new \tacitus89\gamesmod\exception\out_of_bounds('game_id');
		}

		$sql = 'UPDATE ' . $this->games_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $this->data) . '
			WHERE game_id = ' . $this->get_id();
		$this->db->sql_query($sql);

		return $this;
	}

	/**
	* Get id
	*
	* @return int game identifier
	* @access public
	*/
	public function get_id()
	{
		return (isset($this->data['game_id'])) ? (int) $this->data['game_id'] : 0;
	}

	/**
	* Get title
	*
	* @return string title
	* @access public
	*/
	public function get_title()
	{
		return (isset($this->data['game_title'])) ? (string) $this->data['game_title'] : '';
	}

	/**
	* Set title
	*
	* @param string $title
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_title($title)
	{
		// Enforce a string
		$title = (string) $title;

		// We limit the title length to 200 characters
		if (truncate_string($title, 200) != $title)
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('title', 'TOO_LONG'));
		}

		// Set the title on our data array
		$this->data['game_title'] = $title;

		return $this;
	}
	
	/**
	* Get description
	*
	* @return string description
	* @access public
	*/
	public function get_description()
	{
		return (isset($this->data['game_description'])) ? (string) $this->data['game_description'] : '';
	}

	/**
	* Set description
	*
	* @param string $description
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_description($description)
	{
		// Enforce a string
		$description = (string) $description;

		// We limit the description length to 255 characters
		if (truncate_string($description, 255) != $description)
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('description', 'TOO_LONG'));
		}

		// Set the description on our data array
		$this->data['game_description'] = $description;

		return $this;
	}
	
	/**
	* Get image
	*
	* @return string image
	* @access public
	*/
	public function get_image()
	{
		return (isset($this->data['game_image'])) ? (string) $this->data['game_image'] : '';
	}

	/**
	* Set image
	*
	* @param string $image
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_image($image)
	{
		// Enforce a string
		$image = (string) $image;

		// We limit the image length to 200 characters
		if (truncate_string($image, 200) != $image)
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('image', 'TOO_LONG'));
		}

		// Set the image on our data array
		$this->data['game_image'] = $image;

		return $this;
	}

	/**
	* Get the parent identifier
	*
	* @return int parent identifier
	* @access public
	*/
	public function get_parent_id()
	{
		return (isset($this->data['game_parent_id'])) ? (int) $this->data['game_parent_id'] : 0;
	}

	/**
	* Get the left identifier (for the tree)
	*
	* @return int left identifier
	* @access public
	*/
	public function get_left_id()
	{
		return (isset($this->data['game_left_id'])) ? (int) $this->data['game_left_id'] : 0;
	}

	/**
	* Get the right identifier (for the tree)
	*
	* @return int right identifier
	* @access public
	*/
	public function get_right_id()
	{
		return (isset($this->data['game_right_id'])) ? (int) $this->data['game_right_id'] : 0;
	}
}
