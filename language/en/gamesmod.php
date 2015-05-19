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
	'GAME_ADD'						=> 'Add game to your profile',
	'AWARDED_GAME'					=> 'Owned Games',
	'AWARDED_GAME_TO'				=> 'Games Owned by',
	'AWARD_GAME'					=> 'Add Game',
	'GAME_AWARD_GOOD'				=> 'Game awarded successfully!<br /><br /><a href="%s">Return to the previous page</a>',
	'GAME_REMOVE_GOOD'				=> 'Game removed successfully!<br /><br /><a href="%s">Return to the previous page</a>',
	'IMAGE_PREVIEW'					=> 'Preview',
	'GAME_IMG'						=> 'Image',
	'GAME'							=> 'Game',
	'GAMES'							=> 'Games',
	'GAMES_VIEW'					=> 'Games',
	'GAME_AWARDED'					=> 'Owners',
	'GAME_NAME'						=> 'Name',
	'NUMBER_GAMES'					=> 'Games',
	'NO_GAMES_ISSUED'				=> 'No one owns this game',
	'GAME_CP'						=> 'Games Control Panel',
	'GAME_AMOUNT'					=> 'Amount',
	'CATPAGES'						=> 'Categories',
	'GAMES_VIEW_BUTTON'				=> 'View my Games',
	'GAMES_PM'						=> 'Send Private Message to all game owners',
	'GAME_DESC'						=> 'Game Description',
	'GAMES_STATS'					=> 'Games statistics',
	'GAMES_POPULAR'					=> 'Most popular games',
	'GAMES_RECENT'					=> 'Recently added games',
	'GAMES_PLAY'					=> 'I\'m currently playing',
	'ALL'							=> 'ALL',
	'GAME_PLAYING'					=> 'user(s) want to play this game',
	'GAME_PLAY'						=> 'Users are playing these games',

// Error messages
	'NO_CAT_ID'						=> 'No Category ID was specified.',
	'NO_CATS'						=> 'No Categories',
	'NO_GAME_ID'					=> 'No Game ID was specified',
	'NO_GAMES'						=> 'No Available Games',
	'NO_USER_ID'					=> 'No User ID was specified',
	'NO_USER_GAMES'					=> 'This user doesn\'t own any games',

));

?>
