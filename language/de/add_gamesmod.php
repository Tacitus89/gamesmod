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
	'VIEW_TILES'				=> 'Display game tiles',
	'GAMES'						=> 'Games',
    'GAMES_VIEW'				=> 'Games',
	'GAMES_VIEW_EXPLAIN'		=> 'View Games',
	'VIEWING_GAMES' 			=> 'Viewing games',
	'ATTACH_GAMES'				=> 'Attach my games',
	'GAMES_TOTAL' 				=> 'Total games owned',
	'GAME_MOST_POP' 			=> 'Most popular game',
	'GAME_LAST_ADDED' 			=> 'Newest game added',
	'GAMES_STATS'				=> 'Games statistics',
	'GAMES_POPULAR'				=> 'Most popular games',
	'GAMES_RECENT'				=> 'Recently added games',
));

?>
