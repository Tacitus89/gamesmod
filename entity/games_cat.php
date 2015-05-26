<?php
/**
*
* @package Games Mod for phpBB3.1
* @copyright (c) 2015 Marco Candian (tacitus@strategie-zone.de)
* @copyright (c) 2009-2011 Martin Eddy (mods@mecom.com.au)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tacitus89\gamesmod\entity;

/**
* Entity for a single games_cat
*/
class games_cat
{
	/**
	* Data for this entity
	*
	* @var array
	*	id
	*	name
	*	dir
	*	order_id
	*	number
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
	protected $games_cat_table;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface    $db              Database object
	* @param string                               $games_cat_table     Name of the table used to store game data
	* @return \tacitus89\gamesmod\entity\game
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, $games_cat_table)
	{
		$this->db = $db;
		$this->games_cat_table = $games_cat_table;
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
		$sql = 'SELECT gc.id, gc.name, gc.dir, gc.order_id, gc.number
			FROM ' . $this->games_cat_table . ' gc
			WHERE gc.id = ' . (int) $id;
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
	* Load the data from the database for this game by seo_name
	*
	* @param string $seo_name game cat identifier
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\out_of_bounds
	*/
	public function load_by_name($seo_name)
	{
		$sql = 'SELECT gc.id, gc.name, gc.dir, gc.order_id, gc.number
			FROM ' . $this->games_cat_table . ' gc
			WHERE '. $this->db->sql_in_set('gc.name', $seo_name);
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
			'id'					=> 'integer',
			'name'					=> 'set_name',
			'dir'					=> 'string',
			'order_id'				=> 'integer',
			'number'				=> 'integer',
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
			'order_id',
			'number',
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
		if (!empty($this->data['id']))
		{
			// The game already exists
			throw new \tacitus89\gamesmod\exception\out_of_bounds('id');
		}

		// Make extra sure there is no id set
		unset($this->data['id']);

		// Insert the game data to the database
		$sql = 'INSERT INTO ' . $this->games_cat_table . ' ' . $this->db->sql_build_array('INSERT', $this->data);
		$this->db->sql_query($sql);

		// Set the id using the id created by the SQL insert
		$this->data['id'] = (int) $this->db->sql_nextid();

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
		if (empty($this->data['id']))
		{
			// The game does not exist
			throw new \tacitus89\gamesmod\exception\out_of_bounds('id');
		}

		$sql = 'UPDATE ' . $this->games_cat_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $this->data) . '
			WHERE id = ' . $this->get_id();
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

		// We limit the name length to 30 characters
		if (truncate_string($name, 30) != $name)
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('name', 'TOO_LONG'));
		}

		// Set the name on our data array
		$this->data['name'] = $name;

		return $this;
	}

	/**
	* Get dir
	*
	* @return string dir
	* @access public
	*/
	public function get_dir()
	{
		return (isset($this->data['dir'])) ? (string) $this->data['dir'] : '';
	}

	/**
	* Set dir
	*
	* @param string $dir
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_dir($dir)
	{
		// Enforce a string
		$dir = (string) $dir;

		// We limit the dir length to 30 characters
		if (truncate_string($dir, 30) != $dir)
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('dir', 'TOO_LONG'));
		}

		// Set the dir on our data array
		$this->data['dir'] = $dir;

		return $this;
	}

	/**
	* Get the order_id identifier
	*
	* @return int order_id identifier
	* @access public
	*/
	public function get_order_id()
	{
		return (isset($this->data['order_id'])) ? (int) $this->data['order_id'] : 0;
	}

	/**
	* Set order_id
	*
	* @param integer $order_id
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_order_id($order_id)
	{
		// Enforce a integer
		$order_id = (integer) $order_id;

		// If the data is less than 0, it's not unsigned and we'll throw an exception
		if ($order_id < 0)
		{
			throw new \tacitus89\gamesmod\exception\out_of_bounds($order_id);
		}

		// Set the order_id on our data array
		$this->data['order_id'] = $order_id;

		return $this;
	}

	/**
	* Get the number of objects in the cat
	*
	* @return int number
	* @access public
	*/
	public function get_number()
	{
		return (isset($this->data['number'])) ? (int) $this->data['number'] : 0;
	}
}
