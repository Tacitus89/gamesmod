<?php
/**
*
* @package Games Mod for phpBB3.1
* @copyright (c) 2015 Marco Candian (tacitus@strategie-zone.de)
* @copyright (c) 2009-2011 Martin Eddy (mods@mecom.com.au)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ACP_GAMES_INDEX'				=> 'Games Control Panel',
	'ACP_GAMES_TITLE'				=> 'Games Management',
	'ACP_GAMES_SETTINGS'			=> 'Configuration',

	// ACP Logs
	'ACP_GAMESMOD_SETTINGS_LOG'		=> '<strong>Games Mod settings changed</strong>',
	'ACP_GAMESMOD_GAME_EDIT_LOG'	=> '<strong>Game edited</strong><br />» %s',
	'ACP_GAMESMOD_GAME_NEW_LOG'		=> '<strong>Game added</strong><br />» %s',
	'ACP_GAMESMOD_GAME_DELETE_LOG'	=> '<strong>Game deleted</strong><br />» %s',
	'ACP_GAMESMOD_GAME_CAT_EDIT_LOG'	=> '<strong>Game category edited</strong><br />» %s',
	'ACP_GAMESMOD_GAME_CAT_ADD_LOG'		=> '<strong>Game category added</strong><br />» %s',
	'ACP_GAMESMOD_GAME_CAT_DELETE_ALL_LOG'		=> '<strong>Game category and contained games deleted</strong><br />» %s',
	'ACP_GAMESMOD_GAME_CAT_DELETE_MOVING_LOG'	=> '<strong>Game category deleted and contained games moved</strong><br />» %s',
	'ACP_GAMESMOD_CLEAR_SEO_URL_LOG'			=> '<strong>Game Extension: All routes have been deleted!</strong>',
	'ACP_GAMESMOD_CREATE_SEO_URL_LOG'			=> '<strong>Game Extension: All routes have been created!</strong>',
));
