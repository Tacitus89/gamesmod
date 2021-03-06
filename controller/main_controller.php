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
* Main controller
*/
class main_controller
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $helper;

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

	protected $dir;

	/**
	* Constructor
	*
	* @param \phpbb\config\config                 $config          Config object
	* @param \phpbb\controller\helper			  $helper          Controller helper object
	* @param \phpbb\pagination					  $pagination	   Pagination object
	* @param \phpbb\request\request               $request         Request object
	* @param \phpbb\template\template             $template        Template object
	* @param \phpbb\user                          $user            User object
	* @param ContainerInterface                   $container       Service container interface
	* @param \tacitus89\games\operators\games     $games_operator
	* @param \tacitus89\games\operators\games_cat $games_cat_operator
	* @param string                               $root_path       phpBB root path
	* @param string                               $php_ext         phpEx
	* @return \tacitus89\gamesmod\controller\admin_controller
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\pagination $pagination, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, ContainerInterface $container, \tacitus89\gamesmod\operators\games $games_operator, \tacitus89\gamesmod\operators\games_cat $games_cat_operator, $root_path, $php_ext)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->pagination = $pagination;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->container = $container;
		$this->games_operator = $games_operator;
		$this->games_cat_operator = $games_cat_operator;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;

		if($this->config['enable_mod_rewrite'])
		{
			$this->dir = $this->root_path.'ext/tacitus89/gamesmod/images/';
		}
		else {
			$this->dir = $this->root_path.'../ext/tacitus89/gamesmod/images/';
		}
	}

	/**
	* Display the games
	*
	* @return null
	* @access public
	*/
	public function display($category = '', $game = '')
	{
		// When gamesmod are disabled, redirect users back to the forum index
		if(empty($this->config['games_active']))
		{
			redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
		}

		//Seo url is not acitve
		if(!$this->config['game_seo_url'] && ($category != '' || $game != ''))
		{
			//redirect to main page
			redirect($this->helper->route('tacitus89_gamesmod_main_controller'));
		}

		// Add gamesmod controller language file
		$this->user->add_lang_ext('tacitus89/gamesmod', 'gamesmod');

		// Requests
		$game_id = $this->request->variable('gid', 0);
		$parent_id = $this->request->variable('parent_id', 0);

		//Seo url is active
		if($this->config['game_seo_url'] && ($game_id != 0 || $parent_id != 0))
		{
			//redirect to main page
			redirect($this->helper->route('tacitus89_gamesmod_main_controller'));
		}

		$this->add_navlinks();

		//show the list of games
		if($parent_id != 0 || ($category != '' && $game == ''))
		{
			$start = $this->request->variable('start', 0);

			if($this->config['game_seo_url'])
			{
				//correct the path
				$this->dir = '../'.$this->dir;

				// Grab all the games
				$entities = $this->games_operator->get_games_by_name($category, $start, $this->config['games_pagination']);

				if(isset($entities[0]))
				{
					$parent = $entities[0]->get_parent();
				}
				else {
					$parent = $this->container->get('tacitus89.gamesmod.entity.games_cat')->load_by_name($category);
				}

				//number of games
				$total_games = $this->games_operator->get_number_games($parent->get_id());

				//Generation pagination
				$this->pagination->generate_template_pagination($this->helper->route('tacitus89_gamesmod_main_controller', array('category' => $parent->get_route())), 'pagination', 'start', $total_games, $this->config['games_pagination'], $start);
			}
			else {
				// Grab all the games
				$entities = $this->games_operator->get_games($parent_id, $start, $this->config['games_pagination']);

				if(isset($entities[0]))
				{
					$parent = $entities[0]->get_parent();
				}
				else {
					$parent = $this->container->get('tacitus89.gamesmod.entity.games_cat')->load($parent_id);
				}

				//number of games
				$total_games = $this->games_operator->get_number_games($parent->get_id());

				//Generation pagination
				$this->pagination->generate_template_pagination($this->helper->route('tacitus89_gamesmod_main_controller', array('parent_id' => $parent_id)), 'pagination', 'start', $total_games, $this->config['games_pagination'], $start);
			}

			// Process each game entity for display
			foreach ($entities as $entity)
			{
				$image = ($entity->get_parent()->get_dir() != '')? $this->dir.$entity->get_parent()->get_dir().'/'.$entity->get_image() : $this->dir.$entity->get_image();

				// Set output block vars for display in the template
				$this->template->assign_block_vars('games', array(
					'GAME_NAME'			=> $entity->get_name(),
					'GAME_IMAGE'		=> ($entity->get_image() != '')? $image : '',
					'GAME_DESCRIPTION'	=> $entity->get_meta_desc(),
					'GAME_GENRE'		=> $entity->get_genre(),
					'GAME_DEVELOPER'	=> $entity->get_developer(),
					'GAME_PUBLISHER'	=> $entity->get_publisher(),
					'GAME_RELEASE'		=> $entity->get_game_release(),
					'GAME_PLATFORM'		=> $entity->get_platform(),
					'GAME_ID'			=> $entity->get_id(),

					'U_GAME'			=> ($this->config['game_seo_url'])? $this->helper->route('tacitus89_gamesmod_main_controller', array('category' => $entity->get_parent()->get_route(), 'game' => $entity->get_route())):
																			$this->helper->route('tacitus89_gamesmod_main_controller', array('gid' => $entity->get_id())),
				));
			}

			//Add more navlinks
			$this->add_navlinks($parent);

			// Set output vars for display in the template
			$this->template->assign_vars(array(
				'S_GAMES'		=> true,
				'TOTAL_GAMES'	=> $total_games . ' ' . $this->user->lang('GAMES'),
				'U_PAGE_TITLE'	=> ($this->config['game_seo_url'])? $this->helper->route('tacitus89_gamesmod_main_controller', array('category' => $parent->get_route())):
																	$this->helper->route('tacitus89_gamesmod_main_controller', array('parent_id' => $parent_id)),

				'L_PAGE_TITLE'	=> $this->user->lang($parent->get_name()),
				'GAME_META_DESC'	=> $parent->get_meta_desc(),
				'GAME_META_KEYWORDS'=> $parent->get_meta_keywords(),
			));
		}
		//show a game
		elseif ($game_id != 0 || ($category != '' && $game != ''))
		{
			if($this->config['game_seo_url'])
			{
				//correct the path
				$this->dir = '../../'.$this->dir;

				//get the game
				$entity = $this->container->get('tacitus89.gamesmod.entity.game')->load_by_name($game);
			}
			else {
				//get the game
				$entity = $this->container->get('tacitus89.gamesmod.entity.game')->load($game_id);
			}

			//Add more navlinks
			$this->add_navlinks($entity->get_parent());
			$this->add_navlinks($entity->get_parent(), $entity);

			$image = ($entity->get_parent()->get_dir() != '')? $this->dir.$entity->get_parent()->get_dir().'/'.$entity->get_image() : $this->dir.$entity->get_image();

			// Set output block vars for display in the template
			$this->template->assign_vars(array(
				'GAME_NAME'			=> $entity->get_name(),
				'GAME_IMAGE'		=> ($entity->get_image() != '')? $image : '',
				'GAME_DESCRIPTION'	=> $entity->get_description_for_display(),
				'GAME_ID'			=> $entity->get_id(),
				'GAME_GENRE'		=> $entity->get_genre(),
				'GAME_DEVELOPER'	=> $entity->get_developer(),
				'GAME_PUBLISHER'	=> $entity->get_publisher(),
				'GAME_RELEASE'		=> $entity->get_game_release(),
				'GAME_PLATFORM'		=> $entity->get_platform(),
				'GAME_META_DESC'	=> $entity->get_meta_desc(),
				'GAME_META_KEYWORDS'=> $entity->get_meta_keywords(),
				'GAMERS'			=> $this->games_operator->get_gamers($entity->get_id()),
				'GAME_FORUM_URL'	=> ($entity->get_forum_url() != '')? append_sid($this->root_path.$entity->get_forum_url()) : '',
				'GAME_TOPIC_URL'	=> ($entity->get_topic_url() != '')?append_sid($this->root_path.$entity->get_topic_url()) : '',

				'S_GAME_VIEW'	=> true,
				'U_PAGE_TITLE'	=> ($this->config['game_seo_url'])? $this->helper->route('tacitus89_gamesmod_main_controller', array('category' => $entity->get_parent()->get_route(), 'game' => $entity->get_route())):
																	$this->helper->route('tacitus89_gamesmod_main_controller', array('gid' => $entity->get_id())),
				'L_PAGE_TITLE'	=> $this->user->lang($entity->get_name()),
			));
		}
		//show the list of cats
		else
		{
			// Grab all the games
			$entities = $this->games_cat_operator->get_games_cat();

			// Process each game entity for display
			foreach ($entities as $entity)
			{
				// Set output block vars for display in the template
				$this->template->assign_block_vars('games_cat', array(
					'GAME_NAME'		=> $entity->get_name(),
					'GAME_ID'		=> $entity->get_id(),
					'NUMBER'		=> $entity->get_number(),
					'GAME_DESC' 	=> $entity->get_meta_desc(),

					'U_GAME'		=> ($this->config['game_seo_url'])? $this->helper->route('tacitus89_gamesmod_main_controller', array('category' => $entity->get_route())):
																		$this->helper->route('tacitus89_gamesmod_main_controller', array('parent_id' => $entity->get_id())),
				));
			}

			// Set output vars for display in the template
			$this->template->assign_vars(array(
				'S_GAMES_CAT'	=> true,
				'U_PAGE_TITLE'	=> $this->helper->route('tacitus89_gamesmod_main_controller'),
				'L_PAGE_TITLE'	=> $this->user->lang('GAMES'),
			));
		}


		//Show popular games
		if($this->config['game_popular'] > 0 && ($game_id == 0 && $game == ''))
		{
			//Get popular games
			$entities = $this->games_operator->get_popular_games($this->config['game_popular']);

			// Process each popular game entity for display
			foreach ($entities as $entity)
			{
				$image = ($entity->get_parent()->get_dir() != '')? $this->dir.$entity->get_parent()->get_dir().'/'.$entity->get_image() : $this->dir.$entity->get_image();

				// Set output block vars for display in the template
				$this->template->assign_block_vars('popular_games', array(
					'GAME_NAME'		=> $entity->get_name(),
					'GAME_IMAGE'	=> ($entity->get_image() != '')? $image : '',

					'U_GAME'		=> ($this->config['game_seo_url'])?	$this->helper->route('tacitus89_gamesmod_main_controller', array('category' => $entity->get_parent()->get_route(), 'game' =>$entity->get_route())):
																		$this->helper->route('tacitus89_gamesmod_main_controller', array('gid' => $entity->get_id())),
				));
			}
		}

		//Add image size
		$this->games_operator->display_image_size($this->config,$this->template);

		//Show recent games
		if($this->config['game_recent'] > 0 && ($game_id == 0 && $game == ''))
		{
			//Get popular games
			$entities = $this->games_operator->get_recent_games($this->config['game_recent']);

			// Process each popular game entity for display
			foreach ($entities as $entity)
			{
				$image = ($entity->get_parent()->get_dir() != '')? $this->dir.$entity->get_parent()->get_dir().'/'.$entity->get_image() : $this->dir.$entity->get_image();

				// Set output block vars for display in the template
				$this->template->assign_block_vars('recent_games', array(
					'GAME_NAME'		=> $entity->get_name(),
					'GAME_IMAGE'	=> ($entity->get_image() != '')? $image : '',

					'U_GAME'		=> ($this->config['game_seo_url'])?	$this->helper->route('tacitus89_gamesmod_main_controller', array('category' => $entity->get_parent()->get_route(), 'game' =>$entity->get_route())):
																		$this->helper->route('tacitus89_gamesmod_main_controller', array('gid' => $entity->get_id())),
				));
			}
		}


		// Send all data to the template file
		return $this->helper->render('games.html', $this->user->lang('GAMESMOD'));
	}

	/**
	* Adding link at navlinks
	*
	* @param object parent; default = null
	* @param object game; default = null
	* @return null
	* @access public
	*/
	private function add_navlinks($parent = null, $game = null)
	{
		if(empty($parent))
		{
			$this->template->assign_block_vars('navlinks', array(
				'U_VIEW_FORUM'		=> $this->helper->route('tacitus89_gamesmod_main_controller'),
				'FORUM_NAME'		=> $this->user->lang('GAMES'),
			));
		}
		else
		{
			if(!empty($parent) && empty($game))
			{
				$this->template->assign_block_vars('navlinks', array(
					'U_VIEW_FORUM'		=> ($this->config['game_seo_url'])? $this->helper->route('tacitus89_gamesmod_main_controller', array('category' => $parent->get_route())):
																			$this->helper->route('tacitus89_gamesmod_main_controller', array('parent_id' => $parent->get_id())),
					'FORUM_NAME'		=> $this->user->lang($parent->get_name()),
				));
			}
			else {
				$this->template->assign_block_vars('navlinks', array(
					'U_VIEW_FORUM'		=> ($this->config['game_seo_url'])? $this->helper->route('tacitus89_gamesmod_main_controller', array('category' => $parent->get_route(), 'game' => $game->get_route())):
																			$this->helper->route('tacitus89_gamesmod_main_controller', array('gid' => $game->get_id())),
					'FORUM_NAME'		=> $this->user->lang($game->get_name()),
				));
			}
		}
	}

}
