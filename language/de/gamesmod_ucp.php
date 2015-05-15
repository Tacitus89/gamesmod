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
	'UCP_GAMES_INDEX'							=> 'Games',
    'UCP_GAMES_INDEX_TITLE'                     => 'Games Owned',
	'UCP_GAMES_ADD_TITLE'						=> 'Add Games',
	'GAMES'										=> 'Games Owned',
	'GAMES_EXPLAIN'								=> 'View or delete the games you have added to your profile',
	'ADDGAMES'									=> 'Add Games',
	'ADDGAMES_EXPLAIN'							=> 'You can select games from this list to add to your profile.',
	'GAME_REMOVE_GOOD'							=> 'Game(s) removed',
	'GAME'										=> 'Game:',
	'IMAGE_PREVIEW'								=> 'Preview:',
	'IMAGE_ERROR'								=> 'You cannot select this as a game',
	'GAME_ADD_GOOD'								=> 'Game(s) added',
	'NO_GAME_ID'								=> 'No Game(s) Selected',
	'GAMES_UNOWNED'								=> 'Games not owned',
	'CAT'										=> 'Category',
	'GAMES_SHARE_PAGE_LINK'						=> 'View your shared games',
	'GAME_UNSHARE_TITLE'						=> 'Stop sharing this game',
	'GAME_SHARE_TITLE'							=> 'Share this game',
	'GAME_UNPLAY_TITLE'							=> 'Stop showing you want to play this game',
	'GAME_PLAY_TITLE'							=> 'Show you want to play this game',
	'GAME_SHARE_USER_SUCCESS'					=> 'You have shared this game with ',
	'GAMES_SHARING'								=> 'Share a game',
	'SELECT_USER'								=> 'Select user',
	'GAMES_SHARE'								=> 'You are sharing these games',
	'GAMES_SHARED'								=> 'Shared to',
	'GAMES_SHARED_TITLE'						=> 'Shared games',
	'NO_SELF'									=> 'You can\'t share a game with yourself',
	'NO_SHARE'									=> 'You aren\'t sharing any games',
	'NO_CATS'									=> 'No Categories',
	'CATPAGES'									=> 'Categories',
));

?>
