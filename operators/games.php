<?php
/**
*
* @package Games Mod for phpBB3.1
* @copyright (c) 2015 Marco Candian (tacitus@strategie-zone.de)
* @copyright (c) 2009-2011 Martin Eddy (mods@mecom.com.au)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tacitus89\gamesmod\operators;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Operator for a set of games_cat
*/
class games
{
	/** @var ContainerInterface */
	protected $container;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/**
	* The database table the game_cat are stored in
	*
	* @var string
	*/
	protected $game_table;

	/**
	* The database table the game_cat are stored in
	*
	* @var string
	*/
	protected $game_cat_table;

	/**
	* The database table the games_awarded are stored in
	*
	* @var string
	*/
	protected $games_awarded_table;

	/**
	* The database table the users are stored in
	*
	* @var string
	*/
	protected $users_table;

	/**
	* Constructor
	*
	* @param ContainerInterface $container		Service container interface
	* @param phpbb\db\driver\driver_interface 	$db
	* @param string							 	$game_table The database table the game_cat are stored in
	* @param string							 	$games_awarded_table The database table the games_awarded_table are stored in
	* @param string							 	$users_table
	* @return \tacitus89\gamesmod\operators\game
	* @access public
	*/
	public function __construct(ContainerInterface $container, \phpbb\db\driver\driver_interface $db, $game_table, $game_cat_table, $games_awarded_table, $users_table)
	{
		$this->container = $container;
		$this->db = $db;
		$this->game_table = $game_table;
		$this->game_cat_table = $game_cat_table;
		$this->games_awarded_table = $games_awarded_table;
		$this->users_table = $users_table;
	}

	/**
	* Get the games
	*
	* @param int $parent_id Category to display games from; default = 0
	* @param int $start Start position in the table; default = 0
	* @param int $end End position at table; default = 0
	* @return array Array of game data entities
	* @access public
	*/
	public function get_games($parent_id = 0, $start = 0, $end = 0)
	{
		$sql= 'SELECT g.id, g.name, g.description, g.parent, g.image, g.route, gc.dir
			FROM ' . $this->game_table . ' g
			LEFT JOIN '. $this->game_cat_table .' gc ON g.parent = gc.id
			WHERE ' . $this->db->sql_in_set('parent', $parent_id) .'
			ORDER BY name ASC';

		return $this->get_sql_result($sql, $start, $end);
	}

	/**
	* Get the games by name
	*
	* @param int $parent_id Category to display games from; default = 0
	* @param int $start Start position in the table; default = 0
	* @param int $end End position at table; default = 0
	* @return array Array of game data entities
	* @access public
	*/
	public function get_games_by_name($parent_name = '', $start = 0, $end = 0)
	{
		$time_start = microtime(true);

		$sql= 'SELECT g.id AS game_id, gc.id AS games_cat_id, gc.name AS games_cat_name, gc.dir AS games_cat_dir, gc.order_id AS games_cat_order_id, gc.number AS games_cat_number, gc.route AS games_cat_route, g.parent AS game_parent, g.name AS game_name, g.description AS game_description, g.image AS game_image, g.route AS game_route 
			FROM ' . $this->game_table . ' g
			LEFT JOIN '. $this->game_cat_table .' gc ON g.parent = gc.id
			WHERE ' . $this->db->sql_in_set('gc.route', $parent_name) .'
			ORDER BY g.name ASC';

		$time_end = microtime(true);
		echo ($time_end - $time_start);

		//echo \tacitus89\gamesmod\entity\game::get_sql(array('this' => g, 'parent' => gc));
		//echo \tacitus89\gamesmod\entity\game::get_sql_fields(array('this' => 'g', 'parent' => 'gc'));

		return $this->get_sql_result($sql, $start, $end);
	}

	/**
	* Get the owned games
	*
	* @param int $user_id The user_id which has the games
	* @param int $parent_id Category to display games from; default = 0
	* @param int $start Start position in the table; default = 0
	* @param int $end End position at table; default = 0
	* @return array Array of game data entities
	* @access public
	*/
	public function get_owned_games($user_id, $parent_id = 0, $start = 0, $end = 0)
	{
		if($parent_id == 0)
		{
			$sql= 'SELECT g.id, g.name, g.description, g.parent, g.image, g.route, gc.dir
				FROM ' . $this->games_awarded_table . ' ga
				JOIN ' . $this->game_table . ' g ON g.id = ga.game_id
				LEFT JOIN '. $this->game_cat_table .' gc ON g.parent = gc.id
				WHERE '. $this->db->sql_in_set('ga.user_id', $user_id) .'
				ORDER BY g.name ASC';
		}
		else
		{
			$sql= 'SELECT g.id, g.name, g.description, g.parent, g.image, g.route, gc.dir
				FROM ' . $this->game_table . ' g
				JOIN ' . $this->games_awarded_table . ' ga ON g.id = ga.game_id
				LEFT JOIN '. $this->game_cat_table .' gc ON g.parent = gc.id
				WHERE '. $this->db->sql_in_set('ga.user_id', $user_id) .'
				AND ' . $this->db->sql_in_set('g.parent', $parent_id) .'
				ORDER BY g.name ASC';
		}
		return $this->get_sql_result($sql, $start, $end);
	}

	/**
	* Get the not owned games
	*
	* @param int $user_id The user_id which has not the games
	* @param int $parent_id Category to display games from; default = 0
	* @param int $start Start position in the table; default = 0
	* @param int $end End position at table; default = 0
	* @return array Array of game data entities
	* @access public
	*/
	public function get_not_owned_games($user_id, $parent_id = 0, $start = 0, $end = 0)
	{
		$sql= 'SELECT g.id, g.name, g.description, g.parent, g.image, g.route, gc.dir
			FROM ' . $this->game_table . ' g
			LEFT OUTER JOIN ' . $this->games_awarded_table . ' ga ON g.id = ga.game_id AND '. $this->db->sql_in_set('ga.user_id', $user_id) .'
			LEFT JOIN '. $this->game_cat_table .' gc ON g.parent = gc.id
			WHERE ga.user_id is NULL
			AND '. $this->db->sql_in_set('g.parent', $parent_id) .'
			ORDER BY name ASC';

		return $this->get_sql_result($sql, $start, $end);
	}

	/**
	* Get the sql result
	*
	* @param string $sql The SQL-Query
	* @param int $parent_id Category to display games from; default = 0
	* @param int $start Start position in the table; default = 0
	* @param int $end End position at table; default = 0
	* @return array Array of game data entities
	* @access private
	*/
	private function get_sql_result($sql, $start = 0, $end = 0)
	{
		$games = array();

		if($end == 0)
		{
			$result = $this->db->sql_query($sql);
		}
		else
		{
			$result = $this->db->sql_query_limit($sql, $end, $start);
		}
		while ($row = $this->db->sql_fetchrow($result))
		{
			$games[] = $this->container->get('tacitus89.gamesmod.entity.game')
				->import($row);
		}

		$this->db->sql_freeresult($result);

		// Return all game entities
		return $games;
	}

	/**
	* Get the number of games
	*
	* @param int $parent_id Category to display games from
	* @return int Number of games
	* @access public
	*/
	public function get_number_games($parent_id)
	{
		$total_games = 0;

		$sql= 'SELECT COUNT(id) AS total_games
			FROM ' . $this->game_table . '
			WHERE ' . $this->db->sql_in_set('parent', $parent_id);
		$result = $this->db->sql_query($sql);
		$total_games = (int) $this->db->sql_fetchfield('total_games');
		$this->db->sql_freeresult($result);

		// Return all game entities
		return $total_games;
	}

	/**
	* Get the number of owned games
	*
	* @param int $user_id default = 0 = all user
	* @param int $parent_id Category to display games from; default = 0 = all Category
	* @return int Number of games
	* @access public
	*/
	public function get_number_owned_games($user_id = 0, $parent_id = 0)
	{
		$total_games = 0;

		if($user_id > 0 && $parent_id > 0)
		{
			$sql= 'SELECT COUNT(g.id) AS total_games
				FROM ' . $this->game_table . ' g
				JOIN ' . $this->games_awarded_table . ' ga ON ga.game_id = g.id
				WHERE '. $this->db->sql_in_set('ga.user_id', $user_id) .'
				AND ' . $this->db->sql_in_set('g.parent', $parent_id);

		}
		elseif($user_id > 0)
		{
			$sql= 'SELECT COUNT(ga.id) AS total_games
				FROM ' . $this->games_awarded_table . ' ga
				WHERE '. $this->db->sql_in_set('ga.user_id', $user_id);
		}
		elseif($parent_id > 0)
		{
			$sql= 'SELECT COUNT(ga.id) AS total_games
				FROM ' . $this->games_awarded_table . ' ga
				JOIN ' . $this->game_table . ' g ON ga.game_id = g.id
				WHERE ' . $this->db->sql_in_set('g.parent', $parent_id);
		}
		else
		{
			$sql= 'SELECT COUNT(ga.id) AS total_games
				FROM ' . $this->games_awarded_table . ' ga ';
		}
		$result = $this->db->sql_query($sql);
		$total_games = (int) $this->db->sql_fetchfield('total_games');
		$this->db->sql_freeresult($result);

		// Return all game entities
		return $total_games;
	}

	/**
	* Get the number of not owned games
	*
	* @param int $parent_id Category to display games from
	* @param int $user_id
	* @return int Number of games
	* @access public
	*/
	public function get_number_not_owned_games($user_id, $parent_id)
	{
		$total_games = 0;

		$sql= 'SELECT COUNT(g.id) AS total_games
			FROM ' . $this->game_table . ' g
			LEFT OUTER JOIN ' . $this->games_awarded_table . ' ga ON g.id = ga.game_id AND '. $this->db->sql_in_set('ga.user_id', $user_id) .'
			WHERE ga.user_id is NULL
			AND '. $this->db->sql_in_set('g.parent', $parent_id);
		$result = $this->db->sql_query($sql);
		$total_games = (int) $this->db->sql_fetchfield('total_games');
		$this->db->sql_freeresult($result);

		// Return all game entities
		return $total_games;
	}

	/**
	* Add a game
	*
	* @param object $entity game entity with new data to insert
	* @return game_interface Added game entity
	* @access public
	*/
	public function add_game($entity)
	{
		// Insert the game_cat data to the database
		$entity->insert();

		//Update the number in game_cat
		$sql = 'UPDATE ' . $this->game_cat_table . '
			SET number = number + 1
			WHERE ' . $this->db->sql_in_set('id', $entity->get_parent());
		$this->db->sql_query($sql);

		// Get the newly inserted game_cat's identifier
		$game_id = $entity->get_id();

		// Reload the data to return a fresh game entity
		return $entity->load($game_id);
	}

	/**
	* Delete a game and all awarded
	*
	* @param int $game_id The game_cat identifier to delete
	* @return null
	* @access public
	*/
	public function delete_game($game_id)
	{
		//must an integer
		$game_id = (int) $game_id;

		//get the game entity
		$entity = $this->container->get('tacitus89.gamesmod.entity.game')->load($game_id);

		//Update the number in game_cat
		$sql = 'UPDATE ' . $this->game_cat_table . '
			SET number = number - 1
			WHERE ' . $this->db->sql_in_set('id', $entity->get_parent());
		$this->db->sql_query($sql);

		//Delete all awarded games
		//Delete from db
		$sql = 'DELETE FROM ' . $this->games_awarded_table . '
				WHERE ' . $this->db->sql_in_set('game_id', $game_id);
		$this->db->sql_query($sql);

		//Delete from db
		$sql = 'DELETE FROM ' . $this->game_table . '
				WHERE ' . $this->db->sql_in_set('id', $game_id);
		$this->db->sql_query($sql);
	}

	/**
	* Add a list of games to owned_game
	*
	* @param int $user_id The user_id where adding the owned games
	* @param array(int) $entities Array of game_id to adding to games_awarded_table
	* @return count of adding games
	* @access public
	*/
	public function add_owned_game($user_id, $entities)
	{
		$added = 0;
		foreach($entities as $key => $value)
		{
			if (!$value)
			{
				trigger_error($user->lang['NO_GAME_ID'] . $return_page);
			}
			if (isset($value))
			{
				$added++;
				$sql_ary = array(
					'game_id'		=> $value,
					'user_id'		=> $user_id,
				);
				$sql = "INSERT INTO " . $this->games_awarded_table . " " . $this->db->sql_build_array('INSERT', $sql_ary);
			}
			$result = $this->db->sql_query($sql);
		}

		return $added;
	}

	/**
	* Delete a list of owned_game
	*
	* @param int $user_id The user_id where delete the owned games
	* @param array(int) $game_ids Array of game_ids to removing from games_awarded_table
	* @return count of removing games
	* @access public
	*/
	public function delete_owned_game($user_id, $game_ids)
	{

		//Delete from db
		//$sql = 'DELETE FROM ' . $this->games_awarded_table . '
		//		WHERE  '. $this->db->sql_in_set('game_ids', $game_ids) .'
		//		AND '. $this->db->sql_in_set('user_id', $this->user->data['user_id']);
		//$this->db->sql_query($sql);

		$removed = 0;
		foreach($game_ids as $key => $value)
		{
			if (!$value)
			{
				trigger_error($user->lang['NO_GAME_ID'] . $return_page);
			}
			if (isset($value))
			{
				$removed ++;

				$sql = "DELETE FROM " . $this->games_awarded_table . "
						WHERE ". $this->db->sql_in_set('game_id', $value) ."
						AND ". $this->db->sql_in_set('user_id', $user_id);
				$result = $this->db->sql_query($sql);
			}
		}
		return $removed;
	}

	/**
	* Get the gamer
	*
	* @param int $game_id Game ID
	* @return string A list with usernames
	* @access public
	*/
	public function get_gamers($game_id)
	{
		$sql = 'SELECT ga.user_id, u.user_colour, u.username
			FROM ' . $this->games_awarded_table . ' ga
			JOIN ' . $this->users_table . ' u ON ga.user_id = u.user_id
			WHERE '. $this->db->sql_in_set('ga.game_id', $game_id);

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$users_games[] = get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
		}
		$this->db->sql_freeresult($result);

		if(empty($users_games))
		{
			$gamer = '';
		}
		else
		{
			$gamer = implode(", ", $users_games);
		}


		// Return all game entities
		return $gamer;
	}

	/**
	* Get the count of gamer
	*
	* @param int $user_id User ID
	* @return int Count
	* @access public
	*/
	public function get_gamers_count($user_id)
	{
		$count = 0;

		$sql= 'SELECT COUNT(id) AS count
			FROM ' . $this->games_awarded_table . '
			WHERE '. $this->db->sql_in_set('user_id', $user_id);

		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('count');
		$this->db->sql_freeresult($result);

		// Return the count
		return $count;
	}

	/**
	* Get the popular games
	*
	* @param int Number of showing popular games
	* @return array Array of game data entities
	* @access public
	*/
	public function get_popular_games($number)
	{
		$games = array();

		$sql = 'SELECT '. \tacitus89\gamesmod\entity\game::get_sql_fields(array('this' => 'g', 'parent' => 'gc')) .'
			FROM ' . $this->games_awarded_table . ' ga
			JOIN ' . $this->game_table . ' g ON ga.game_id = g.id
			LEFT JOIN '. $this->game_cat_table .' gc ON g.parent = gc.id
			GROUP BY g.id';

		$result = $this->db->sql_query_limit($sql, $number);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$games[] = $this->container->get('tacitus89.gamesmod.entity.game')
				->import($row);
		}
		$this->db->sql_freeresult($result);

		// Return all game entities
		return $games;
	}

	/**
	* Get the recent games
	*
	* @param int Number of showing recent games
	* @return array Array of game data entities
	* @access public
	*/
	public function get_recent_games($number)
	{
		$games = array();

		$sql = 'SELECT '. \tacitus89\gamesmod\entity\game::get_sql_fields(array('this' => 'g', 'parent' => 'gc')) .'
			FROM ' . $this->game_table . ' g
			LEFT JOIN '. $this->game_cat_table .' gc ON g.parent = gc.id
			ORDER BY g.id DESC';

		$result = $this->db->sql_query_limit($sql, $number);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$games[] = $this->container->get('tacitus89.gamesmod.entity.game')
				->import($row);
		}
		$this->db->sql_freeresult($result);

		// Return all game entities
		return $games;
	}

	/**
	* Clear the seo_url in games table
	*
	* @return null
	* @access public
	*/
	public function clear_route()
	{
		$sql = 'UPDATE ' . $this->game_table . '
			SET route = ""';
		$this->db->sql_query($sql);
	}

	/**
	* Clear the seo_url in games table
	*
	* @return null
	* @access public
	*/
	public function create_route()
	{
		$sql = 'SELECT g.id, g.name, g.description, g.parent, g.image, g.route, gc.dir
			FROM ' . $this->game_table . ' g
			LEFT JOIN '. $this->game_cat_table .' gc ON g.parent = gc.id';

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$game = $this->container->get('tacitus89.gamesmod.entity.game')
				->import($row);
			try
			{
				//replace the special characters
				$string = preg_replace('/[!"#$%&*\'()+,.\/\\\\:;<=>?@\[\]^`{|}~ ]/', "_", strtolower($row['name']));
				//replace the repeat
				$string = preg_replace('/(_)\\1+/', "_", strtolower($string));
				$game->set_route($string);
				$game->save();
			}
			catch (\tacitus89\gamesmod\exception\base $e)
			{
			}

		}
		$this->db->sql_freeresult($result);
	}
}
