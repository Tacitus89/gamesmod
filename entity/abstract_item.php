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
abstract class abstract_item
{
	/**
	* All of fields of this objects
	*
	**/
	protected static $fields;

	/**
	* All object must be assigned to a class
	**/
	protected static $classes;

	public static function get_sql_fields($table_prefix = array(), $prefix = '')
	{
		//get fields data
		$fields = static::$fields;

		$new_fields = array();

		if($prefix != '' && !empty($table_prefix))
		{
			foreach ($fields as $key => $value)
			{
				$new_fields[] = $table_prefix .'.'. $key .' AS '. $prefix.$key;
			}
		}
		if(!empty($table_prefix))
		{
			foreach ($fields as $key => $value)
			{
				if($value === 'object')
				{
					$class = '\tacitus89\gamesmod\entity\\'.static::$classes[$key];
					$new_fields[] = $class::get_sql_fields(array('this' => $table_prefix[$key]));
				}
				$new_fields[] = $table_prefix['this'] .'.'. $key .' AS '. basename(get_called_class()) .'_'. $key;
			}
		}

		return implode(", ", $new_fields);
	}

	/**
	* Load the data from the database for this game
	*
	* @param int $id game identifier
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\out_of_bounds
	*/
	abstract public function load($id);

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

		/*
		if(isset($data['dir']))
		{
			$this->dir = (string) $data['dir'];
			unset($data['dir']);
		}*/

		//$data = $this->set_parent($data);
		$class = (new \ReflectionClass($this))->getShortName() .'_';
		print_r($data);

		// Go through the basic fields and set them to our data array
		foreach (static::$fields as $field => $type)
		{
			echo $class;
			echo $field;
			// If the data wasn't sent to us, throw an exception
			if (!isset($data[$class.$field]))
			{
				echo 'oho!'. $class.$field;
				throw new \tacitus89\gamesmod\exception\invalid_argument(array($field, 'FIELD_MISSING'));
			}

			// If the type is a method on this class, call it
			if (method_exists($this, $type))
			{
				$this->$type($data[$class.$field]);
			}
			elseif($type === 'object')
			{
				echo 'huhu';
				$subclass = '\tacitus89\gamesmod\entity\\'.static::$classes[$field];
				$this->data[$field] = new $subclass($this->db, $this->game_cat_table);
				$this->data[$field]->import($data);
			}
			else
			{
				// settype passes values by reference
				$value = $data[$class.$field];
				echo $value;

				// We're using settype to enforce data types
				settype($value, $type);

				$this->data[$field] = $value;
			}
		}

		// Some fields must be unsigned (>= 0)
		$validate_unsigned = array(
			'id',
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

	public function set_data($data, $field)
	{
		$fields = static::$fields;

		$field_data = array();

		$class = (new \ReflectionClass($this))->getShortName() .'_';

		foreach ($fields as $key => $value)
		{
			$field_data[$key] = $data[$class.$key];
			echo $field_data[$key];
			unset($data[$class.$key]);
		}
		//$data[$field] = $this->import($field_data);
		$this->data = $field_data;
		//$data[$field] = $this;

		return $data;
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
	abstract public function insert();

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
	abstract public function save();

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
