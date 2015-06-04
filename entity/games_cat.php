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
class games_cat extends abstract_entity
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
	*	route
	*	meta_desc
	*	meta_keywords
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
	* All of fields of this objects
	*
	**/
	protected static $fields = array(
		'id'					=> 'integer',
		'name'					=> 'set_name',
		'dir'					=> 'string',
		'order_id'				=> 'integer',
		'number'				=> 'integer',
		'route'					=> 'string',
		'meta_desc'				=> 'string',
		'meta_keywords'			=> 'string',
	);

	/**
	* All object must be assigned to a class
	**/
	protected static $classes = array();

	/**
	* Some fields must be unsigned (>= 0)
	**/
	protected static $validate_unsigned = array(
		'id',
		'order_id',
		'number',
	);

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
		$sql = 'SELECT '. games_cat::get_sql_fields(array('this' => 'gc')) .'
			FROM ' . $this->games_cat_table . ' gc
			WHERE gc.id = ' . (int) $id;
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($data === false)
		{
			// A game does not exist
			throw new \tacitus89\gamesmod\exception\out_of_bounds('id');
		}

		//Import data for this game
		$this->import($data);

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
		$sql = 'SELECT '. games_cat::get_sql_fields(array('this' => 'gc')) .'
			FROM ' . $this->games_cat_table . ' gc
			WHERE '. $this->db->sql_in_set('gc.route', $seo_name);
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($data === false)
		{
			// A game does not exist
			throw new \tacitus89\gamesmod\exception\out_of_bounds('id');
		}

		//Import data for this game
		$this->import($data);

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

	/**
	* Set route
	*
	* @param string $route Route text
	* @return page_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_route($route)
	{
		// Enforce a string
		$route = (string) $route;

		// Route is a empty field
		if ($route == '')
		{
			// Set the route on our data array
			$this->data['route'] = '';
			return $this;
		}

		// Route should not contain any special characters
		if (!preg_match('/^[^!"#$%&*\'()+,.\/\\\\:;<=>?@\[\]^`{|}~ ]*$/i', $route))
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('route', 'ILLEGAL_CHARACTERS'));
		}

		// We limit the route length to 100 characters
		if (truncate_string($route, 100) != $route)
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('route', 'TOO_LONG'));
		}

		// Routes must be unique
		if (!$this->get_id() || ($this->get_id() && $this->get_route() != $route))
		{
			$sql = 'SELECT 1
				FROM ' . $this->games_cat_table . "
				WHERE route = '" . $this->db->sql_escape($route) . "'
					AND id <> " . $this->get_id();
			$result = $this->db->sql_query_limit($sql, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				throw new \tacitus89\gamesmod\exception\unexpected_value(array('route', 'NOT_UNIQUE'));
			}
		}

		// Set the route on our data array
		$this->data['route'] = $route;

		return $this;
	}

	/**
	* Get meta_desc
	*
	* @return string meta_desc
	* @access public
	*/
	public function get_meta_desc()
	{
		return (isset($this->data['meta_desc'])) ? (string) $this->data['meta_desc'] : '';
	}

	/**
	* Set meta_desc
	*
	* @param string $meta_desc
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_meta_desc($meta_desc)
	{
		// Enforce a string
		$meta_desc = (string) $meta_desc;

		// We limit the image length to 255 characters
		if (truncate_string($meta_desc, 255) != $meta_desc)
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('meta_desc', 'TOO_LONG'));
		}

		// Set the image on our data array
		$this->data['meta_desc'] = $meta_desc;

		return $this;
	}

	/**
	* Get meta_keywords
	*
	* @return string meta_keywords
	* @access public
	*/
	public function get_meta_keywords()
	{
		return (isset($this->data['meta_keywords'])) ? (string) $this->data['meta_keywords'] : '';
	}

	/**
	* Set meta_keywords
	*
	* @param string $meta_keywords
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_meta_keywords($meta_keywords)
	{
		// Enforce a string
		$meta_keywords = (string) $meta_keywords;

		// We limit the image length to 255 characters
		if (truncate_string($meta_keywords, 255) != $meta_keywords)
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('meta_keywords', 'TOO_LONG'));
		}

		// Set the image on our data array
		$this->data['meta_keywords'] = $meta_keywords;

		return $this;
	}
}
