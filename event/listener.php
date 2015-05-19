<?php
/**
*
* @package Games Mod for phpBB3.1
* @copyright (c) 2015 Marco Candian (tacitus@strategie-zone.de)
* @copyright (c) 2009-2011 Martin Eddy (mods@mecom.com.au)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tacitus89\gamesmod\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \tacitus89\gamesmod\operators\games */
	protected $games_operator;

	/** @var \tacitus89\gamesmod\operators\games_cat */
	protected $games_cat_operator;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	protected $dir;

	/**
	* Constructor
	*
	* @param \phpbb\config\config        $config             Config object
	* @param \phpbb\config\db_text       $config_text        DB text object
	* @param \phpbb\controller\helper    $helper             Controller helper object
	* @param \phpbb\request\request      $request            Request object
	* @param \phpbb\template\template    $template           Template object
	* @param \phpbb\user                 $user               User object
	* @param \tacitus89\games\operators\games     $games_operator
	* @param \tacitus89\games\operators\games_cat $games_cat_operator
	* @param string                               $root_path       phpBB root path
	* @param string                               $php_ext         phpEx
	* @return \phpbb\boardannouncements\event\listener
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \tacitus89\gamesmod\operators\games $games_operator, \tacitus89\gamesmod\operators\games_cat $games_cat_operator, $root_path, $php_ext)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->games_operator = $games_operator;
		$this->games_cat_operator = $games_cat_operator;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;

		$this->dir = $this->root_path.'ext/tacitus89/gamesmod/images/';
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'					=> 'load_language_on_setup',
			'core.page_header'					=> 'add_page_header_link',
			'core.viewtopic_cache_guest_data'	=> 'viewtopic_cache_user',
			'core.viewtopic_cache_user_data'	=> 'viewtopic_cache_user',
			'core.viewtopic_post_rowset_data'	=> 'viewtopic_post_rowset_data',
			'core.viewtopic_modify_post_row'	=> 'add_games_at_viewtopic',
			'core.index_modify_page_title'		=> 'add_games_at_index',
			'core.viewonline_overwrite_location'=> 'viewonline_page',
			'core.ucp_prefs_view_data'			=> 'ucp_prefs_view_data',
			'core.ucp_prefs_view_update_data'	=> 'ucp_prefs_view_update_data',
			'core.memberlist_view_profile'		=> 'display_games_in_profile',
			'core.submit_post_modify_sql_data'	=> 'submit_post_modify_sql_data',
			'core.posting_modify_template_vars'	=> 'posting_modify_template_vars',
		);
	}

	/**
	* Load common gamesmod language files during user setup
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'tacitus89/gamesmod',
			'lang_set' => 'add_gamesmod',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	* Create a URL to the gamesmod controller file for the header linklist
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function add_page_header_link($event)
	{
		$this->template->assign_vars(array(
			'S_GAMES_ENABLED' => (!empty($this->config['games_active'])) ? true : false,
			'U_GAMES' => $this->helper->route('tacitus89_gamesmod_main_controller'),
		));
	}

	/**
	* Display the owned games in the profile
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function display_games_in_profile($event)
	{
		// Do not continue if gamesmod has been disabled
		// or user has been deactivated
		if (!$this->config['games_active'] || $event['member']['game_view'] == 0)
		{
			return;
		}

		// Grab all the games
		$entities = $this->games_operator->get_owned_games($event['member']['user_id']);

		$game_count = count($entities);

		//Do not continue if the user has not been games
		if($game_count < 1)
		{
			return;
		}

		// Process each game entity for display
		foreach ($entities as $entity)
		{
			//parent
			$parent = $this->games_cat_operator->get($entity->get_parent());
			$dir = ($parent->get_dir() != '') ? $parent->get_dir() . '/' : '';

			// Set output block vars for display in the template
			$this->template->assign_block_vars('games', array(
				'GAME_NAME'			=> $entity->get_name(),
				'GAME_IMAGE'		=> $this->dir.$dir.$entity->get_image(),
				'GAME_ID'			=> $entity->get_id(),

				'U_GAME'			=> $this->helper->route('tacitus89_gamesmod_main_controller', array('gid' => $entity->get_id())),
			));
		}

		// Add gamesmod language file
		$this->user->add_lang_ext('tacitus89/gamesmod', 'gamesmod');

		$width = $height = $style = '';
		if($this->config['game_small_img_width'])
		{
			$width = ' width="'. $this->config['game_small_img_width'] .'"';
			$style .= 'width:'. $this->config['game_small_img_width'] .'px;';
		}
		if($this->config['game_small_img_ht'])
		{
			$height	= ' height="'. $this->config['game_small_img_ht'] .'"';
			$style .= 'height:'. $this->config['game_small_img_ht'] .'px;';
		}

		// Output gamesmod to the template
		$this->template->assign_vars(array(
			'S_GAMES'			=> true,
			'GAME_SMALL_WIDTH'	=> $width,
			'GAME_SMALL_HEIGHT'	=> $height,
			'GAME_STYLE'		=> $style,
			'GAME_COUNT'		=> $game_count,
		));
	}

	/**
	* Add the data for viewtopics
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_cache_user($event)
	{
		// Do not continue if gamesmod has been disabled
		// or user has been deactivated
		if (!$this->config['games_active'])
		{
			return;
		}

		$user_cache_data = $event['user_cache_data'];
		$user_cache_data['game_count'] = $this->games_operator->get_gamers_count($event['row']['user_id']);

		if($this->config['game_display_topic'] && $this->config['game_topic_limit'] > 0 && $event['row']['game_view'] == 1)
		{
			$user_cache_data['games'] = $this->show_gamers_game($event['row']['user_id']);
		}

		$event['user_cache_data'] = $user_cache_data;
	}

	/**
	* show the games in the viewtopics
	* Adding to the Cache
	*
	* @param int $user_id The ID from user
	* @return string Games from user for viewtopics
	* @access private
	*/
	private function show_gamers_game($user_id)
	{
		$games = '';

		// Grab all the games
		$entities = $this->games_operator->get_owned_games($user_id);

		if(!empty($entities))
		{
			//Sort all entities to her parents
			$count = array();
			foreach ($entities as $entity)
			{
				if(empty($count[$entity->get_parent()]) || $count[$entity->get_parent()] < $this->config['game_topic_limit'])
				{
					$game[$entity->get_parent()][] = $entity;
					if(empty($count[$entity->get_parent()]))
					{
						$count[$entity->get_parent()] = 1;
					}
					else {
						$count[$entity->get_parent()]++;
					}

				}
			}

			$width = $height = '';
			if($this->config['game_small_img_width'])
			{
				$width = ' width="'. $this->config['game_small_img_width'] .'"';
			}
			if($this->config['game_small_img_ht'])
			{
				$height	= ' height="'. $this->config['game_small_img_ht'] .'"';
			}

			$games = '';
			// Process each game entity for display
			foreach ($game as $value)
			{
				foreach ($value as $value2)
				{
					//parent
					$parent = $this->games_cat_operator->get($value2->get_parent());
					$dir = ($parent->get_dir() != '') ? $parent->get_dir() . '/' : '';
					$games .= '<div style="float: left;"><a href="'. $this->helper->route('tacitus89_gamesmod_main_controller', array('gid' => $value2->get_id())) .'"><img src="'. $this->dir . $dir.$value2->get_image() .'" class="games_img" alt="'. $value2->get_name() .'" '. $width . $height .'" /></a></div>';
				}
				if($this->config['game_topic_sep'])
				{
					$games .= '<div style="clear:left"></div>';
				}
			}
		}

		return $games;
	}

	/**
	* Add the data for viewtopics
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_post_rowset_data($event)
	{
		$rowset_data = $event['rowset_data'];
		$rowset_data['enable_games'] = $event['row']['enable_games'];
		$event['rowset_data'] = $rowset_data;
	}

	/**
	* Display the owned games in the viewtopic profiles
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function add_games_at_viewtopic($event)
	{
		// Do not continue if gamesmod has been disabled
		//or games are disabled for this post
		if (!$this->config['games_active'])
		{
			return;
		}

		$post_row = $event['post_row'];
		$post_row['GAME_COUNT'] = $event['user_poster_data']['game_count'];
		if($event['row']['enable_games'])
		{
			$post_row['GAMES'] = $event['user_poster_data']['games'];
		}
		$event['post_row'] = $post_row;
	}

	/**
	* Show users as viewing the Gamesmod on viewonline page
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewonline_page($event)
	{
		if ($event['on_page'][1] == 'app')
		{
			if (strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/games') === 0)
			{
				$event['location'] = $this->user->lang('VIEWING_GAMES');
				$event['location_url'] = $this->controller_helper->route('tacitus89_gamesmod_main_controller');
			}
		}
	}

	/**
	* Adding option data
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function ucp_prefs_view_data($event)
	{
		// Do not continue if gamesmod has been disabled
		if (!$this->config['games_active'])
		{
			return;
		}

		$data = $event['data'];
		$data['gametiles'] = $this->user->data['game_view'];
		$event['data'] = $data;

		$this->template->assign_vars(array(
			'S_GAMES'			=> $event['data']['gametiles'],
		));
	}

	/**
	* Adding option data
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function ucp_prefs_view_update_data($event)
	{
		// Do not continue if gamesmod has been disabled
		if (!$this->config['games_active'])
		{
			return;
		}

		$data = $event['data'];
		$data['gametiles'] = $this->request->variable('gametiles', 1);
		$event['data'] = $data;

		$sql_ary = $event['sql_ary'];
		$sql_ary['game_view'] = $event['data']['gametiles'];
		$event['sql_ary'] = $sql_ary;
	}

	/**
	* Display the games on the index
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function add_games_at_index($event)
	{
		// Do not continue if gamesmod has been disabled
		if (!$this->config['games_active'])
		{
			return;
		}

		//Show popular games
		if($this->config['game_popular_index'] > 0)
		{
			//Get popular games
			$entities = $this->games_operator->get_popular_games($this->config['game_popular_index']);

			// Process each popular game entity for display
			foreach ($entities as $entity)
			{
				//parent
				$parent = $this->games_cat_operator->get($entity->get_parent());
				$dir = ($parent->get_dir() != '') ? $parent->get_dir() . '/' : '';
				// Set output block vars for display in the template
				$this->template->assign_block_vars('popular_games', array(
					'GAME_NAME'		=> $entity->get_name(),
					'GAME_IMAGE'	=> $this->dir.$dir.$entity->get_image(),

					'U_GAME'		=> $this->helper->route('tacitus89_gamesmod_main_controller', array('gid' => $entity->get_id())),
				));
			}
		}

		//Show recent games
		if($this->config['game_recent_index'] > 0)
		{
			//Get popular games
			$entities = $this->games_operator->get_recent_games($this->config['game_recent_index']);

			// Process each popular game entity for display
			foreach ($entities as $entity)
			{
				//parent
				$parent = $this->games_cat_operator->get($entity->get_parent());
				$dir = ($parent->get_dir() != '') ? $parent->get_dir() . '/' : '';
				// Set output block vars for display in the template
				$this->template->assign_block_vars('recent_games', array(
					'GAME_NAME'		=> $entity->get_name(),
					'GAME_IMAGE'	=> $this->dir.$dir.$entity->get_image(),

					'U_GAME'		=> $this->helper->route('tacitus89_gamesmod_main_controller', array('gid' => $entity->get_id())),
				));
			}
		}

		if($this->config['game_index_ext_stats'])
		{
			echo $event['member']['user_id'];
			$popular = $this->games_operator->get_popular_games(1);
			$recent = $this->games_operator->get_recent_games(1);
			$number = $this->games_operator->get_number_owned_games($event['member']['user_id']);

			if(!empty($popular) && !empty($number))
			{
				$this->template->assign_vars(array(
					'GAMES_OWNED'			=> $number,
					'GAMES_MOST_POP_NAME'	=> $popular[0]->get_name(),
					'GAMES_MOST_POP_URL'	=> $this->helper->route('tacitus89_gamesmod_main_controller', array('gid' => $popular[0]->get_id())),
				));
			}

			if(!empty($recent))
			{
				$this->template->assign_vars(array(
					'GAMES_NEWEST_NAME'		=> $recent[0]->get_name(),
					'GAMES_NEWEST_URL'		=> $this->helper->route('tacitus89_gamesmod_main_controller', array('gid' => $recent[0]->get_id())),
				));
			}


		}
	}

	/**
	* Adding enable games to sql data of posts
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function submit_post_modify_sql_data($event)
	{
		$sql_data = $event['sql_data'];
		$sql_data[POSTS_TABLE]['sql']['enable_games'] = (isset($_POST['enable_games'])) ? true : false;
		$event['sql_data'] = $sql_data;
	}

	/**
	* Adding enable games to sql data of posts
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function posting_modify_template_vars($event)
	{
		$bool = false;
		//default: it is checked
		if(!isset($event['post_data']['enable_games']))
		{
			$bool = true;
		}
		//if enable_games == true?
		elseif($event['post_data']['enable_games'])
		{
			$bool = true;
		}
		
		$this->template->assign_vars(array(
			'S_GAMES_CHECKED'	=> ($bool)? ' checked="checked"' : '',
		));
	}
}
