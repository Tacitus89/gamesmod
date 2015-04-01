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
	* @param \phpbb\boardrules\operators\rule     $rule_operator   Rule operator object
	* @param string                               $root_path       phpBB root path
	* @param string                               $php_ext         phpEx
	* @return \phpbb\boardrules\controller\admin_controller
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, ContainerInterface $container, $root_path, $php_ext)
	{
		$this->config = $config;
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->container = $container;
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