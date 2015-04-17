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
* Operator for a set of games_cat
*/
class games implements games_interface
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
	* Constructor
	*
	* @param ContainerInterface $container		Service container interface
	* @param phpbb\db\driver\driver_interface 	$db
	* @param string							 	$game_table The database table the game_cat are stored in
	* @return \tacitus89\gamesmod\operators\game
	* @access public
	*/
	public function __construct(ContainerInterface $container, \phpbb\db\driver\driver_interface $db, $game_table)
	{
		$this->container = $container;
		$this->db = $db;
		$this->game_table = $game_table;
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
		
		$sql = "SELECT *
			FROM " . $this->game_table . '
			WHERE ' . $this->db->sql_in_set('parent', $parent_id) .'
			ORDER BY name ASC';
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
		
		$sql= 'SELECT COUNT(id) AS total_games
			FROM ' . $this->game_table . '
			WHERE ' . $this->db->sql_in_set('parent', $parent_id) .'
			ORDER BY name ASC';
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

		// Get the newly inserted game_cat's identifier
		$game_id = $entity->get_id();

		// Reload the data to return a fresh game entity
		return $entity->load($game_id);
	}

	/**
	* Delete a game_cat
	*
	* @param int $game_id The game_cat identifier to delete
	* @return null
	* @access public
	*/
	public function delete_game($game_id)
	{
		//must an integer
		$game_id = (int) $game_id;
		
		//Delete from db
		$sql = 'DELETE FROM ' . $this->game_table . '
				WHERE ' . $this->db->sql_in_set('id', $game_id);
		$this->db->sql_query($sql);
	}
}
