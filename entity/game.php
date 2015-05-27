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
class game
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
	*	route
	* @access protected
	*/
	protected $data;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var ContainerInterface */
	protected $container;

	/**
	* The database table the games are stored in
	*
	* @var string
	*/
	protected $games_table;

	/**
	* The database table the game_cat are stored in
	*
	* @var string
	*/
	protected $game_cat_table;

	/**
	* Specifies the path to the image
	*
	* @var string
	*/
	protected $dir;

	/**
	* Specifies the parent entity
	*
	* @var string
	*/
	protected $parent;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface    $db              Database object
	* @param ContainerInterface                   $container       Service container interface
	* @param string                               $games_table     Name of the table used to store game data
	* @param string                               $games_cat_table Name of the table used to store game data
	* @return \tacitus89\gamesmod\entity\game
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, $games_table, $game_cat_table)
	{
		$this->db = $db;
		$this->games_table = $games_table;
		$this->game_cat_table = $game_cat_table;
		$this->dir = '';

		$this->parent = new games_cat($db, $game_cat_table);
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
		$sql = 'SELECT g.id, g.name, g.description, g.parent, g.image, g.route, gc.dir,
				gc.id as parent_id, gc.name as parent_name, gc.dir as parent_dir, gc.order_id as parent_order_id, gc.number as parent_number, gc.route as parent_route
			FROM ' . $this->games_table . ' g
			LEFT JOIN '. $this->game_cat_table .' gc ON g.parent = gc.id
			WHERE g.id = ' . (int) $id;
		$result = $this->db->sql_query($sql);
		$this->data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($this->data === false)
		{
			// A game does not exist
			throw new \tacitus89\gamesmod\exception\out_of_bounds('id');
		}

		$this->dir = $this->data['dir'];
		unset($this->data['dir']);

		$this->include_parent();

		return $this;
	}

	/**
	* Load the data from the database for this game by seo_name
	*
	* @param string $seo_name game identifier
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\out_of_bounds
	*/
	public function load_by_name($seo_name)
	{
		$sql = 'SELECT g.id, g.name, g.description, g.parent, g.image, g.route, gc.dir,
			gc.id as parent_id, gc.name as parent_name, gc.dir as parent_dir, gc.order_id as parent_order_id, gc.number as parent_number, gc.route as parent_route
			FROM ' . $this->games_table . ' g
			LEFT JOIN '. $this->game_cat_table .' gc ON g.parent = gc.id
			WHERE '. $this->db->sql_in_set('g.route', $seo_name);
		$result = $this->db->sql_query($sql);
		$this->data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($this->data === false)
		{
			// A game does not exist
			throw new \tacitus89\gamesmod\exception\out_of_bounds('id');
		}

		$this->dir = $this->data['dir'];
		unset($this->data['dir']);

		$this->data = $this->include_parent($this->data);

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

		if(isset($data['dir']))
		{
			$this->dir = (string) $data['dir'];
			unset($data['dir']);
		}

		$data = $this->include_parent($data);

		// All of our fields
		$fields = array(
			// column					=> data type (see settype())
			'id'						=> 'integer',
			'parent'					=> 'integer',
			'name'						=> 'set_name', // call set_title()
			'description'				=> 'string',
			'image'						=> 'string',
			'route'						=> 'string',
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
		$sql = 'INSERT INTO ' . $this->games_table . ' ' . $this->db->sql_build_array('INSERT', $this->data);
		$this->db->sql_query($sql);

		// Set the game_id using the id created by the SQL insert
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

		$sql = 'UPDATE ' . $this->games_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $this->data) . '
			WHERE id = ' . $this->get_id();
		$this->db->sql_query($sql);

		return $this;
	}

	/**
	* Include parent
	*
	* @param data array
	* @return data array
	* @access private
	*/
	private function include_parent($data)
	{
		$parent_data = array(
			'id'					=> $data['parent_id'],
			'name'					=> $data['parent_name'],
			'dir'					=> $data['parent_dir'],
			'order_id'				=> $data['parent_order_id'],
			'number'				=> $data['parent_number'],
			'route'					=> $data['parent_route'],
		);

		$this->parent->import($parent_data);

		unset($data['parent_id']);
		unset($data['parent_name']);
		unset($data['parent_dir']);
		unset($data['parent_order_id']);
		unset($data['parent_number']);
		unset($data['parent_route']);

		return $data;
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
	* Get the parent: game_cat object
	*
	* @return object game_cat
	* @access public
	*/
	public function get_parent2()
	{
		return $this->parent;
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
	* Get the dir of image
	*
	* @return string dir
	* @access public
	*/
	public function get_dir()
	{
		return (isset($this->dir)) ? (string) $this->dir.'/' : '';
	}

	/**
	* Get route
	*
	* @return string route
	* @access public
	*/
	public function get_route()
	{
		return (isset($this->data['route'])) ? (string) $this->data['route'] : '';
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
				FROM ' . $this->games_table . "
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
}
