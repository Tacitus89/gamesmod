<?php
/**
*
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace tacitus89\gamesmod\operators;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Operator for a set of owned_games
*/
class owned_games implements games_interface
{
	/** @var ContainerInterface */
	protected $container;
	
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	
	/** @var \phpbb\user */
	protected $user;
	
	/**
	* The database table the game are stored in
	*
	* @var string
	*/
	protected $game_table;
	
	/**
	* The database table the games_awarded are stored in
	*
	* @var string
	*/
	protected $games_awarded_table;

	/**
	* Constructor
	*
	* @param ContainerInterface $container		Service container interface
	* @param phpbb\db\driver\driver_interface 	$db
	* @param \phpbb\user                        $user            User object
	* @param string							 	$game_table The database table the games are stored in
	* @param string							 	$games_awarded_table The database table the games_awarded_table are stored in
	* @return \tacitus89\gamesmod\operators\game
	* @access public
	*/
	public function __construct(ContainerInterface $container, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, $game_table, $games_awarded_table)
	{
		$this->container = $container;
		$this->db = $db;
		$this->user = $user;
		$this->game_table = $game_table;
		$this->games_awarded_table = $games_awarded_table;
	}

	/**
	* Get the games
	*
	* @param int $parent_id Category to display games from
	* @param int $start Start position in the table; default = 0
	* @param int $end End position at table; default = 15
	* @return array Array of game data entities
	* @access public
	*/
	public function get_games($parent_id, $start = 0, $end = 15)
	{
		$games = array();
		
		$sql= 'SELECT g.id, g.name, g.description, g.parent, g.image, ga.play, ga.share, ga.share_id
			FROM ' . $this->game_table . ' g
			JOIN ' . $this->games_awarded_table . ' ga ON g.id = ga.game_id 
			WHERE '. $this->db->sql_in_set('user_id', $this->user->data['user_id']) .'
			AND '. $this->db->sql_in_set('parent', $parent_id) .'
			ORDER BY name ASC';
		$result = $this->db->sql_query_limit($sql, $end, $start);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$games[] = $this->container->get('tacitus89.gamesmod.entity.owned_game')
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
		$games = array();
		
		$sql= 'SELECT COUNT(g.id) AS total_games
			FROM ' . $this->game_table . ' g
			JOIN ' . $this->games_awarded_table . ' ga ON g.id = ga.game_id 
			WHERE '. $this->db->sql_in_set('user_id', $this->user->data['user_id']) .'
			AND '. $this->db->sql_in_set('parent', $parent_id) .'
			ORDER BY name ASC';
		$result = $this->db->sql_query($sql);
		$total_games = (int) $this->db->sql_fetchfield('total_games');
		$this->db->sql_freeresult($result);

		// Return all game entities
		return $total_games;
	}

	/**
	* Add a list of games to owned_game
	*
	* @param array(int) $entities Array of game_id to adding to games_awarded_table
	* @return count of adding games
	* @access public
	*/
	public function add_game($entities)
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
					'user_id'		=> $this->user->data['user_id'],
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
	* @param array(int) $game_id Array of game_id to removing from games_awarded_table
	* @return count of removing games
	* @access public
	*/
	public function delete_game($game_id)
	{
		
		//Delete from db
		$sql = 'DELETE FROM ' . $this->games_awarded_table . '
				WHERE  '. $this->db->sql_in_set('game_id', $game_id) .'
				AND '. $this->db->sql_in_set('user_id', $this->user->data['user_id']);
		$this->db->sql_query($sql);
		
		$removed = 0;
		foreach($game_id as $key => $value)
		{
			if (!$value)
			{
				trigger_error($user->lang['NO_GAME_ID'] . $return_page);
			}
			if (isset($value))
			{
				$removed ++;
				
				$sql = "DELETE FROM " . $this->games_awarded_table . "
						WHERE ". $this->db->sql_in_set('game_id', $$value) ."
						AND ". $this->db->sql_in_set('user_id', $this->user->data['user_id']);
				$result = $this->db->sql_query($sql);
			}
		}
		return $removed;
	}
	
	/**
	* Set a game as playing
	*
	* @param int $game_id The game identifier
	* @return null
	* @access public
	*/
	public function play_game($game_id)
	{
		$sql = 'UPDATE ' . $this->games_awarded_table . '
			SET play = 1
			WHERE '. $this->db->sql_in_set('game_id', $game_id) .'
			AND '.  $this->db->sql_in_set('user_id', $this->user->data['user_id']);
		$this->db->sql_query($sql);
	}
	
	/**
	* Set a game as not playing
	*
	* @param int $game_id The game identifier
	* @return null
	* @access public
	*/
	public function unplay_game($game_id)
	{
		$sql = 'UPDATE ' . $this->games_awarded_table . '
			SET play = 0
			WHERE '. $this->db->sql_in_set('game_id', $game_id) .'
			AND '.  $this->db->sql_in_set('user_id', $this->user->data['user_id']);
		$this->db->sql_query($sql);
	}
	
	/**
	* Set a game as sharing
	*
	* @param int $game_id The game identifier
	* @return null
	* @access public
	*/
	public function share_game($game_id, $share_user_id)
	{
		$sql = 'UPDATE ' . $this->games_awarded_table . '
			SET share = 1, '. $this->db->sql_in_set('share_id', $share_user_id) .'
			WHERE '. $this->db->sql_in_set('game_id', $game_id) .'
			AND '.  $this->db->sql_in_set('user_id', $this->user->data['user_id']);
		$this->db->sql_query($sql);
	}
	
	/**
	* Set a game as not sharing
	*
	* @param int $game_id The game identifier
	* @return null
	* @access public
	*/
	public function unshare_game($game_id)
	{
		$sql = 'UPDATE ' . $this->games_awarded_table . '
			SET share = 0, share_id = 0
			WHERE '. $this->db->sql_in_set('game_id', $game_id) .'
			AND '.  $this->db->sql_in_set('user_id', $this->user->data['user_id']);
		$this->db->sql_query($sql);
	}
}
