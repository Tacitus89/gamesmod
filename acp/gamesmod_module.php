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
	public $new_config;

	function main($id, $mode)
	{
		global $phpbb_container, $request, $user;

		// Add the board rules ACP lang file
		$user->add_lang_ext('tacitus89/gamesmod', 'gamesmod_acp');

		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('tacitus89.gamesmod.admin.controller');

		// Requests
		$action	= request_var('action', '');
		$submode = request_var('submode', '');

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
			/*
				// Load a template from adm/style for our ACP page
				$this->tpl_name = 'acp_games';

				// Set the page title for our ACP page
				$this->page_title = $user->lang('ACP_GAMES_INDEX');

				// Perform any actions submitted by the user
				switch($action)
				{
					case 'add':
						// Set the page title for our ACP page
						$this->page_title = $user->lang('ACP_BOARDRULES_CREATE_RULE');

						// Load the add rule handle in the admin controller
						$admin_controller->add_rule($language, $parent_id);

						// Return to stop execution of this script
						return;
					break;

					case 'edit':
						// Set the page title for our ACP page
						$this->page_title = $user->lang('ACP_BOARDRULES_EDIT_RULE');

						// Load the edit rule handle in the admin controller
						$admin_controller->edit_rule($rule_id);

						// Return to stop execution of this script
						return;
					break;

					case 'move_down':
						// Move a rule down one position
						$admin_controller->move_rule($rule_id, 'down');
					break;

					case 'move_up':
						// Move a rule up one position
						$admin_controller->move_rule($rule_id, 'up');
					break;

					case 'delete':
						// Delete a rule
						$admin_controller->delete_rule($rule_id);
					break;
				}

				// Check if a language variable was submitted and display
				// the rules for that language. If no language was submitted,
				// display the language selection menu.
				if (empty($language))
				{
					$admin_controller->display_language_selection();
				}
				else
				{
					$admin_controller->display_rules($language, $parent_id);
				}
				*/
			break;
		}
	}
}
