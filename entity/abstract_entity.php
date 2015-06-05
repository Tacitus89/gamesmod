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
* Abstract Entity for all Entities
*/
abstract class abstract_entity
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

	/**
	* Some fields must be unsigned (>= 0)
	**/
	protected static $validate_unsigned;

	/**
	* Generated from entity attribute the sql column
	* Only for entity in entity and not for entity in entity in entity...
	*
	* @param array $table_prefix declare the prefix of tables
	* @return string The finished sql column
	* @access public
	* @throws \tacitus89\gamesmod\exception\out_of_bounds
	*/
	public static function get_sql_fields($table_prefix = array())
	{
		//get fields data
		$fields = static::$fields;

		//declare new fields
		$new_fields = array();

		//get the called class
		$called_class = substr(get_called_class(), strrpos(get_called_class(), '\\')+1);

		if(!empty($table_prefix))
		{
			//Go through all fields and renamed it
			foreach ($fields as $key => $value)
			{
				//If value a object
				if($value === 'object')
				{
					//get class of object
					$class = __NAMESPACE__. '\\' .static::$classes[$key];
					//get the fields of the object
					$new_fields[] = $class::get_sql_fields(array('this' => $table_prefix[$key]));
				}
				//set renamed fields
				$new_fields[] = $table_prefix['this'] .'.'. $key .' AS '. $called_class .'_'. $key;
			}
		}
		else
		{
			//Go through all fields and renamed it
			foreach ($fields as $key => $value)
			{
				if($value === 'object')
				{
					//if object have subobject, it must be set a table_prefix
					throw new \tacitus89\gamesmod\exception\invalid_argument(array($key, 'FIELD_MISSING'));
				}
				$new_fields[] = $key .' AS '. $called_class .'_'. $key;
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

		//get class name
		$class = substr(get_called_class(), strrpos(get_called_class(), '\\')+1) .'_';

		// Go through the basic fields and set them to our data array
		foreach (static::$fields as $field => $type)
		{
			// If the data wasn't sent to us, throw an exception
			if (!isset($data[$class.$field]))
			{
				throw new \tacitus89\gamesmod\exception\invalid_argument(array($field, 'FIELD_MISSING'));
			}

			// If the type is a method on this class, call it
			if (method_exists($this, $type))
			{
				$this->$type($data[$class.$field]);
			}
			//Special case: if type a object!
			elseif($type === 'object')
			{
				//Get subclass
				$subclass = __NAMESPACE__. '\\' .static::$classes[$field];

				//Generating the subclass
				$this->data[$field] = new $subclass($this->db, $this->game_cat_table);

				//Import the data to subclass
				$this->data[$field]->import($data);
			}
			else
			{
				// settype passes values by reference
				$value = $data[$class.$field];

				// We're using settype to enforce data types
				settype($value, $type);

				$this->data[$field] = $value;
			}
		}

		foreach (static::$validate_unsigned as $field)
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
}
