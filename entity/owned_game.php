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
* Entity for a single owned_game
*/
class owned_game implements game_interface
{
	/**
	* Data for this entity
	*
	* @var array
	*	id
	*	name
	*	description
	*	image
	*	parent
	*	play
	*	share
	*	share_id
	* @access protected
	*/
	protected $data;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	
	/** @var \phpbb\user */
	protected $user;

	/**
	* The database table the games are stored in
	*
	* @var string
	*/
	protected $games_table;
	
	/**
	* The database table the games are stored in
	*
	* @var string
	*/
	protected $games_awarded_table;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface    $db              Database object
	* @param string                               $games_table     Name of the table used to store game data
	* @return \tacitus89\gamesmod\entity\game
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, $games_table, $games_awarded_table)
	{
		$this->db = $db;
		$this->user = $user;
		$this->games_table = $games_table;
		$this->games_awarded_table = $games_awarded_table;
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
		$sql = 'SELECT g.id, g.name, g.description, g.image, ga.play, ga.share, ga.share_id
			FROM ' . $this->games_table . ' g
			LEFT JOIN '. $this->games_awarded_table .' ga
			WHERE game_id = ' . (int) $id .'
			AND user_id = '. $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);
		$this->data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($this->data === false)
		{
			// A game does not exist
			throw new \tacitus89\gamesmod\exception\out_of_bounds('id');
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
			// column					=> data type (see settype())
			'id'						=> 'integer',
			'parent'					=> 'integer',
			'name'						=> 'set_name', // call set_title()
			'description'				=> 'string',
			'image'						=> 'string',
			'play'						=> 'integer',
			'share'						=> 'integer',
			'share_id'					=> 'integer',
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
			'id',
			'parent',
			'play',
			'share',
			'share_id',
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
	* Do nothing
	*
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function insert()
	{
		return $this;
	}

	/**
	* Do nothing
	*
	*
	* @access public
	*/
	public function save()
	{
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
		return (isset($this->data['id'])) ? (int) $this->data['id'] : 0;
	}

	/**
	* Get name
	*
	* @return string name
	* @access public
	*/
	public function get_name()
	{
		return (isset($this->data['name'])) ? (string) $this->data['name'] : '';
	}

	/**
	* Set name
	*
	* @param string $name
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_name($name)
	{
		// Enforce a string
		$name = (string) $name;

		// We limit the name length to 200 characters
		if (truncate_string($name, 200) != $name)
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('name', 'TOO_LONG'));
		}

		// Set the name on our data array
		$this->data['name'] = $name;

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
		return (isset($this->data['description'])) ? (string) $this->data['description'] : '';
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
		$this->data['description'] = $description;

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
		return (isset($this->data['image'])) ? (string) $this->data['image'] : '';
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
		$this->data['image'] = $image;

		return $this;
	}

	/**
	* Get the parent identifier
	*
	* @return int parent identifier
	* @access public
	*/
	public function get_parent()
	{
		return (isset($this->data['parent'])) ? (int) $this->data['parent'] : 0;
	}
	
	/**
	* Set parent
	*
	* @param integer $parent
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_parent($parent)
	{
		// Enforce a integer
		$parent = (integer) $parent;

		// If the data is less than 0, it's not unsigned and we'll throw an exception
		if ($parent < 0)
		{
			throw new \tacitus89\gamesmod\exception\out_of_bounds($parent);
		}

		// Set the parent on our data array
		$this->data['parent'] = $parent;

		return $this;
	}
	
	/**
	* Get the play bit
	*
	* @return int play integer
	* @access public
	*/
	public function get_play()
	{
		return (isset($this->data['play'])) ? (int) $this->data['play'] : 0;
	}
	
	/**
	* Set play bit
	*
	* @param integer $bit
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_play($bit)
	{
		// Enforce a integer
		$bit = (integer) $bit;
		//it must be 0 or 1
		if($bit > 0)
		{
			$bit = 1;
		}

		// If the data is less than 0, it's not unsigned and we'll throw an exception
		if ($bit < 0)
		{
			throw new \tacitus89\gamesmod\exception\out_of_bounds($bit);
		}

		// Set the play on our data array
		$this->data['play'] = $bit;

		return $this;
	}
	
	/**
	* Get the share bit
	*
	* @return int share integer
	* @access public
	*/
	public function get_share()
	{
		return (isset($this->data['share'])) ? (int) $this->data['share'] : 0;
	}
	
	/**
	* Set share bit
	*
	* @param integer $bit
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_share($bit)
	{
		// Enforce a integer
		$bit = (integer) $bit;
		//it must be 0 or 1
		if($bit > 0)
		{
			$bit = 1;
		}

		// If the data is less than 0, it's not unsigned and we'll throw an exception
		if ($bit < 0)
		{
			throw new \tacitus89\gamesmod\exception\out_of_bounds($bit);
		}

		// Set the share on our data array
		$this->data['share'] = $bit;

		return $this;
	}
	
	/**
	* Get the share_id
	*
	* @return int share integer
	* @access public
	*/
	public function get_share_id()
	{
		return (isset($this->data['share'])) ? (int) $this->data['share'] : 0;
	}
	
	/**
	* Set share_id
	*
	* @param integer $share_id
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_share_id($share_id)
	{
		// Enforce a integer
		$share_id = (integer) $share_id;

		// If the data is less than 0, it's not unsigned and we'll throw an exception
		if ($bit < 0)
		{
			throw new \tacitus89\gamesmod\exception\out_of_bounds($share_id);
		}

		// Set the share on our data array
		$this->data['share_id'] = $share_id;

		return $this;
	}
}
