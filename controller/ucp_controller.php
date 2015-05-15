<?php
/**
*
* @package Games Mod for phpBB3.1
* @copyright (c) 2015 Marco Candian (tacitus@strategie-zone.de)
* @copyright (c) 2009-2011 Martin Eddy (mods@mecom.com.au)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tacitus89\gamesmod\controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Admin controller
*/
class ucp_controller
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var ContainerInterface */
	protected $container;

	/** @var \tacitus89\gamesmod\operators\games */
	protected $games_operator;

	/** @var \tacitus89\gamesmod\operators\games_cat */
	protected $games_cat_operator;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/** @var string Custom form action */
	protected $u_action;

	/**
	* Constructor
	*
	* @param \phpbb\config\config                 $config          Config object
	* @param \phpbb\db\driver\driver_interface    $db              Database object
	* @param \phpbb\pagination					  $pagination	   Pagination object
	* @param \phpbb\request\request               $request         Request object
	* @param \phpbb\template\template             $template        Template object
	* @param \phpbb\user                          $user            User object
	* @param ContainerInterface                   $container       Service container interface
	* @param \tacitus89\games\operators\game      $game_operator   game operator object
	* @param string                               $root_path       phpBB root path
	* @param string                               $php_ext         phpEx
	* @return \tacitus89\gamesmod\controller\admin_controller
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\pagination $pagination, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, ContainerInterface $container,  \tacitus89\gamesmod\operators\games $games_operator, \tacitus89\gamesmod\operators\games_cat $games_cat_operator, $root_path, $php_ext)
	{
		$this->config = $config;
		$this->db = $db;
		$this->pagination = $pagination;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->container = $container;
		$this->games_operator = $games_operator;
		$this->games_cat_operator = $games_cat_operator;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* Display the games (not owned games)
	*
	* @param int $parent_id Category to display games from; default: 0
	* @return null
	* @access public
	*/
	public function display_games($parent_id = 0)
	{
		if($parent_id == 0)
		{
			//Display the Categories
			$this->show_cats();
		}
		else
		{
			// Add form key
			add_form_key('add_owned_game');

			$start = $this->request->variable('start', 0);

			// Grab all the games
			$entities = $this->games_operator->get_not_owned_games($this->user->data['user_id'], $parent_id, $start, $this->config['games_pagination']);

			//Display the games
			$this->show_games($entities, $parent_id);

			//number of games
			$total_games = $this->games_operator->get_number_not_owned_games($this->user->data['user_id'], $parent_id);

			//Generation pagination
			$this->pagination->generate_template_pagination("{$this->u_action}&amp;parent_id={$parent_id}", 'pagination', 'start', $total_games, $this->config['games_pagination'], $start);
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'TOTAL_GAMES'		=> $total_games,
			'U_ACTION'			=> "{$this->u_action}&amp;parent_id={$parent_id}&amp;action=add_game",
			'U_MAIN'			=> "{$this->u_action}&amp;parent_id=0",
			'S_IS_OWNED_GAMES'	=> false,
		));

	}

	/**
	* Display the owned_games
	*
	* @param int $parent_id Category to display games from; default: 0
	* @return null
	* @access public
	*/
	public function display_owned_games($parent_id = 0)
	{
		if($parent_id == 0)
		{
			//Display the Categories
			$this->show_cats();
		}
		else
		{
			// Add form key
			add_form_key('remove_owned_game');

			$start = $this->request->variable('start', 0);

			// Grab all the games
			$entities = $this->games_operator->get_owned_games($this->user->data['user_id'], $parent_id, $start, $this->config['games_pagination']);

			//display the games
			$this->show_games($entities, $parent_id);

			//number of games
			$total_games = $this->games_operator->get_number_owned_games($this->user->data['user_id'], $parent_id);

			//Generation pagination
			$this->pagination->generate_template_pagination("{$this->u_action}&amp;parent_id={$parent_id}", 'pagination', 'start', $total_games, $this->config['games_pagination'], $start);
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'TOTAL_GAMES'		=> $total_games,
			'U_ACTION'			=> "{$this->u_action}&amp;parent_id={$parent_id}&amp;action=remove_game",
			'U_MAIN'			=> "{$this->u_action}&amp;parent_id=0",
		));
	}

	/**
	* Display the game_cat
	*
	* @return null
	* @access private
	*/
	private function show_cats()
	{
		// Grab all the games_cat
		$entities = $this->games_cat_operator->get_games_cat();

		// Process each game entity for display
		foreach ($entities as $entity)
		{
			// Set output block vars for display in the template
			$this->template->assign_block_vars('games_cat', array(
				'GAME_NAME'		=> $entity->get_name(),
				'GAME_ID'		=> $entity->get_id(),

				'U_GAME'		=> "{$this->u_action}&amp;parent_id=" . $entity->get_id(),
			));
		}
		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'U_ACTION'		=> "{$this->u_action}&amp;parent_id={$parent_id}",
			'U_ADD_GAME'	=> "{$this->u_action}&amp;action=add",
			'U_MAIN'		=> "{$this->u_action}&amp;parent_id=0",
		));

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_GAME_CAT'		=> true,
		));
	}

	/**
	* Display the owned_games
	*
	* @param array of objects $entities Games to display
	* @return null
	* @access private
	*/
	private function show_games($entities, $parent_id)
	{
		//parent
		$parent = $this->container->get('tacitus89.gamesmod.entity.games_cat')->load($parent_id);
		$dir = ($parent->get_dir() != '') ? $parent->get_dir() . '/' : '';
		// Process each game entity for display
		foreach ($entities as $entity)
		{
			// Set output block vars for display in the template
			$this->template->assign_block_vars('games', array(
				'GAME_NAME'			=> $entity->get_name(),
				'GAME_IMAGE'		=> $dir.$entity->get_image(),
				'GAME_ID'			=> $entity->get_id(),

				'U_GAME'			=> "{$this->u_action}&amp;parent_id=" . $entity->get_id(),
			));
		}

		// Set output block vars for display in the template
		$this->template->assign_block_vars('breadcrumb', array(
			'GAME_CAT'			=> $parent->get_name(),

			'S_CURRENT_LEVEL'	=> ($parent->get_id() == $parent_id) ? true : false,

			'U_GAME'			=> "{$this->u_action}&amp;parent_id=" . $parent->get_id(),
		));

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_GAME'			=> true,
		));
	}

	/**
	* Adding a list of owned_games
	*
	* @param int $parent_id Category to display games from; default: 0
	* @return null
	* @access public
	*/
	public function add_user_game($parent_id)
	{
		// Initiate a game entity
		//$entity = $this->container->get('tacitus89.gamesmod.entity');

		// Build game parent pull down menu
		//$this->build_parent_select_menu($entity, $parent_id, $mode = 'add');

		$game_ary = $this->request->variable('selected', array(0));

		// Get form's POST actions (submit)
		$submit = $this->request->is_set_post('submit');
		if ($submit)
		{
			// Test if the form is valid
			if (!check_form_key('add_owned_game'))
			{
				$errors[] = $this->user->lang('FORM_INVALID');
			}

			$added = $this->games_operator->add_owned_game($this->user->data['user_id'], $game_ary);

			$message = $added . " " . $this->user->lang['GAME_ADD_GOOD'] . '<br /><br />' . sprintf($this->user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '&amp;parent_id= '. $parent_id .'">', '</a>');
			trigger_error($message);
		}
	}

	/**
	* Remove a list of owned_games
	*
	* @param int $parent_id Category to display games from; default: 0
	* @return null
	* @access public
	*/
	public function remove_user_game($parent_id)
	{
		// Initiate a game entity
		//$entity = $this->container->get('tacitus89.gamesmod.entity');

		// Build game parent pull down menu
		//$this->build_parent_select_menu($entity, $parent_id, $mode = 'add');

		$game_ary = $this->request->variable('selected', array(0));

		// Get form's POST actions (submit)
		$submit = $this->request->is_set_post('submit');
		if ($submit)
		{
			// Test if the form is valid
			if (!check_form_key('remove_owned_game'))
			{
				$errors[] = $this->user->lang('FORM_INVALID');
			}

			$removed = $this->games_operator->delete_owned_game($this->user->data['user_id'],$game_ary);

			$message = $removed . " " . $this->user->lang['GAME_REMOVE_GOOD'] . '<br /><br />' . sprintf($this->user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '&amp;parent_id= '. $parent_id .'">', '</a>');
			trigger_error($message);
		}
	}

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
