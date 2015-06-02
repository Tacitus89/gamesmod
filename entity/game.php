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
	*	description_bbcode_uid
	*	description_bbcode_bitfield
	*	description_bbcode_options
	*	image
	*	parent
	*	route
	*	genre
	*	developer
	*	publisher
	*	game_release
	*	platform
	*	forum_url
	*	topic_url
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
		'description_bbcode_uid'		=> 'string',
		'description_bbcode_bitfield'	=> 'string',
		'description_bbcode_options'	=> 'integer',
		'image'				=> 'string',
		'route'				=> 'string',
		'genre'				=> 'string',
		'developer'			=> 'string',
		'publisher'			=> 'string',
		'game_release'		=> 'integer',
		'platform'			=> 'string',
		'forum_url'			=> 'string',
		'topic_url'			=> 'string',
		'meta_desc'			=> 'string',
		'meta_keywords'		=> 'string',
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
		'game_release',
		'description_bbcode_options',
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
	* Get description for edit
	*
	* @return string
	* @access public
	*/
	public function get_description_for_edit()
	{
		// Use defaults if these haven't been set yet
		$description = (isset($this->data['description'])) ? $this->data['description'] : '';
		$uid = (isset($this->data['description_bbcode_uid'])) ? $this->data['description_bbcode_uid'] : '';
		$options = (isset($this->data['description_bbcode_options'])) ? (int) $this->data['description_bbcode_options'] : 0;

		// Generate for edit
		$description_data = generate_text_for_edit($description, $uid, $options);

		return $description_data['text'];
	}

	/**
	* Get description for display
	*
	* @param bool $censor_text True to censor the text (Default: true)
	* @return string
	* @access public
	*/
	public function get_description_for_display($censor_text = true)
	{
		// If these haven't been set yet; use defaults
		$description = (isset($this->data['description'])) ? $this->data['description'] : '';
		$uid = (isset($this->data['description_bbcode_uid'])) ? $this->data['description_bbcode_uid'] : '';
		$bitfield = (isset($this->data['description_bbcode_bitfield'])) ? $this->data['description_bbcode_bitfield'] : '';
		$options = (isset($this->data['description_bbcode_options'])) ? (int) $this->data['description_bbcode_options'] : 0;

		$route = $this->get_route();

		// Generate for display
		$description = generate_text_for_display($description, $uid, $bitfield, $options, $censor_text);

		return $description;
	}

	/**
	* Set description
	*
	* @param string $description
	* @return page_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function set_description($description)
	{
		// Prepare the text for storage
		$uid = $bitfield = $flags = '';
		generate_text_for_storage($description, $uid, $bitfield, $flags, $this->description_bbcode_enabled(), $this->description_magic_url_enabled(), $this->description_smilies_enabled());

		// Set the description to our data array
		$this->data['description'] = $description;
		$this->data['description_bbcode_uid'] = $uid;
		$this->data['description_bbcode_bitfield'] = $bitfield;
		// Flags are already set

		return $this;
	}

	/**
	* Check if bbcode is enabled on the description
	*
	* @return bool
	* @access public
	*/
	public function description_bbcode_enabled()
	{
		return ($this->data['description_bbcode_options'] & OPTION_FLAG_BBCODE);
	}

	/**
	* Enable bbcode on the description
	* This should be called before set_description(); description_enable_bbcode()->set_description()
	*
	* @return page_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function description_enable_bbcode()
	{
		$this->set_description_option(OPTION_FLAG_BBCODE);

		return $this;
	}

	/**
	* Disable bbcode on the description
	* This should be called before set_description(); description_disable_bbcode()->set_description()
	*
	* @return page_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function description_disable_bbcode()
	{
		$this->set_description_option(OPTION_FLAG_BBCODE, true);

		return $this;
	}

	/**
	* Check if magic_url is enabled on the description
	*
	* @return bool
	* @access public
	*/
	public function description_magic_url_enabled()
	{
		return ($this->data['description_bbcode_options'] & OPTION_FLAG_LINKS);
	}

	/**
	* Enable magic url on the description
	* This should be called before set_description(); description_enable_magic_url()->set_description()
	*
	* @return page_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function description_enable_magic_url()
	{
		$this->set_description_option(OPTION_FLAG_LINKS);

		return $this;
	}

	/**
	* Disable magic url on the description
	* This should be called before set_description(); description_disable_magic_url()->set_description()
	*
	* @return page_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function description_disable_magic_url()
	{
		$this->set_description_option(OPTION_FLAG_LINKS, true);

		return $this;
	}

	/**
	* Check if smilies are enabled on the description
	*
	* @return bool
	* @access public
	*/
	public function description_smilies_enabled()
	{
		return ($this->data['description_bbcode_options'] & OPTION_FLAG_SMILIES);
	}

	/**
	* Enable smilies on the description
	* This should be called before set_description(); description_enable_smilies()->set_description()
	*
	* @return page_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function description_enable_smilies()
	{
		$this->set_description_option(OPTION_FLAG_SMILIES);

		return $this;
	}

	/**
	* Disable smilies on the description
	* This should be called before set_description(); description_disable_smilies()->set_description()
	*
	* @return page_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function description_disable_smilies()
	{
		$this->set_description_option(OPTION_FLAG_SMILIES, true);

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

	/**
	* Get genre
	*
	* @return string genre
	* @access public
	*/
	public function get_genre()
	{
		return (isset($this->data['genre'])) ? (string) $this->data['genre'] : '';
	}

	/**
	* Set genre
	*
	* @param string $genre
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_genre($genre)
	{
		// Enforce a string
		$genre = (string) $genre;

		// We limit the image length to 255 characters
		if (truncate_string($genre, 255) != $genre)
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('genre', 'TOO_LONG'));
		}

		// Set the image on our data array
		$this->data['genre'] = $genre;

		return $this;
	}

	/**
	* Get developer
	*
	* @return string developer
	* @access public
	*/
	public function get_developer()
	{
		return (isset($this->data['developer'])) ? (string) $this->data['developer'] : '';
	}

	/**
	* Set developer
	*
	* @param string $developer
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_developer($developer)
	{
		// Enforce a string
		$developer = (string) $developer;

		// We limit the image length to 255 characters
		if (truncate_string($developer, 255) != $developer)
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('developer', 'TOO_LONG'));
		}

		// Set the image on our data array
		$this->data['developer'] = $developer;

		return $this;
	}

	/**
	* Get publisher
	*
	* @return string publisher
	* @access public
	*/
	public function get_publisher()
	{
		return (isset($this->data['publisher'])) ? (string) $this->data['publisher'] : '';
	}

	/**
	* Set publisher
	*
	* @param string $publisher
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_publisher($publisher)
	{
		// Enforce a string
		$publisher = (string) $publisher;

		// We limit the image length to 255 characters
		if (truncate_string($publisher, 255) != $publisher)
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('publisher', 'TOO_LONG'));
		}

		// Set the image on our data array
		$this->data['publisher'] = $publisher;

		return $this;
	}

	/**
	* Get game_release
	*
	* @return string game_release
	* @access public
	*/
	public function get_game_release()
	{
		return (isset($this->data['game_release']) && $this->data['game_release'] > 0) ? (string) date('d.m.Y',$this->data['game_release']) : '';
	}

	/**
	* Set game_release
	*
	* @param string $game_release
	* @return page_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \phpbb\pages\exception\out_of_bounds
	*/
	public function set_game_release($game_release)
	{
		$game_release = (string) $game_release;

		if($game_release === '')
		{
			$date = 0;
		}
		else
		{
			//string to time
			$date = strtotime($game_release);

			//conversion not successful
			if(empty($date))
			{
				throw new \tacitus89\gamesmod\exception\unexpected_value(array('game_release', 'ILLEGAL_CHARACTERS'));
			}
		}

		// Set the route on our data array
		$this->data['game_release'] = $date;

		return $this;
	}

	/**
	* Get platform
	*
	* @return string platform
	* @access public
	*/
	public function get_platform()
	{
		return (isset($this->data['platform'])) ? (string) $this->data['platform'] : '';
	}

	/**
	* Set platform
	*
	* @param string $platform
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_platform($platform)
	{
		// Enforce a string
		$platform = (string) $platform;

		// We limit the image length to 255 characters
		if (truncate_string($platform, 255) != $platform)
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('platform', 'TOO_LONG'));
		}

		// Set the image on our data array
		$this->data['platform'] = $platform;

		return $this;
	}

	/**
	* Get forum_url
	*
	* @return string forum_url
	* @access public
	*/
	public function get_forum_url()
	{
		return (isset($this->data['forum_url'])) ? (string) $this->data['forum_url'] : '';
	}

	/**
	* Set forum_url
	*
	* @param string $forum_url
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_forum_url($forum_url)
	{
		// Enforce a string
		$forum_url = (string) $forum_url;

		preg_match('@(viewforum\\.php\\?).*(f=\\d*)@', $forum_url, $hit);

		if(empty($hit))
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('forum_url', 'ILLEGAL_CHARACTERS'));
		}

		// We limit the image length to 255 characters
		if (truncate_string($forum_url, 255) != $forum_url)
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('forum_url', 'TOO_LONG'));
		}

		// Set the image on our data array
		$this->data['forum_url'] = $hit[0];

		return $this;
	}

	/**
	* Get topic_url
	*
	* @return string topic_url
	* @access public
	*/
	public function get_topic_url()
	{
		return (isset($this->data['topic_url'])) ? (string) $this->data['topic_url'] : '';
	}

	/**
	* Set topic_url
	*
	* @param string $topic_url
	* @return game_interface $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \tacitus89\gamesmod\exception\unexpected_value
	*/
	public function set_topic_url($topic_url)
	{
		// Enforce a string
		$topic_url = (string) $topic_url;

		preg_match('@(viewtopic\\.php\\?).*(f=\\d*)?.*(t=\\d*)@', $topic_url, $hit);

		if(empty($hit))
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('topic_url', 'ILLEGAL_CHARACTERS'));
		}

		// We limit the image length to 255 characters
		if (truncate_string($topic_url, 255) != $topic_url)
		{
			throw new \tacitus89\gamesmod\exception\unexpected_value(array('topic_url', 'TOO_LONG'));
		}

		// Set the image on our data array
		$this->data['topic_url'] = $hit[0];

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

	/**
	* Set option helper
	*
	* @param int $option_value Value of the option
	* @param bool $negate Negate (unset) option (Default: False)
	* @param bool $reparse_description Reparse the description after setting option (Default: True)
	* @return null
	* @access protected
	*/
	protected function set_description_option($option_value, $negate = false, $reparse_description = true)
	{
		// Set description_bbcode_options to 0 if it does not yet exist
		$this->data['description_bbcode_options'] = (isset($this->data['description_bbcode_options'])) ? $this->data['description_bbcode_options'] : 0;

		// If we're setting the option and the option is not already set
		if (!$negate && !($this->data['description_bbcode_options'] & $option_value))
		{
			// Add the option to the options
			$this->data['description_bbcode_options'] += $option_value;
		}

		// If we're unsetting the option and the option is already set
		if ($negate && $this->data['description_bbcode_options'] & $option_value)
		{
			// Subtract the option from the options
			$this->data['description_bbcode_options'] -= $option_value;
		}

		// Reparse the description
		if ($reparse_description && !empty($this->data['description']))
		{
			$description = $this->data['description'];

			decode_message($description, $this->data['description_bbcode_uid']);

			$this->set_description($description);
		}
	}
}
