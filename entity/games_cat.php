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
	protected static $subClasses = array();

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
	* @return \tacitus89\gamesmod\entity\game_cat
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, $games_cat_table)
	{
		$this->db = $db;
		$this->db_table = $games_cat_table;
	}

	/**
	* Generated a new Object
	*
	* @param \phpbb\db\driver\driver_interface    $db              Database object
	* @param string                               $games_cat_table     Name of the table used to store game data
	* @return \tacitus89\gamesmod\entity\game_cat
	* @access protected
	*/
	protected static function factory($db, $games_cat_table)
	{
		return new self($db, $games_cat_table);
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
			FROM ' . $this->db_table . ' gc
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
	* Set name
	*
	* @param string $name
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_name($name)
	{
		return $this->set_string('name', $name, 30);
	}

	/**
	* Get dir
	*
	* @return string dir
	* @access public
	*/
	public function get_dir()
	{
		return $this->get_string($this->data['dir']);
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
		return $this->set_string('dir', $dir, 30);
	}

	/**
	* Get the order_id identifier
	*
	* @return int order_id identifier
	* @access public
	*/
	public function get_order_id()
	{
		return $this->get_integer($this->data['order_id']);
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
		return $this->set_integer('order_id', $order_id);
	}

	/**
	* Get the number of objects in the cat
	*
	* @return int number
	* @access public
	*/
	public function get_number()
	{
		return $this->get_integer($this->data['number']);
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
		return $this->get_string($this->data['meta_desc']);
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
		return $this->set_string('meta_desc', $meta_desc);
	}

	/**
	* Get meta_keywords
	*
	* @return string meta_keywords
	* @access public
	*/
	public function get_meta_keywords()
	{
		return $this->get_string($this->data['meta_keywords']);
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
		return $this->set_string('meta_keywords', $meta_keywords);
	}
}
