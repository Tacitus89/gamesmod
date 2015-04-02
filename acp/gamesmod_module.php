<?php
/**
*
* @package GamesMod
* @copyright (c) 2015
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tacitus89\gamesmod\acp;

class gamesmod_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $phpbb_container, $request, $user;

		// Add the board rules ACP lang file
		$user->add_lang_ext('tacitus89/gamesmod', 'gamesmod_acp');

		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('tacitus89.gamesmod.admin.controller');

		// Requests
		$action	= $request->variable('action', '');
		$game_id = $request->variable('game_id', 0);
		$parent_id = $request->variable('parent_id', 0);

		// Make the $u_action url available in the admin controller
		$admin_controller->set_page_url($this->u_action);

		// Load the "settings" or "manage" module modes
		switch($mode)
		{
			case 'config':
				// Load a template from adm/style for our ACP page
				$this->tpl_name = 'acp_games_config';

				// Set the page title for our ACP page
				$this->page_title = $user->lang('ACP_GAMES_INDEX');

				// Load the display options handle in the admin controller
				$admin_controller->display_options();
			break;

			case 'management':
				// Load a template from adm/style for our ACP page
				$this->tpl_name = 'acp_games';

				// Set the page title for our ACP page
				$this->page_title = $user->lang('ACP_GAMES_INDEX');
				
				// Perform any actions submitted by the user
				switch($action)
				{
					case 'add':
					
						//$this->tpl_name = 'acp_games_new';
						$this->page_title = $user->lang['ACP_GAMES_INDEX'];
						
						// Load the add game handle in the admin controller
						$admin_controller->add_game($parent_id);
						
						// Return to stop execution of this script
						return;
					break;

					case 'edit':
						// Set the page title for our ACP page
						$this->page_title = $user->lang('ACP_GAMES_INDEX');

						// Load the edit game handle in the admin controller
						$admin_controller->edit_game($game_id);

						// Return to stop execution of this script
						return;
					break;

					case 'move_down':
						// Move a game down one position
						$admin_controller->move_game($game_id, 'down');
					break;

					case 'move_up':
						// Move a game up one position
						$admin_controller->move_game($game_id, 'up');
					break;

					case 'delete':
						// Delete a game
						$admin_controller->delete_game($game_id);
					break;
				}
				
				$admin_controller->display_games($parent_id);
			break;
		}
	}
}
