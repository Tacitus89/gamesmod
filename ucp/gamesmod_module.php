<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tacitus89\gamesmod\ucp;

/**
* @package ucp
*/
class gamesmod_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $phpbb_container, $request, $user;

		// Add the game ucp lang file
		$user->add_lang_ext('tacitus89/gamesmod', 'gamesmod_ucp');

		// Get an instance of the ucp controller
		$ucp_controller = $phpbb_container->get('tacitus89.gamesmod.ucp.controller');

		// Requests
		$action	= $request->variable('action', '');
		$game_id = $request->variable('game_id', 0);
		$parent_id = $request->variable('parent_id', 0);

		// Make the $u_action url available in the admin controller
		$ucp_controller->set_page_url($this->u_action);
		
		$this->page_title = 'GAMES';
		$this->tpl_name = 'ucp_games';

		// Load the "settings" or "manage" module modes
		switch($mode)
		{
			case 'index':
				switch($action)
				{
					case 'share_view':
					break;
					
					case 'share_add':
						$this->page_title = 'GAMES_SHARING';
						$ucp_controller->share_add_game($game_id, $parent_id);
						return;
					break;
					
					case 'share_delete':
						$ucp_controller->share_delete_game($game_id);
					break;
					
					case 'play_add':
						$ucp_controller->play_add_game($game_id);
					break;
					
					case 'play_delete':
						$ucp_controller->play_delete_game($game_id);
					break;
					
					default:
						if($action == 'remove_game')
						{
							$ucp_controller->remove_user_game($parent_id);
						}
					break;
				}
				$ucp_controller->display_owned_games($parent_id);
			break;
			case 'add':

				if($action == 'add_game')
				{
					$ucp_controller->add_user_game($parent_id);
				}
				
				$this->page_title = 'ADDGAMES';
				$ucp_controller->display_games($parent_id);
			break;
		}
	}
}
