<?php
/**
*
* @package GamesMod
* @copyright (c) 2015
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tacitus89\gamesmod\controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Admin controller
*/
class ucp_controller implements ucp_interface
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
	
	/** @var \tacitus89\gamesmod\operators\owned_games */
	protected $owned_games_operator;
	
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
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\pagination $pagination, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, ContainerInterface $container,  \tacitus89\gamesmod\operators\games $games_operator, \tacitus89\gamesmod\operators\games_cat $games_cat_operator, \tacitus89\gamesmod\operators\owned_games $owned_games_operator, $root_path, $php_ext)
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
		$this->owned_games_operator = $owned_games_operator;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}
	
	/**
	* Display the games
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
			$entities = $this->container->get('tacitus89.gamesmod.operator.not_owned_games')->get_games($parent_id, $start, $this->config['games_pagination']);
			
			//Display the games
			$this->show_games($entities, $parent_id);
			
			//number of games
			$total_games = $this->container->get('tacitus89.gamesmod.operator.not_owned_games')->get_number_games($parent_id);
			
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
			$entities = $this->owned_games_operator->get_games($parent_id, $start, $this->config['games_pagination']);
			
			//display the games
			$this->show_games($entities, $parent_id);
			
			//number of games
			$total_games = $this->owned_games_operator->get_number_games($parent_id);
			
			//Generation pagination
			$this->pagination->generate_template_pagination("{$this->u_action}&amp;parent_id={$parent_id}", 'pagination', 'start', $total_games, $this->config['games_pagination'], $start);
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'TOTAL_GAMES'		=> $total_games,
			'U_ACTION'			=> "{$this->u_action}&amp;parent_id={$parent_id}&amp;action=remove_game",
			'U_MAIN'			=> "{$this->u_action}&amp;parent_id=0",
			'S_IS_OWNED_GAMES'	=> true,
			'S_GAME_SHARE'		=> $this->config['game_share_allow'],
			'S_GAME_PLAY'		=> $this->config['game_play_allow'],
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
			'S_CATEGORY'	=> true,
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
		// Process each game entity for display
		foreach ($entities as $entity)
		{
			if(method_exists($entity, 'get_play'))
			{
				// Set output block vars for display in the template
				$this->template->assign_block_vars('games', array(
					'GAME_NAME'			=> $entity->get_name(),
					'GAME_IMAGE'		=> $entity->get_image(),
					'GAME_ID'			=> $entity->get_id(),
					'S_GAME_SHARE_ADD'	=> ($entity->get_share()) ? false : true,
					'U_SHARE'			=> ($entity->get_share()) ? "{$this->u_action}&amp;parent_id=". $parent_id ."&amp;action=share_delete&amp;game_id=". $entity->get_id() ."" : "{$this->u_action}&amp;parent_id=". $parent_id ."&amp;action=share_add&amp;game_id=". $entity->get_id() ."",
					'S_GAME_PLAY_ADD'	=> ($entity->get_play()) ? false : true,
					'U_PLAY'			=> ($entity->get_play()) ? "{$this->u_action}&amp;parent_id=". $parent_id ."&amp;action=play_delete&amp;game_id=". $entity->get_id() ."" : "{$this->u_action}&amp;parent_id=". $parent_id ."&amp;action=play_add&amp;game_id=". $entity->get_id() ."",

					'U_GAME'			=> "{$this->u_action}&amp;parent_id=" . $entity->get_id(),
				));
			}
			else
			{
				// Set output block vars for display in the template
				$this->template->assign_block_vars('games', array(
					'GAME_NAME'			=> $entity->get_name(),
					'GAME_IMAGE'		=> $entity->get_image(),
					'GAME_ID'			=> $entity->get_id(),

					'U_GAME'			=> "{$this->u_action}&amp;parent_id=" . $entity->get_id(),
				));
			}
		}
		
		// Set output block vars for display in the template
		//Get CAT Name
		$entity = $this->container->get('tacitus89.gamesmod.entity.games_cat')->load($parent_id);
		$this->template->assign_block_vars('breadcrumb', array(
			'GAME_CAT'		=> $entity->get_name(),

			'S_CURRENT_LEVEL'	=> ($entity->get_id() == $parent_id) ? true : false,

			'U_GAME'			=> "{$this->u_action}&amp;parent_id=" . $entity->get_id(),
		));
		
		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_CATEGORY'	=> false,
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
			
			$added = $this->owned_games_operator->add_game($game_ary);
			
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
			
			$removed = $this->owned_games_operator->delete_game($game_ary);
			
			$message = $removed . " " . $this->user->lang['GAME_REMOVE_GOOD'] . '<br /><br />' . sprintf($this->user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '&amp;parent_id= '. $parent_id .'">', '</a>');
			trigger_error($message);
		}
	}
	
	/**
	* Set a game as sharing
	*
	* @param integer $game_id  The game identifier
	* @param int $parent_id Category to display games from; default: 0
	* @return null
	* @access public
	*/
	public function share_add_game($game_id, $parent_id = 0)
	{
		//Grab the username
		$username = $this->request->variable('username', '');
		
		if($username != '')
		{
			// Create an array to collect errors that will be output to the user
			$errors = array();
			
			//Get the user_id from username
			user_get_id_name($user_id, $username);
			
			if(!sizeof($user_id))
			{
				$errors[] = $this->user->lang('NO_USER');
			}

			if($this->user->data['user_id'] == $user_id[0])
			{
				$errors[] = $this->user->lang('NO_SELF');
			}
			
			if(empty($errors))
			{
				$this->owned_games_operator->share_game($game_id, $user_id);
				$message = $this->user->lang['GAME_SHARE_USER_SUCCESS'] . $username[0] . '<br /><br />' . sprintf($this->user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '&amp;parent_id= '. $parent_id .'">', '</a>');
				trigger_error($message);
			}
		}
		
		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ERROR'			=> (sizeof($errors)) ? true : false,
			'ERROR_MSG'			=> (sizeof($errors)) ? implode('<br />', $errors) : '',
			'S_SHARE_ADD_USER'	=> true,
			'U_FIND_USERNAME'	=> append_sid("{$this->root_path}memberlist.{$this->php_ext}", 'mode=searchuser&form=ucp&field=username&select_single=true'),
			'U_POST_ACTION'		=> "{$this->u_action}&amp;parent_id=". $parent_id ."&amp;action=share_add&amp;game_id=". $game_id,
		));
	}
	
	/**
	* Set a game as not sharing
	*
	* @param integer $game_id  The game identifier
	* @param int $parent_id Category to display games from; default: 0
	* @return null
	* @access public
	*/
	public function share_delete_game($game_id)
	{
		$this->owned_games_operator->unshare_game($game_id);
	}
	
	/**
	* Set a game as playing
	*
	* @param integer $game_id  The game identifier
	* @param int $parent_id Category to display games from; default: 0
	* @return null
	* @access public
	*/
	public function play_add_game($game_id)
	{
		$this->owned_games_operator->play_game($game_id);
	}
	
	/**
	* Set a game as not playing
	*
	* @param integer $game_id  The game identifier
	* @param int $parent_id Category to display games from; default: 0
	* @return null
	* @access public
	*/
	public function play_delete_game($game_id)
	{
		$this->owned_games_operator->unplay_game($game_id);
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