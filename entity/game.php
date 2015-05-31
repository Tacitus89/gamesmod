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
class game extends abstract_entity
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
	* All of fields of this objects
	*
	**/
	protected static $fields = array(
		'id'				=> 'integer',
		'parent'			=> 'object',
		'name'				=> 'set_name',
		'description'		=> 'string',
		'image'				=> 'string',
		'route'				=> 'string',
	);

	/**
	* All object must be assigned to a class
	**/
	protected static $classes = array(
		'parent'			=> 'games_cat',
	);

	/**
	* Some fields must be unsigned (>= 0)
	**/
	protected static $validate_unsigned = array(
		'id',
	);

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
		$data = array();

		$sql = 'SELECT '. game::get_sql_fields(array('this' => 'g', 'parent' => 'gc')) .'
			FROM ' . $this->games_table . ' g
			LEFT JOIN '. $this->game_cat_table .' gc ON g.parent = gc.id
			WHERE g.id = ' . (int) $id;
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
	* @param string $seo_name game identifier
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\out_of_bounds
	*/
	public function load_by_name($seo_name)
	{
		$data = array();

		$sql = 'SELECT '. game::get_sql_fields(array('this' => 'g', 'parent' => 'gc')) .'
			FROM ' . $this->games_table . ' g
			LEFT JOIN '. $this->game_cat_table .' gc ON g.parent = gc.id
			WHERE '. $this->db->sql_in_set('g.route', $seo_name);
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

		//save the parent object to parent
		$parent = $this->data['parent'];
		//set parent-id to parent
		$this->data['parent'] = $parent->get_id();

		// Insert the game data to the database
		$sql = 'INSERT INTO ' . $this->games_table . ' ' . $this->db->sql_build_array('INSERT', $this->data);
		$this->db->sql_query($sql);

		// Set the game_id using the id created by the SQL insert
		$this->data['id'] = (int) $this->db->sql_nextid();

		//set parent object back
		$this->data['parent'] = $parent;

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

		//save the parent object to parent
		$parent = $this->data['parent'];
		//set parent-id to parent
		$this->data['parent'] = $parent->get_id();

		$sql = 'UPDATE ' . $this->games_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $this->data) . '
			WHERE id = ' . $this->get_id();
		$this->db->sql_query($sql);

		//set parent object back
		$this->data['parent'] = $parent;

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
	* Get the parent object
	*
	* @return object Object games_cat class
	* @access public
	*/
	public function get_parent()
	{
		return $this->data['parent'];
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

		//Generated new games_cat object
		$this->data['parent'] = new games_cat($this->db, $this->game_cat_table);

		//Load the data for new parent
		$this->data['parent']->load($parent);

		return $this;
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
