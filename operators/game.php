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
* Operator for a set of games
*/
class game implements game_interface
{
	/** @var ContainerInterface */
	protected $container;

	/**
	* Nestedset for games
	*
	* @var \tacitus89\gamesmod\operators\nestedset_games
	*/
	protected $nestedset_games;

	/**
	* Constructor
	*
	* @param ContainerInterface $container Service container interface
	* @param \tacitus89\gamesmod\operators\nestedset_games $nestedset_games Nestedset object for tree functionality
	* @return \tacitus89\gamesmod\operators\game
	* @access public
	*/
	public function __construct(ContainerInterface $container, \tacitus89\gamesmod\operators\nestedset_games $nestedset_games)
	{
		$this->container = $container;
		$this->nestedset_games = $nestedset_games;
	}

	/**
	* Get the games
	*
	* @param int $parent_id Category to display games from; default: 0
	* @return array Array of game data entities
	* @access public
	*/
	public function get_games($parent_id = 0)
	{
		$entities = array();

		// Load all game data from the database into an array
		$rowset = $this->nestedset_games
			->get_games_data($parent_id);

		// Import each game into an entity, and store them in an array
		foreach ($rowset as $row)
		{
			$entities[] = $this->container->get('tacitus89.gamesmod.entity')
				->import($row);
		}

		// Return all game entities
		return $entities;
	}

	/**
	* Add a game
	*
	* @param object $entity game entity with new data to insert
	* @param int $parent_id Category to display games from; default: 0
	* @return game_interface Added game entity
	* @access public
	*/
	public function add_game($entity, $parent_id = 0)
	{
		// Insert the game data to the database for the given language selection
		$entity->insert();

		// Get the newly inserted game's identifier
		$game_id = $entity->get_id();

		// Update the tree for the game in the database
		$this->nestedset_games->add_to_nestedset($game_id);

		// If a parent id was supplied, update the game's parent id and tree ids
		if ($parent_id)
		{
			$this->nestedset_games->change_parent($game_id, $parent_id);
		}

		// Reload the data to return a fresh game entity
		return $entity->load($game_id);
	}

	/**
	* Delete a game
	*
	* @param int $game_id The game identifier to delete
	* @return null
	* @access public
	* @throws \tacitus89\gamesmod\exception\out_of_bounds
	*/
	public function delete_game($game_id)
	{
		$game_id = (int) $game_id;

		// Try to delete the game or category from the database
		try
		{
			$this->nestedset_games->delete($game_id);
		}
		catch (\OutOfBoundsException $e)
		{
			throw new \tacitus89\gamesmod\exception\out_of_bounds('game_id');
		}
	}

	/**
	* Move a game up/down
	*
	* @param int $game_id The game identifier to move
	* @param string $direction The direction (up|down)
	* @param int $amount The number of places to move the game
	* @return null
	* @access public
	* @throws \tacitus89\gamesmod\exception\out_of_bounds
	*/
	public function move($game_id, $direction = 'up', $amount = 1)
	{
		$game_id = (int) $game_id;
		$amount = (int) $amount;

		// Try to move the game or category up/down
		try
		{
			$this->nestedset_games->move($game_id, (($direction != 'up') ? -$amount : $amount));
		}
		catch (\OutOfBoundsException $e)
		{
			throw new \tacitus89\gamesmod\exception\out_of_bounds('game_id');
		}
	}

	/**
	* Change game parent
	*
	* @param int $game_id The current game identifier
	* @param int $new_parent_id The new game parent identifier
	* @return null
	* @access public
	* @throws \tacitus89\gamesmod\exception\out_of_bounds
	*/
	public function change_parent($game_id, $new_parent_id)
	{
		$game_id = (int) $game_id;
		$new_parent_id = (int) $new_parent_id;

		// Try to change game parent
		try
		{
			$this->nestedset_games->change_parent($game_id, $new_parent_id);
		}
		catch (\OutOfBoundsException $e)
		{
			$field = (strpos($e->getMessage(), 'INVALID_ITEM') !== false) ? 'game_id' : 'new_parent_id';

			throw new \tacitus89\gamesmod\exception\out_of_bounds($field);
		}
	}

	/**
	* Get a game's parent games (for use in breadcrumbs)
	*
	* @param int $parent_id Category to display games from
	* @return array Array of game data for a game's parent game
	* @access public
	*/
	public function get_game_parents($parent_id)
	{
		$entities = array();

		// Load all parent game data from the database into an array
		$rowset = $this->nestedset_games
			->get_path_data($parent_id);

		// Import each game into an entity, and store them in an array
		foreach ($rowset as $row)
		{
			$entities[] = $this->container->get('tacitus89.gamesmod.entity')
				->import($row);
		}
		return $entities;
	}
}
