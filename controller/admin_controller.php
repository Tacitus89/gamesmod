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
class admin_controller
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

	protected $dir;

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
	* @param \tacitus89\games\operators\games_cat  $games_cat_operator   games_cat operator object
	* @param string                               $root_path       phpBB root path
	* @param string                               $php_ext         phpEx
	* @return \tacitus89\gamesmod\controller\admin_controller
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\pagination $pagination, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, ContainerInterface $container, \tacitus89\gamesmod\operators\games $games_operator, \tacitus89\gamesmod\operators\games_cat $games_cat_operator, $root_path, $php_ext)
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

		$this->dir = $this->root_path.'ext/tacitus89/gamesmod/images/';
	}

	/**
	* Display the options a user can configure for this extension
	*
	* @return null
	* @access public
	*/
	public function display_options()
	{
		// Create a form key for preventing CSRF attacks
		add_form_key('gamesmod_config');

		// Create an array to collect errors that will be output to the user
		$errors = array();

		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			// Test if the submitted form is valid
			if (!check_form_key('gamesmod_config'))
			{
				$errors[] = $this->user->lang('FORM_INVALID');
			}

			// If no errors, process the form data
			if (empty($errors))
			{
				// Set the options the user configured
				$this->set_options();

				// Add option settings change action to the admin log
				$phpbb_log = $this->container->get('log');
				$phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_GAMESMOD_SETTINGS_LOG');

				// Option settings have been updated
				// Confirm this to the user and provide link back to previous page
				trigger_error($this->user->lang('ACP_GAMES_CONF_SAVED') . adm_back_link($this->u_action));
			}
		}

		//Clear the seo url
		if($this->request->is_set_post('clear_seo_url'))
		{
			// Use a confirmation box routine when deleting a game
			if (confirm_box(true))
			{
				// Delete the game route on confirmation
				$this->games_operator->clear_route();
				$this->games_cat_operator->clear_route();

				// Add action to the admin log
				$phpbb_log = $this->container->get('log');
				$phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_GAMESMOD_CLEAR_SEO_URL_LOG');

				// Show user confirmation of the deleted game and provide link back to the previous page
				trigger_error($this->user->lang('ACP_CLEAR_SEO_URL_GOOD') . adm_back_link("{$this->u_action}"));
			}
			else
			{
				// Request confirmation from the user to delete the game
				confirm_box(false, $this->user->lang('ACP_CONFIRM_CLEAR_SEO_URL'), build_hidden_fields(array(
					'mode' 			=> 'config',
					'clear_seo_url' => 'clear_seo_url',
				)));

				// Use a redirect to take the user back to the previous page
				// if the user chose not delete the game from the confirmation page.
				redirect("{$this->u_action}");
			}
		}

		//Create the seo url
		if($this->request->is_set_post('create_seo_url'))
		{
			// Use a confirmation box routine when deleting a game
			if (confirm_box(true))
			{
				//Create the game route on confirmation
				$this->games_operator->create_route();
				$this->games_cat_operator->create_route();

				// Add action to the admin log
				$phpbb_log = $this->container->get('log');
				$phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_GAMESMOD_CREATE_SEO_URL_LOG');

				// Show user confirmation of the deleted game and provide link back to the previous page
				trigger_error($this->user->lang('ACP_CREATE_SEO_URL_GOOD') . adm_back_link("{$this->u_action}"));
			}
			else
			{
				// Request confirmation from the user to delete the game
				confirm_box(false, $this->user->lang('ACP_CONFIRM_CREATE_SEO_URL'), build_hidden_fields(array(
					'mode' 			=> 'config',
					'create_seo_url'=> 'create_seo_url',
				)));

				// Use a redirect to take the user back to the previous page
				// if the user chose not delete the game from the confirmation page.
				redirect("{$this->u_action}");
			}
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ERROR'		=> (sizeof($errors)) ? true : false,
			'ERROR_MSG'		=> (sizeof($errors)) ? implode('<br />', $errors) : '',

			'U_ACTION'		=> $this->u_action,

			'S_GAMES_ACTIVE'					=> $this->config['games_active'] ? true : false,
			'S_GAMES_DESCRIPTION'				=> $this->config['games_description'] ? true : false,
			'S_GAMES_PAGINATION'				=> $this->config['games_pagination'],
			'S_GAME_SMALL_IMG_WIDTH'			=> $this->config['game_small_img_width'],
			'S_GAME_SMALL_IMG_HT'				=> $this->config['game_small_img_ht'],
			//
			'S_GAME_RECENT'						=> $this->config['game_recent'],
			'S_GAME_POPULAR'					=> $this->config['game_popular'],
			'S_GAMES_SEO_URL'					=> $this->config['game_seo_url'] ? true : false,
			//
			'S_GAME_DISPLAY_PROFILE'			=> $this->config['game_display_profile'] ? true : false,
			'S_GAME_PROFILE_SEP'				=> $this->config['game_profile_sep'] ? true : false,
			//
			'S_GAME_RECENT_INDEX'				=> $this->config['game_recent_index'],
			'S_GAME_POPULAR_INDEX'				=> $this->config['game_popular_index'],
			'S_GAME_INDEX_EXT_STATS'			=> $this->config['game_index_ext_stats'] ? true : false,
			//
			'S_GAME_DISPLAY_TOPIC'				=> $this->config['game_display_topic'] ? true : false,
			'S_GAME_TOPIC_SEP'					=> $this->config['game_topic_sep'] ? true : false,
			'S_GAME_TOPIC_LIMIT'				=> $this->config['game_topic_limit'],
		));
	}

	/**
	* Set the options a user can configure
	*
	* @return null
	* @access protected
	*/
	protected function set_options()
	{
		$this->config->set('games_active', $this->request->variable('games_active', 0));
		$this->config->set('games_description', $this->request->variable('games_description', 0));
		$this->config->set('games_pagination', $this->request->variable('games_pagination', 0));
		$this->config->set('game_small_img_width', $this->request->variable('game_small_img_width', 0));
		$this->config->set('game_small_img_ht', $this->request->variable('game_small_img_ht', 0));
		//
		$this->config->set('game_recent', $this->request->variable('game_recent', 0));
		$this->config->set('game_popular', $this->request->variable('game_popular', 0));
		$this->config->set('game_seo_url', $this->request->variable('game_seo_url', 0));
		//
		$this->config->set('game_display_profile', $this->request->variable('game_display_profile', 0));
		$this->config->set('game_profile_sep', $this->request->variable('game_profile_sep', 0));
		//
		$this->config->set('game_recent_index', $this->request->variable('game_recent_index', 0));
		$this->config->set('game_popular_index', $this->request->variable('game_popular_index', 0));
		$this->config->set('game_index_ext_stats', $this->request->variable('game_index_ext_stats', 0));
		//
		$this->config->set('game_display_topic', $this->request->variable('game_display_topic', 0));
		$this->config->set('game_topic_sep', $this->request->variable('game_topic_sep', 0));
		$this->config->set('game_topic_limit', $this->request->variable('game_topic_limit', 0));
	}

	/**
	* Display the games_cat
	*
	* @return null
	* @access public
	*/
	public function display_games_cats()
	{
		// Grab all the games_cat
		$entities = $this->games_cat_operator->get_games_cat();

		// Process each game entity for display
		foreach ($entities as $entity)
		{
			// Set output block vars for display in the template
			$this->template->assign_block_vars('game_cats', array(
				'NAME'		=> $entity->get_name(),

				'S_IS_CATEGORY'		=> true,

				'U_DELETE'			=> "{$this->u_action}&amp;action=delete_cat&amp;parent_id=" . $entity->get_id(),
				'U_EDIT'			=> "{$this->u_action}&amp;action=edit_cat&amp;parent_id=" . $entity->get_id(),
				'U_MOVE_DOWN'		=> "{$this->u_action}&amp;action=move_cat_down&amp;parent_id=" . $entity->get_id() . '&amp;hash=' . generate_link_hash('down' . $entity->get_id()),
				'U_MOVE_UP'			=> "{$this->u_action}&amp;action=move_cat_up&amp;parent_id=" . $entity->get_id() . '&amp;hash=' . generate_link_hash('up' . $entity->get_id()),
				'U_GAME'			=> "{$this->u_action}&amp;action=view_games&amp;parent_id=" . $entity->get_id(),
			));

		}

		// Add form key
		add_form_key('add_edit_game_cat');

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'U_ACTION'		=> "{$this->u_action}",
			'U_ADD_CAT'		=> "{$this->u_action}&amp;action=add_cat",
			'U_MAIN'		=> "{$this->u_action}",
		));
	}

	/**
	* Display the games
	*
	* @param int $parent_id Category to display games from
	* @return null
	* @access public
	*/
	public function display_games($parent_id)
	{
		$start = $this->request->variable('start', 0);

		// Grab all the games
		$entities = $this->games_operator->get_games($parent_id, $start, $this->config['games_pagination']);

		if(isset($entities[0]))
		{
			$parent = $entities[0]->get_parent();
		}
		else {
			$parent = $this->container->get('tacitus89.gamesmod.entity.games_cat')->load($parent_id);
		}

		// Process each game entity for display
		foreach ($entities as $entity)
		{
			$image = ($entity->get_parent()->get_dir() != '')? $this->dir.$entity->get_parent()->get_dir().'/'.$entity->get_image() : $this->dir.$entity->get_image();

			// Set output block vars for display in the template
			$this->template->assign_block_vars('games', array(
				'GAME_NAME'		=> $entity->get_name(),

				'U_IMAGE'			=> ($entity->get_image() != '')? $image : '',
				'U_DELETE'			=> "{$this->u_action}&amp;action=delete_game&amp;game_id=" . $entity->get_id(),
				'U_EDIT'			=> "{$this->u_action}&amp;action=edit_game&amp;game_id=" . $entity->get_id(),
				'U_GAME'			=> "{$this->u_action}&amp;parent_id=" . $entity->get_id(),
			));
		}

		//number of games
		$total_games = $this->games_operator->get_number_games($parent_id);

		//Generation pagination
		$this->pagination->generate_template_pagination("{$this->u_action}&amp;action=view_games&amp;parent_id={$parent_id}", 'pagination', 'start', $total_games, $this->config['games_pagination'], $start);

		// Add form key
		add_form_key('add_edit_game');

		// Set output block vars for display in the template
		$this->template->assign_block_vars('breadcrumb', array(
			'GAME_CAT'		=> $parent->get_name(),

			'S_CURRENT_LEVEL'	=> ($parent->get_id() == $parent_id) ? true : false,

			'U_GAME'			=> "{$this->u_action}&amp;parent_id=" . $parent->get_id(),
		));

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'TOTAL_GAMES'	=> $total_games,
			'U_ACTION'		=> "{$this->u_action}&amp;parent_id={$parent_id}",
			'U_ADD_GAME'	=> "{$this->u_action}&amp;parent_id={$parent_id}&amp;action=add_game",
			'U_MAIN'		=> "{$this->u_action}&amp;parent_id=0",
		));
	}

	/**
	* Add a game_cat
	*
	* @return null
	* @access public
	*/
	public function add_game_cat()
	{
		// Add form key
		add_form_key('add_edit_game_cat');

		// Get form's POST actions (submit or preview)
		$submit = $this->request->is_set_post('submit');

		// Initiate a game entity
		$entity = $this->container->get('tacitus89.gamesmod.entity.games_cat');

		// Create an array to collect errors that will be output to the user
		$errors = array();
		try
		{
			$entity->set_name($this->request->variable('game_cat_name', '', true));
			$entity->set_dir($this->request->variable('game_cat_dir', '', true));
			$entity->set_route($this->request->variable('game_cat_route', '', true));
			$entity->set_meta_desc($this->request->variable('game_cat_meta_description', '', true));
			$entity->set_meta_keywords($this->request->variable('game_cat_meta_keywords', '', true));
		}
		catch (\tacitus89\gamesmod\exception\base $e)
		{
			// Catch exceptions and add them to errors array
			$errors[] = $e->get_message($this->user);
		}

		// If the form has been submitted
		if ($submit)
		{
			// Test if the form is valid
			if (!check_form_key('add_edit_game_cat'))
			{
				$errors[] = $this->user->lang('FORM_INVALID');
			}

			// Do not allow an empty game title
			if ($entity->get_name() == '')
			{
				$errors[] = $this->user->lang['ACP_CAT_ADD_FAIL'];
			}
		}

		// Insert or update game
		if ($submit && empty($errors))
		{
			// Add a new game entity to the database
			$this->games_cat_operator->add_games_cat($entity);

			// Add action to the admin log
			$phpbb_log = $this->container->get('log');
			$phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_GAMESMOD_GAME_CAT_ADD_LOG', time(), array($entity->get_name()));

			// Show user confirmation of the added game and provide link back to the previous page
			trigger_error($this->user->lang('ACP_CAT_ADD_GOOD') . adm_back_link("{$this->u_action}"));
		}


		$dir_options = '<option value=""></option>';
		if ($dh = opendir($this->dir))
		{
			foreach(glob($this->dir."/*",GLOB_ONLYDIR) as $file)
			{
				if ($entity->get_dir() == basename($file))
				{
					$dir_options .= '<option value="' . basename($file) . '" selected="selected">' . basename($file) . '</option>';
				}
				else
				{
					$dir_options .= '<option value="' . basename($file) . '">' . basename($file) . '</option>';
				}
			}
			closedir($dh);
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ERROR'			=> (sizeof($errors)) ? true : false,
			'ERROR_MSG'			=> (sizeof($errors)) ? implode('<br />', $errors) : '',
			'S_ADD_GAME_CAT'	=> true,
			'DIR_OPTIONS'		=> $dir_options,
			'GAME_CAT_NAME'		=> $entity->get_name(),
		));
	}

	/**
	* Add a game
	*
	* @param int $parent_id Category to display games from; default: 0
	* @return null
	* @access public
	*/
	public function add_game($parent_id = 0)
	{
		// Add form key
		add_form_key('add_edit_game');

		// Initiate a game entity
		$entity = $this->container->get('tacitus89.gamesmod.entity.game');

		// Build game parent pull down menu
		$this->build_parent_select_menu($entity, $parent_id, $mode = 'add');

		// Collect the form data
		$data = array(
			'parent'		=> $this->request->variable('game_parent', $parent_id),
			'name'			=> $this->request->variable('game_name', '', true),
			'description'	=> $this->request->variable('game_description', '', true),
			'image'			=> $this->request->variable('game_image', '', true),
			'route'			=> $this->request->variable('game_route', '', true),
			'genre'			=> $this->request->variable('game_genre', '', true),
			'developer'		=> $this->request->variable('game_developer', '', true),
			'publisher'		=> $this->request->variable('game_publisher', '', true),
			'game_release'	=> $this->request->variable('game_release', '', true),
			'platform'		=> $this->request->variable('game_platform', '', true),
			'forum_url'		=> $this->request->variable('game_forum_url', '', true),
			'topic_url'		=> $this->request->variable('game_topic_url', '', true),
			'meta_desc'		=> $this->request->variable('game_meta_desc', '', true),
			'meta_keywords'	=> $this->request->variable('game_meta_keywords', '', true),
		);

		// Process the new game
		$this->add_edit_game_data($entity, $data);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ADD_GAME'		=> true,
			'U_ACTION'			=> "{$this->u_action}&amp;action=add_game",
			'U_BACK'			=> "{$this->u_action}&amp;action=view_games&amp;parent_id={$parent_id}",
		));
	}

	/**
	* Edit a game_cat
	*
	* @param int $parent_id The game_cat identifier to edit
	* @return null
	* @access public
	*/
	public function edit_game_cat($parent_id)
	{
		// Add form key
		add_form_key('add_edit_game_cat');

		// Get form's POST actions submit
		$submit = $this->request->is_set_post('submit');

		// Initiate and load the game entity
		$entity = $this->container->get('tacitus89.gamesmod.entity.games_cat')->load($parent_id);

		// Collect the form data
		$data = array(
			'game_cat_name'	=> $this->request->variable('game_cat_name', $entity->get_name(), true),
			'game_cat_dir'	=> $this->request->variable('game_cat_dir', $entity->get_dir(), true),
			'game_cat_route'=> $this->request->variable('game_cat_route', $entity->get_route(), true),
			'game_cat_meta_desc'=> $this->request->variable('game_cat_meta_description', $entity->get_meta_desc(), true),
			'game_cat_meta_keywords'=> $this->request->variable('game_cat_meta_keywords', $entity->get_meta_keywords(), true),
		);

		// Create an array to collect errors that will be output to the user
		$errors = array();
		try
		{
			$entity->set_name($data['game_cat_name']);
			$entity->set_dir($data['game_cat_dir']);
			$entity->set_route($data['game_cat_route']);
			$entity->set_meta_desc($data['game_cat_meta_desc']);
			$entity->set_meta_keywords($data['game_cat_meta_keywords']);
		}
		catch (\tacitus89\gamesmod\exception\base $e)
		{
			// Catch exceptions and add them to errors array
			$errors[] = $e->get_message($this->user);
		}

		// If the form has been submitted
		if ($submit)
		{
			// Test if the form is valid
			if (!check_form_key('add_edit_game_cat'))
			{
				$errors[] = $this->user->lang('FORM_INVALID');
			}

			// Do not allow an empty game title
			if ($entity->get_name() == '')
			{
				$errors[] = $this->user->lang['ACP_CAT_ADD_FAIL'];
			}
		}

		// Insert or update game
		if ($submit && empty($errors))
		{
			// Save the edited game entity to the database
			$entity->save();

			// Add action to the admin log
			$phpbb_log = $this->container->get('log');
			$phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_GAMESMOD_GAME_CAT_EDIT_LOG', time(), array($entity->get_name()));

			// Show user confirmation of the saved game and provide link back to the previous page
			trigger_error($this->user->lang['ACP_CAT_EDIT_GOOD'] . adm_back_link("{$this->u_action}"));
		}

		$dir_options = '<option value=""></option>';
		if ($dh = opendir($this->dir))
		{
			foreach(glob($this->dir."/*",GLOB_ONLYDIR) as $file)
			{
				if ($entity->get_dir() == basename($file))
				{
					$dir_options .= '<option value="' . basename($file) . '" selected="selected">' . basename($file) . '</option>';
				}
				else
				{
					$dir_options .= '<option value="' . basename($file) . '">' . basename($file) . '</option>';
				}
			}
			closedir($dh);
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ERROR'			=> (sizeof($errors)) ? true : false,
			'ERROR_MSG'			=> (sizeof($errors)) ? implode('<br />', $errors) : '',
			'S_EDIT_GAME_CAT'	=> true,
			'DIR_OPTIONS'		=> $dir_options,
			'GAME_CAT_NAME'		=> $entity->get_name(),
			'GAME_CAT_ROUTE'			=> $entity->get_route(),
			'GAME_CAT_META_DESC'		=> $entity->get_meta_desc(),
			'GAME_CAT_META_KEYWORDS'	=> $entity->get_meta_keywords(),

			'U_ACTION'			=> "{$this->u_action}&amp;parent_id={$parent_id}&amp;action=edit_cat",
			'U_BACK'			=> "{$this->u_action}",
		));
	}

	/**
	* Edit a game
	*
	* @param int $game_id The game identifier to edit
	* @return null
	* @access public
	*/
	public function edit_game($game_id)
	{
		// Add form key
		add_form_key('add_edit_game');

		// Initiate and load the game entity
		$entity = $this->container->get('tacitus89.gamesmod.entity.game')->load($game_id);

		// Build game parent pull down menu
		$this->build_parent_select_menu($entity);

		// Collect the form data
		$data = array(
			'parent'		=> $this->request->variable('game_parent', $entity->get_parent()->get_id()),
			'name'			=> $this->request->variable('game_name', $entity->get_name(), true),
			'description'	=> $this->request->variable('game_description', $entity->get_description_for_edit(), true),
			'image'			=> $this->request->variable('game_image', $entity->get_image(), true),
			'route'			=> $this->request->variable('game_route', $entity->get_route(), true),
			'genre'			=> $this->request->variable('game_genre', $entity->get_genre(), true),
			'developer'		=> $this->request->variable('game_developer', $entity->get_developer(), true),
			'publisher'		=> $this->request->variable('game_publisher', $entity->get_publisher(), true),
			'game_release'	=> $this->request->variable('game_release', $entity->get_game_release(), true),
			'platform'		=> $this->request->variable('game_platform', $entity->get_platform(), true),
			'forum_url'		=> $this->request->variable('game_forum_url', $entity->get_forum_url(), true),
			'topic_url'		=> $this->request->variable('game_topic_url', $entity->get_topic_url(), true),
			'meta_desc'		=> $this->request->variable('game_meta_description', $entity->get_meta_desc(), true),
			'meta_keywords'	=> $this->request->variable('game_meta_keywords', $entity->get_meta_keywords(), true),
		);

		// Process the edited game
		$this->add_edit_game_data($entity, $data);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_EDIT_GAME'		=> true,

			'U_EDIT_ACTION'		=> "{$this->u_action}&amp;game_id={$game_id}&amp;action=edit_game",
			'U_BACK'			=> "{$this->u_action}&amp;action=view_games&amp;parent_id={$entity->get_parent()->get_id()}",
		));
	}

	/**
	* Process game data to be added or edited
	*
	* @param object $entity The game entity object
	* @param array $data The form data to be processed
	* @return null
	* @access protected
	*/
	protected function add_edit_game_data($entity, $data)
	{
		// Get form's POST actions (submit or preview)
		$submit = $this->request->is_set_post('submit');

		// Load posting language file for the BBCode editor
		$this->user->add_lang('posting');

		// Create an array to collect errors that will be output to the user
		$errors = array();

		// Grab the form's game data fields
		$game_fields = array(
			'name'			=> $data['name'],
			'description'	=> $data['description'],
			'image'			=> $data['image'],
			'parent'		=> $data['parent'],
			'route'			=> $data['route'],
			'genre'			=> $data['genre'],
			'developer'		=> $data['developer'],
			'publisher'		=> $data['publisher'],
			'game_release'	=> $data['game_release'],
			'platform'		=> $data['platform'],
			'forum_url'		=> $data['forum_url'],
			'topic_url'		=> $data['topic_url'],
			'meta_desc'		=> $data['meta_desc'],
			'meta_keywords'	=> $data['meta_keywords'],
		);

		// Grab the form data's message parsing options (possible values: 1 or 0)
		// If submit use the data from the form
		// If game edit use data stored in the entity
		// If game add use default values
		$description_parse_options = array(
			'bbcode'	=> ($submit) ? $this->request->variable('parse_bbcode', false) : (($entity->get_id()) ? $entity->description_bbcode_enabled() : 1),
			'magic_url'	=> ($submit) ? $this->request->variable('parse_magic_url', false) : (($entity->get_id()) ? $entity->description_magic_url_enabled() : 1),
			'smilies'	=> ($submit) ? $this->request->variable('parse_smilies', false) : (($entity->get_id()) ? $entity->description_smilies_enabled() : 1),

		);

		// Set the content parse options in the entity
		foreach ($description_parse_options as $function => $enabled)
		{
			call_user_func(array($entity, ($enabled ? 'description_enable_' : 'description_disable_') . $function));
		}

		// Purge temporary variable
		unset($content_parse_options);

		// Set the game's data in the entity
		foreach ($game_fields as $entity_function => $game_data)
		{
			try
			{
				// Calling the set_$entity_function on the entity and passing it $game_data
				call_user_func_array(array($entity, 'set_' . $entity_function), array($game_data));
			}
			catch (\tacitus89\gamesmod\exception\base $e)
			{
				// Catch exceptions and add them to errors array
				$errors[] = $e->get_message($this->user);
			}
		}

		unset($game_fields);

		// If the form has been submitted
		if ($submit)
		{
			// Test if the form is valid
			if (!check_form_key('add_edit_game'))
			{
				$errors[] = $this->user->lang('FORM_INVALID');
			}

			//Image Upload
			$uploadfile = $this->request->file('uploadfile');
			if($uploadfile['name'] != '')
			{
				if (!class_exists('\fileupload'))
				{
					include($this->root_path . 'includes/functions_upload.' . $this->php_ext);
				}
				$upload = new \fileupload('GAME_', array('jpg', 'jpeg', 'gif', 'png'), 80000, 0, 0, 0, 0, explode('|', $this->config['mime_triggers']));
				$file = $upload->form_upload('uploadfile');
				$file->clean_filename('real', '', '');
				if($entity->get_parent()->get_dir() != '')
				{
					$destination = 'ext/tacitus89/gamesmod/images/'.$entity->get_parent()->get_dir();
				}
				else {
					$destination = 'ext/tacitus89/gamesmod/images';
				}

				$data['image'] = $file->realname;

				// Move file and overwrite any existing image
				$file->move_file($destination, true);

				if (sizeof($file->error))
				{
					$file->remove();
					trigger_error(implode('<br />', $file->error));
				}
				else
				{
					chmod($this->root_path.$destination . '/' . $data['image'], 0644);
					$entity->set_image($data['image']);
				}
			}

			// Do not allow an empty game title
			if ($entity->get_name() == '')
			{
				$errors[] = $this->user->lang('ACP_GAME_ADD_FAIL');
			}
		}

		// Insert or update game
		if ($submit && empty($errors))
		{
			if ($entity->get_id())
			{
				// Save the edited game entity to the database
				$entity->save();

				// Add action to the admin log
				$phpbb_log = $this->container->get('log');
				$phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_GAMESMOD_GAME_EDIT_LOG', time(), array($entity->get_name()));

				// Show user confirmation of the saved game and provide link back to the previous page
				trigger_error($this->user->lang('ACP_GAME_EDIT_GOOD') . adm_back_link("{$this->u_action}&amp;action=view_games&amp;parent_id={$entity->get_parent()->get_id()}"));
			}
			else
			{
				// Add a new game entity to the database
				$this->games_operator->add_game($entity);

				// Add action to the admin log
				$phpbb_log = $this->container->get('log');
				$phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_GAMESMOD_GAME_NEW_LOG', time(), array($entity->get_name()));

				// Show user confirmation of the added game and provide link back to the previous page
				trigger_error($this->user->lang('ACP_GAME_ADD_GOOD') . adm_back_link("{$this->u_action}&amp;action=view_games&amp;parent_id={$data['parent']}"));
			}
		}

		//view existing images
		$dir = $this->dir.$entity->get_parent()->get_dir();
		$options = '<option value=""></option>';
		if ($dh = opendir($dir))
		{
			foreach(glob($dir."/*.{jpg,gif,jpeg,png}", GLOB_BRACE) as $file)
			{
				$options .= '<option value="' . basename($file) . '" ';
				$options .= (basename($file) == $entity->get_image())? 'selected="selected"':'';
				$options .= '>' . basename($file) . '</option>';
			}
			closedir($dh);
		}

		$dir = ($entity->get_parent()->get_dir() != '') ? $entity->get_parent()->get_dir() . '/' : '';
		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ERROR'			=> (sizeof($errors)) ? true : false,
			'ERROR_MSG'			=> (sizeof($errors)) ? implode('<br />', $errors) : '',

			'GAME_NAME'			=> $entity->get_name(),
			'GAME_ROUTE'		=> $entity->get_route(),
			'GAME_DESCRIPTION'	=> $entity->get_description_for_edit(),
			'GAME_GENRE'		=> $entity->get_genre(),
			'GAME_DEVELOPER'	=> $entity->get_developer(),
			'GAME_PUBLISHER'	=> $entity->get_publisher(),
			'GAME_RELEASE'		=> $entity->get_game_release(),
			'GAME_PLATFORM'		=> $entity->get_platform(),
			'GAME_FORUM_URL'	=> $entity->get_forum_url(),
			'GAME_TOPIC_URL'	=> $entity->get_topic_url(),
			'GAME_META_DESC'	=> $entity->get_meta_desc(),
			'GAME_META_KEYWORDS'=> $entity->get_meta_keywords(),
			'IMAGE_OPTIONS'		=> $options,
			'GAME_IMAGE'		=> ($entity->get_image() != '')? '' . $this->dir . $dir . $entity->get_image() . '' : '',

			'S_PARSE_BBCODE_CHECKED'	=> $entity->description_bbcode_enabled(),
			'S_PARSE_SMILIES_CHECKED'	=> $entity->description_smilies_enabled(),
			'S_PARSE_MAGIC_URL_CHECKED'	=> $entity->description_magic_url_enabled(),
			'BBCODE_STATUS'		=> $this->user->lang('BBCODE_IS_ON', '<a href="' . append_sid("{$this->root_path}faq.{$this->php_ext}", 'mode=bbcode') . '">', '</a>'),
			'SMILIES_STATUS'	=> $this->user->lang('SMILIES_ARE_ON'),
			'IMG_STATUS'		=> $this->user->lang('IMAGES_ARE_ON'),
			'FLASH_STATUS'		=> $this->user->lang('FLASH_IS_ON'),
			'URL_STATUS'		=> $this->user->lang('URL_IS_ON'),

			'S_BBCODE_ALLOWED'	=> true,
			'S_SMILIES_ALLOWED'	=> true,
			'S_BBCODE_IMG'		=> true,
			'S_BBCODE_FLASH'	=> true,
			'S_LINKS_ALLOWED'	=> true,
		));
	}

	/**
	* Delete a game_cat
	*
	* @param int $parent_id The game identifier to delete
	* @return null
	* @access public
	*/
	public function delete_game_cat($parent_id)
	{
		// Get form's POST actions ()
		$deleteall = $this->request->is_set_post('deleteall');
		$moveall = $this->request->is_set_post('moveall');
		$cancelcat = $this->request->is_set_post('cancelcat');

		// Initiate and load the game entity
		$entity = $this->container->get('tacitus89.gamesmod.entity.games_cat')->load($parent_id);

		//cancel
		if($cancelcat)
		{
			//redirect back to main menu
			redirect("{$this->u_action}");
		}

		if($deleteall)
		{
			// Delete the game on confirmation
			$this->games_cat_operator->delete_games_cat($parent_id);

			// Add action to the admin log
			$phpbb_log = $this->container->get('log');
			$phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_GAMESMOD_GAME_CAT_DELETE_ALL_LOG', time(), array($entity->get_name()));

			//redirect back to main menu
			redirect("{$this->u_action}");
		}

		if($moveall)
		{
			$new_cat = $this->request->variable('newcat', 0);

			// Delete the game on confirmation
			$this->games_cat_operator->delete_games_cat($parent_id, $new_cat);

			// Add action to the admin log
			$phpbb_log = $this->container->get('log');
			$phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_GAMESMOD_GAME_CAT_DELETE_MOVING_LOG', time(), array($entity->get_name()));

			//redirect back to main menu
			redirect("{$this->u_action}");
		}

		if(!$deleteall && !$moveall)
		{
			$entities = $this->games_cat_operator->get_games_cat();
			$choice = '';
			foreach ($entities as $entity)
			{
				if($entity->get_id() != $parent_id)
				{
					$choice .= '<option value="' . $entity->get_id() . '">' . $entity->get_name() . '</option>';
				}
			}
			if (!empty($choice))
			{
				trigger_error(sprintf($this->user->lang['ACP_CAT_DELETE_CONFIRM'], $choice));
			}
			else
			{
				trigger_error($this->user->lang['ACP_CAT_DELETE_CONFIRM_ELSE']);
			}

			// Set output vars for display in the template
			$this->template->assign_vars(array(
				'S_DELETE_GAME_CAT'		=> true,
				'U_DELETE_CAT'			=> "{$this->u_action}&amp;action=delete_cat",
				'U_MAIN'				=> "{$this->u_action}",
			));
		}
	}

	/**
	* Delete a game
	*
	* @param int $game_id The game identifier to delete
	* @return null
	* @access public
	*/
	public function delete_game($game_id)
	{
		// Initiate and load the game entity
		$entity = $this->container->get('tacitus89.gamesmod.entity.game')->load($game_id);

		// Use a confirmation box routine when deleting a game
		if (confirm_box(true))
		{
			// Delete the game on confirmation
			$this->games_operator->delete_game($game_id);

			// Add action to the admin log
			$phpbb_log = $this->container->get('log');
			$phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_GAMESMOD_GAME_DELETE_LOG', time(), array($entity->get_name()));

			// Show user confirmation of the deleted game and provide link back to the previous page
			trigger_error($this->user->lang('ACP_GAME_DELETE_GOOD') . adm_back_link("{$this->u_action}&amp;action=view_games&amp;parent_id={$entity->get_parent()->get_id()}"));
		}
		else
		{
			// Request confirmation from the user to delete the game
			confirm_box(false, $this->user->lang('ACP_CONFIRM_MSG_1'), build_hidden_fields(array(
				'mode' => 'management',
				'action' => 'delete_game',
				'game_id' => $game_id,
			)));

			// Use a redirect to take the user back to the previous page
			// if the user chose not delete the game from the confirmation page.
			redirect("{$this->u_action}&amp;action=view_games&amp;parent_id={$entity->get_parent()->get_id()}");
		}
	}

	/**
	* Move a game_cat up/down
	*
	* @param int $parent_id The game identifier to move
	* @param string $direction The direction (up|down)
	* @return null
	* @access public
	*/
	public function move_game_cat($parent_id, $direction)
	{
		// If the link hash is invalid, stop and show an error message to the user
		if (!check_link_hash($this->request->variable('hash', ''), $direction . $parent_id))
		{
			trigger_error($this->user->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Move the game
		$this->games_cat_operator->move($parent_id, $direction);

		// Send a JSON response if an AJAX request was used
		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$json_response->send(array('success' => true));
		}

		// Initiate and load the game entity for no AJAX request
		$entity = $this->container->get('tacitus89.gamesmod.entity.games_cat')->load($parent_id);

		// Use a redirect to reload the current page
		redirect("{$this->u_action}");
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

	/**
	* Build pull down menu options of available game parents
	*
	* @param object $entity The game entity object
	* @param int $parent_id Category to display games from; default: 0
	* @param string $mode Display menu for add or edit mode
	* @return null
	* @access protected
	*/
	protected function build_parent_select_menu($entity, $parent_id = 0, $mode = 'edit')
	{
		$parent_id = ($mode == 'edit') ? $entity->get_parent()->get_id() : $parent_id;

		// Prepare game pull-down field
		$game_menu_items = $this->games_cat_operator->get_games_cat();

		// Process each game menu item for pull-down
		foreach ($game_menu_items as $game_menu_item)
		{
			// Set output block vars for display in the template
			$this->template->assign_block_vars('gamemenu', array(
				'GAME_ID'			=> $game_menu_item->get_id(),
				'GAME_TITLE'		=> $game_menu_item->get_name(),

				'S_GAME_PARENT'		=> ($game_menu_item->get_id() == $parent_id) ? true : false,
			));
		}
	}
}
