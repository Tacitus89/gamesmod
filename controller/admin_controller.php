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
class admin_controller implements admin_interface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var ContainerInterface */
	protected $container;
	
	/** @var \tacitus89\gamesmod\operators\game */
	protected $game_operator;

	/** @var \phpbb\includes\functions_upload */
	protected $upload;
	
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
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, ContainerInterface $container, \tacitus89\gamesmod\operators\game $game_operator, $root_path, $php_ext)
	{
		$this->config = $config;
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->container = $container;
		$this->game_operator = $game_operator;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
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
				
				//TODO: Adding
				// Add option settings change action to the admin log
				//$phpbb_log = $this->container->get('log');
				//$phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_BOARDRULES_SETTINGS_LOG');

				// Option settings have been updated and logged
				// Confirm this to the user and provide link back to previous page
				trigger_error($this->user->lang('ACP_GAMES_CONF_SAVED') . adm_back_link($this->u_action));
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
			'S_GAME_PLAY_ALLOW'					=> $this->config['game_play_allow'] ? true : false,
			'S_GAME_SHARE_ALLOW'				=> $this->config['game_share_allow'] ? true : false,
			'S_GAME_RECENT'						=> $this->config['game_recent'],
			'S_GAME_POPULAR'					=> $this->config['game_popular'],
			'S_GAME_PLAY'						=> $this->config['game_play'],
			'S_GAME_RECENT_INDEX'				=> $this->config['game_recent_index'],
			'S_GAME_POPULAR_INDEX'				=> $this->config['game_popular_index'],
			'S_GAME_PLAY_INDEX'					=> $this->config['game_play_index'],
			'S_GAME_INDEX_EXT_STATS'			=> $this->config['game_index_ext_stats'] ? true : false,
			'S_GAME_DISPLAY_TOPIC'				=> $this->config['game_display_topic'] ? true : false,
			'S_GAME_TOPIC_SEP'					=> $this->config['game_topic_sep'] ? true : false,
			'S_GAME_TOPIC_LIMIT'				=> $this->config['game_topic_limit'],
			//
			'S_GAME_RECENT_PORTAL'				=> $this->config['game_recent_portal'],
			'S_GAME_POPULAR_PORTAL'				=> $this->config['game_popular_portal'],
			'S_GAME_PLAY_PORTAL'				=> $this->config['game_play_portal'],
			'S_GAME_PORTAL_THUMB_WIDTH'			=> $this->config['game_portal_thumb_width'],
			'S_GAME_PORTAL_THUMB_HEIGHT'		=> $this->config['game_portal_thumb_height'],
			'S_GAME_RECENT_SIDE_PORTAL'			=> $this->config['game_recent_side_portal'],
			'S_GAME_POPULAR_SIDE_PORTAL'		=> $this->config['game_popular_side_portal'],
			'S_GAME_PLAY_SIDE_PORTAL'			=> $this->config['game_play_side_portal'],
			'S_GAME_STATS_PORTAL'				=> $this->config['game_stats_portal'] ? true : false,
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
		$this->config->set('game_play_allow', $this->request->variable('game_play_allow', 0));
		$this->config->set('game_share_allow', $this->request->variable('game_share_allow', 0));
		$this->config->set('game_recent', $this->request->variable('game_recent', 0));
		$this->config->set('game_popular', $this->request->variable('game_popular', 0));
		$this->config->set('game_play', $this->request->variable('game_play', 0));
		$this->config->set('game_recent_index', $this->request->variable('game_recent_index', 0));
		$this->config->set('game_popular_index', $this->request->variable('game_popular_index', 0));
		$this->config->set('game_play_index', $this->request->variable('game_play_index', 0));
		$this->config->set('game_index_ext_stats', $this->request->variable('game_index_ext_stats', 0));
		$this->config->set('game_display_topic', $this->request->variable('game_display_topic', 0));
		$this->config->set('game_topic_sep', $this->request->variable('game_topic_sep', 0));
		$this->config->set('game_topic_limit', $this->request->variable('game_topic_limit', 0));
		//
		$this->config->set('game_recent_portal', $this->request->variable('game_recent_portal', 0));
		$this->config->set('game_popular_portal', $this->request->variable('game_popular_portal', 0));
		$this->config->set('game_play_portal', $this->request->variable('game_play_portal', 0));
		$this->config->set('game_portal_thumb_width', $this->request->variable('game_portal_thumb_width', 0));
		$this->config->set('game_portal_thumb_height', $this->request->variable('game_portal_thumb_height', 0));
		$this->config->set('game_recent_side_portal', $this->request->variable('game_recent_side_portal', 0));
		$this->config->set('game_popular_side_portal', $this->request->variable('game_popular_side_portal', 0));
		$this->config->set('game_play_side_portal', $this->request->variable('game_play_side_portal', 0));
		$this->config->set('game_stats_portal', $this->request->variable('game_stats_portal', 0));
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
		// Grab all the games
		$entities = $this->game_operator->get_games($parent_id);

		// Initialize a variable to hold the right_id value
		$last_right_id = 0;

		// Process each game entity for display
		foreach ($entities as $entity)
		{
			if ($entity->get_left_id() < $last_right_id)
			{
				continue; // The current game is a child of a previous game, do not display it
			}

			// Set output block vars for display in the template
			$this->template->assign_block_vars('games', array(
				'GAME_TITLE'		=> $entity->get_title(),

				'S_IS_CATEGORY'		=> ($entity->get_right_id() - $entity->get_left_id() > 1) ? true : false,

				'U_DELETE'			=> "{$this->u_action}&amp;action=delete&amp;game_id=" . $entity->get_id(),
				'U_EDIT'			=> "{$this->u_action}&amp;action=edit&amp;game_id=" . $entity->get_id(),
				'U_MOVE_DOWN'		=> "{$this->u_action}&amp;action=move_down&amp;game_id=" . $entity->get_id() . '&amp;hash=' . generate_link_hash('down' . $entity->get_id()),
				'U_MOVE_UP'			=> "{$this->u_action}&amp;action=move_up&amp;game_id=" . $entity->get_id() . '&amp;hash=' . generate_link_hash('up' . $entity->get_id()),
				'U_GAME'			=> "{$this->u_action}&amp;parent_id=" . $entity->get_id(),
			));

			// Store the current right_id value
			$last_right_id = $entity->get_right_id();
		}

		// Prepare game breadcrumb path navigation
		$entities = $this->game_operator->get_game_parents($parent_id);

		// Process each game entity for breadcrumb display
		foreach ($entities as $entity)
		{
			// Set output block vars for display in the template
			$this->template->assign_block_vars('breadcrumb', array(
				'GAME_TITLE'		=> $entity->get_title(),

				'S_CURRENT_LEVEL'	=> ($entity->get_id() == $parent_id) ? true : false,

				'U_GAME'			=> "{$this->u_action}&amp;parent_id=" . $entity->get_id(),
			));
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'U_ACTION'		=> "{$this->u_action}&amp;parent_id={$parent_id}",
			'U_ADD_GAME'	=> "{$this->u_action}&amp;parent_id={$parent_id}&amp;action=add",
			'U_MAIN'		=> "{$this->u_action}&amp;parent_id=0",
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
		$entity = $this->container->get('tacitus89.gamesmod.entity');

		// Build game parent pull down menu
		$this->build_parent_select_menu($entity, $parent_id, $mode = 'add');

		// Collect the form data
		$data = array(
			'game_parent_id'	=> $this->request->variable('game_parent', $parent_id),
			'game_title'		=> $this->request->variable('game_title', '', true),
			'game_description'	=> $this->request->variable('game_description', '', true),
		);

		// Process the new game
		$this->add_edit_game_data($entity, $data);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ADD_GAME'		=> true,

			'U_ADD_ACTION'		=> "{$this->u_action}&amp;parent_id={$parent_id}&amp;action=add",
			'U_BACK'			=> "{$this->u_action}&amp;parent_id={$parent_id}",
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
		$entity = $this->container->get('tacitus89.gamesmod.entity')->load($game_id);

		// Build game parent pull down menu
		$this->build_parent_select_menu($entity);

		// Collect the form data
		$data = array(
			'game_parent_id'	=> $this->request->variable('game_parent', $entity->get_parent_id()),
			'game_title'		=> $this->request->variable('game_title', $entity->get_title(), true),
			'game_description'	=> $this->request->variable('game_description', $entity->get_description(), true),
			'game_image'		=> $this->request->variable('game_image', $entity->get_image(), true),
		);

		// Process the edited game
		$this->add_edit_game_data($entity, $data);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_EDIT_GAME'		=> true,
			'S_IS_CATEGORY'		=> ($entity->get_right_id() - $entity->get_left_id() > 1) ? true : false,

			'U_EDIT_ACTION'		=> "{$this->u_action}&amp;game_id={$game_id}&amp;action=edit",
			'U_BACK'			=> "{$this->u_action}&amp;parent_id={$entity->get_parent_id()}",
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

		// Create an array to collect errors that will be output to the user
		$errors = array();

		//Image Upload?
		$uploadfile = $this->request->file('uploadfile');
		if($uploadfile)
		{
			if (!class_exists('\fileupload'))
			{
				include($this->root_path . 'includes/functions_upload.' . $this->php_ext);
			}
			$upload = new \fileupload('GAME_', array('jpg', 'jpeg', 'gif', 'png'), 80000, 0, 0, 0, 0, explode('|', $config['mime_triggers']));
			$file = $upload->form_upload('uploadfile');
			$file->clean_filename('real', '', '');
			$destination = 'images/games';
			if( !is_dir($this->root_path . $destination) )
			{
				mkdir($this->root_path . $destination, 0644);
				//TODO: if failed?
			}
			$data['game_image'] = $file->realname;

			// Move file and overwrite any existing image
			$file->move_file($destination, true);
			
			if (sizeof($file->error))
			{
				$file->remove();
				trigger_error(implode('<br />', $file->error));
			}
			else
			{
				@chmod($this->root_path . $destination . '/' . $data['game_image'], 0644);
			}
		}
		
		// Grab the form's game data fields
		$game_fields = array(
			'title'			=> $data['game_title'],
			'description'	=> $data['game_description'],
			'image'			=> $data['game_image'],
		);
		
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

			// Do not allow an empty game title
			if ($entity->get_title() == '')
			{
				$errors[] = $this->user->lang('ACP_GAME_TITLE_EMPTY');
			}
		}

		// Insert or update game
		if ($submit && empty($errors))
		{
			if ($entity->get_id())
			{
				// Save the edited game entity to the database
				$entity->save();

				// Change game parent
				if (isset($data['game_parent_id']) && ($data['game_parent_id'] != $entity->get_parent_id()))
				{
					$this->game_operator->change_parent($entity->get_id(), $data['game_parent_id']);
				}

				// Show user confirmation of the saved game and provide link back to the previous page
				trigger_error($this->user->lang('ACP_GAME_EDITED') . adm_back_link("{$this->u_action}&amp;parent_id={$entity->get_parent_id()}"));
			}
			else
			{
				// Add a new game entity to the database
				$this->game_operator->add_game($entity, $data['game_parent_id']);

				// Show user confirmation of the added game and provide link back to the previous page
				trigger_error($this->user->lang('ACP_GAME_ADDED') . adm_back_link("{$this->u_action}&amp;parent_id={$data['game_parent_id']}"));
			}
		}
		
		//view existing images
		$dir = $this->root_path.'images/games';
		$options = '<option value=""></option>';
		$dirfile = array();
		if ($dh = opendir($dir))
		{
			while (($file = readdir($dh)) !== false)
			{
				if (strlen($file) >= 3 && ( strpos($file, '.gif',1) || strpos($file, '.jpg',1) || strpos($file, '.jpeg',1) || strpos($file, '.png',1) ))
				{
					$dirfile[] = $file;
				}
			}
			closedir($dh);
		}
		natsort($dirfile);
		foreach ($dirfile as $key => $value)
		{
			$options .= '<option value="' . $value . '">' . $value . '</option>';
		}
		
		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ERROR'			=> (sizeof($errors)) ? true : false,
			'ERROR_MSG'			=> (sizeof($errors)) ? implode('<br />', $errors) : '',

			'GAME_TITLE'		=> $entity->get_title(),
			'GAME_DESCRIPTION'	=> $entity->get_description(),
			'IMAGE_OPTIONS'		=> $options,
			'GAME_IMAGE'		=> '<img src="' . $this->root_path . 'images/games/' . $entity->get_image() . '" title="' . $entity->get_title() . '" style="max-width: 60px;"/>',
		));
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
		$entity = $this->container->get('tacitus89.gamesmod.entity')->load($game_id);

		// Use a confirmation box routine when deleting a game
		if (confirm_box(true))
		{
			// Delete the game on confirmation
			$this->game_operator->delete_game($game_id);

			// Show user confirmation of the deleted game and provide link back to the previous page
			trigger_error($this->user->lang('ACP_GAME_DELETED') . adm_back_link("{$this->u_action}&amp;parent_id={$entity->get_parent_id()}"));
		}
		else
		{
			// Request confirmation from the user to delete the game
			confirm_box(false, $this->user->lang('ACP_DELETE_RULE_CONFIRM'), build_hidden_fields(array(
				'mode' => 'management',
				'action' => 'delete',
				'game_id' => $game_id,
			)));

			// Use a redirect to take the user back to the previous page
			// if the user chose not delete the game from the confirmation page.
			redirect("{$this->u_action}&amp;parent_id={$entity->get_parent_id()}");
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
	*/
	public function move_game($game_id, $direction, $amount = 1)
	{
		// If the link hash is invalid, stop and show an error message to the user
		if (!check_link_hash($this->request->variable('hash', ''), $direction . $game_id))
		{
			trigger_error($this->user->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Move the game
		$this->game_operator->move($game_id, $direction, $amount);

		// Send a JSON response if an AJAX request was used
		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$json_response->send(array('success' => true));
		}

		// Initiate and load the game entity for no AJAX request
		$entity = $this->container->get('tacitus89.gamesmod.entity')->load($game_id);

		// Use a redirect to reload the current page
		redirect("{$this->u_action}&amp;parent_id={$entity->get_parent_id()}");
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
		$parent_id = ($mode == 'edit') ? $entity->get_parent_id() : $parent_id;

		// Prepare game pull-down field
		$game_menu_items = $this->game_operator->get_games();

		$padding = '';
		$padding_store = array();
		$right = 0;

		// Process each game menu item for pull-down
		foreach ($game_menu_items as $game_menu_item)
		{
			if ($game_menu_item->get_left_id() < $right)
			{
				$padding .= '&nbsp;&nbsp;';
				$padding_store[$game_menu_item->get_parent_id()] = $padding;
			}
			else if ($game_menu_item->get_left_id() > $right + 1)
			{
				$padding = (isset($padding_store[$game_menu_item->get_parent_id()])) ? $padding_store[$game_menu_item->get_parent_id()] : '';
			}

			$right = $game_menu_item->get_right_id();

			// Set output block vars for display in the template
			$this->template->assign_block_vars('gamemenu', array(
				'GAME_ID'			=> $game_menu_item->get_id(),
				'GAME_TITLE'		=> $padding . $game_menu_item->get_title(),

				'S_DISABLED'		=> ($mode == 'edit' && (($game_menu_item->get_left_id() > $entity->get_left_id()) && ($game_menu_item->get_right_id() < $entity->get_right_id()) || ($game_menu_item->get_id() == $entity->get_id()))) ? true : false,
				'S_GAME_PARENT'		=> ($game_menu_item->get_id() == $parent_id) ? true : false,
			));
		}
	}
}