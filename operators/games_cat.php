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
class games_cat
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
	protected $game_cat_table;

	/**
	* The database table the game are stored in
	*
	* @var string
	*/
	protected $game_table;

	/**
	* Constructor
	*
	* @param ContainerInterface $container		Service container interface
	* @param phpbb\db\driver\driver_interface 	$db
	* @param string							 	$game_cat_table The database table the game_cat are stored in
	* @return \tacitus89\gamesmod\operators\game
	* @access public
	*/
	public function __construct(ContainerInterface $container, \phpbb\db\driver\driver_interface $db, $game_table, $game_cat_table)
	{
		$this->container = $container;
		$this->db = $db;
		$this->game_cat_table = $game_cat_table;
		$this->game_table = $game_table;
	}

	/**
	* Get the games_cat
	*
	* @return array Array of game data entities
	* @access public
	*/
	public function get_games_cat()
	{
		$game_cat = array();

		$sql = "SELECT *
			FROM " . $this->game_cat_table . "
			ORDER BY order_id ASC";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$game_cat[] = $this->container->get('tacitus89.gamesmod.entity.games_cat')
				->import($row);
		}
		$this->db->sql_freeresult($result);

		// Return all game entities
		return $game_cat;
	}

	/**
	* Get one games_cat
	*
	* @return array Array of game data entities
	* @access public
	*/
	public function get($id)
	{
		return $this->container->get('tacitus89.gamesmod.entity.games_cat')->load($id);
	}

	/**
	* Add a game
	*
	* @param object $entity game entity with new data to insert
	* @param int $parent_id Category to display games from; default: 0
	* @return game_interface Added game entity
	* @access public
	*/
	public function add_games_cat($entity)
	{
		$sql = "SELECT COUNT(order_id) AS counter
				FROM " . $this->game_cat_table;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$number = $row['counter'];
		$this->db->sql_freeresult($result);

		if($number != 0 && $entity->get_order_id() < $number)
		{
			$entity->set_order_id($number);
		}

		// Insert the game_cat data to the database
		$entity->insert();

		// Get the newly inserted game_cat's identifier
		$games_cat_id = $entity->get_id();

		// Reload the data to return a fresh game entity
		return $entity->load($games_cat_id);
	}

	/**
	* Delete a game_cat
	*
	* @param int $games_cat_id The game_cat identifier to delete
	* @param int $new_cat The new cat_id to move the game or 0 to delete the game; default: 0
	* @return null
	* @access public
	*/
	public function delete_games_cat($games_cat_id, $new_cat = 0)
	{
		//must an integer
		$games_cat_id = (int) $games_cat_id;
		$new_cat = (int) $new_cat;

		$sql = "SELECT order_id
				FROM " . $this->game_cat_table . "
				WHERE " . $this->db->sql_in_set('id', $games_cat_id);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$order_id = $row['order_id'];
		$this->db->sql_freeresult($result);

		if($new_cat > 0)
		{
			//updating the game in this cat
			$sql = 'UPDATE ' . $this->game_table . '
			SET parent = '. $new_cat .'
			WHERE parent = ' . $games_cat_id;
			$this->db->sql_query($sql);
		}
		else
		{
			//deleting the game in this cat
			$sql = 'DELETE FROM ' . $this->game_table . '
			WHERE parent = ' . $games_cat_id;
			$this->db->sql_query($sql);
		}

		//Delete from db
		$sql = 'DELETE FROM ' . $this->game_cat_table . '
				WHERE ' . $this->db->sql_in_set('id', $games_cat_id);
		$this->db->sql_query($sql);

		//updating cats for new positions
		$sql = 'UPDATE ' . $this->game_cat_table . '
		SET order_id = order_id - 1
		WHERE order_id > ' . $order_id;
		$this->db->sql_query($sql);
	}

	/**
	* Move a game_cat up/down
	*
	* @param int $games_cat_id The game identifier to move
	* @param string $direction The direction (up|down)
	* @return null
	* @access public
	*/
	public function move($games_cat_id, $direction = 'up')
	{
		$games_cat_id = (int) $games_cat_id;

		$sql = "SELECT order_id
				FROM " . $this->game_cat_table . "
				WHERE " . $this->db->sql_in_set('id', $games_cat_id);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$old_position = $row['order_id'];
		$this->db->sql_freeresult($result);

		$sql = "SELECT COUNT(order_id) AS counter
				FROM " . $this->game_cat_table;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$number = $row['counter'];
		$this->db->sql_freeresult($result);

		$new_position = $old_position;
		//Up and not first
		if($direction == 'up' && $old_position > 0)
		{
			$new_position = $old_position - 1;
		}
		//down and not lastest
		elseif ($direction == 'down' && $old_position < $number)
		{
			$new_position = $old_position + 1;
		}

		//Update the cat at the new position
		$sql = 'UPDATE ' . $this->game_cat_table . '
		SET order_id = ' . $old_position . '
		WHERE ' . $this->db->sql_in_set('order_id', $new_position);
		$this->db->sql_query($sql);

		//Update the moving cat to new position
		$sql = 'UPDATE ' . $this->game_cat_table . '
		SET order_id = ' . $new_position . '
		WHERE ' . $this->db->sql_in_set('id', $games_cat_id);
		$this->db->sql_query($sql);
	}
}
