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
class not_owned_games implements games_interface
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

		$sql = "SELECT g.id, g.name, g.description, g.parent, g.image
			FROM " . $this->game_table . " g
			LEFT OUTER JOIN " . $this->games_awarded_table . " ga ON g.id = ga.game_id AND ga.user_id = ". $this->user->data['user_id'] ."
			WHERE ga.user_id is NULL
			AND g.parent = $parent_id
			ORDER BY name";
		$result = $this->db->sql_query_limit($sql, $end, $start);
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
		$games = array();
		
		$sql= "SELECT COUNT(g.id) AS total_games
			FROM " . $this->game_table . " g
			LEFT OUTER JOIN " . $this->games_awarded_table . " ga ON g.id = ga.game_id AND ga.user_id = ". $this->user->data['user_id'] ."
			WHERE ga.user_id is NULL
			AND g.parent = $parent_id
			ORDER BY name";
		$result = $this->db->sql_query($sql);
		$total_games = (int) $this->db->sql_fetchfield('total_games');
		$this->db->sql_freeresult($result);

		// Return all game entities
		return $total_games;
	}

	/**
	* Do nothing
	*
	* @return game_interface same game entity
	* @access public
	*/
	public function add_game($entity)
	{
		return $entity;
	}

	/**
	* Do nothing
	*
	* @param int $game_id The game_cat identifier to delete
	* @return null
	* @access public
	*/
	public function delete_game($game_id)
	{
	}
}
