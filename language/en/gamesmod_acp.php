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
	'ACP_GAMES_INDEX'				=> 'Games Control Panel',
	'ACP_GAMES_INDEX_EXPLAIN'		=> 'Games Index Explain',
	'ACP_GAMES_TITLE'				=> 'Games Management',
	'ACP_GAMES_SETTINGS'			=> 'Configuration',
// Games Management
	'ACP_GAME_MGT_TITLE'				=> 'Game Management',
	'ACP_GAME_MGT_DESC'				=> 'Here you can view, create, modify, and delete game categories',

	'ACP_GAMES'							=> 'Games',
	'ACP_GAMES_DESC'					=> 'Here you can view, create, modify, and delete games for this category.',
	'ACP_GAME_LEGEND'					=> 'Game',
	'ACP_GAME_TITLE_EDIT'				=> 'Edit Game',
	'ACP_GAME_TEXT_EDIT'				=> 'Modify an existing game',
	'ACP_GAME_TITLE_ADD'				=> 'Create Game',
	'ACP_GAME_TEXT_ADD'					=> 'Create a new game from scratch',
	'ACP_GAME_DELETE_GOOD'				=> 'The game was removed successfully.',
	'ACP_GAME_EDIT_GOOD'				=> 'The game was updated successfully.',
	'ACP_GAME_ADD_FAIL'					=> 'No game name was listed for addition.',
	'ACP_GAME_ADD_GOOD'					=> 'The game was added successfully.',
	'ACP_CONFIRM_MSG_1'					=> 'Are you sure you wish to delete this game? This will also delete this game from any users that have it.',
	'ACP_NAME_TITLE'					=> 'Game Name',
	'ACP_NAME_DESC'						=> 'Game Description',
	'ACP_IMAGE_TITLE'					=> 'Game Image',
	'ACP_IMAGE_EXPLAIN'					=> 'The image for the game inside the images/games/ directory',
	'ACP_PARENT_TITLE'					=> 'Game Category',
	'ACP_PARENT_EXPLAIN'				=> 'The category that the game is to be put in',
	'ACP_PARENT_CHANGE_EXPLAIN'			=> 'If you change the category you will need to move the image to the new category\'s directory',
	'ACP_CREATE_GAME'					=> 'Create Game',
	'ACP_NO_GAMES'						=> 'No Games',
	'ACP_GAMES_MGT_INDEX'				=> 'Game Categories',
	'ACP_GAME_TITLE_CAT'				=> 'Edit Category',
	'ACP_GAME_TEXT_CAT'					=> 'Modify an existing category',
	'ACP_GAME_LEGEND_CAT'				=> 'Category',
	'ACP_NAME_TITLE_CAT'				=> 'Category Name',
	'ACP_DIR_TITLE'						=> 'Sub directory',
	'ACP_DIR_CHANGE_EXPLAIN'			=> 'If you change the directory you will need to move all the images to the new directory',
	'ACP_CREATE_CAT'					=> 'Create Category',
	'ACP_CAT_ADD_FAIL'					=> 'No category name was listed for addition.',
	'ACP_CAT_ADD_GOOD'					=> 'The category was added successfully.',
	'ACP_CAT_EDIT_GOOD'					=> 'The category was edited successfully.',
	'ACP_CAT_DELETE_CONFIRM'			=> 'Which category would you like to move all this category\'s games to upon deletion? <br /><form method="post"><fieldset class="submit-buttons"><select name="newcat">%s</select><br /><br /><input class="button1" type="submit" name="moveall" value="Move All Games" />&nbsp;<input class="button2" type="submit" name="deleteall" value="Delete All Games" />&nbsp;<input type="submit" class="button2" name="cancelcat" value="Cancel Deletion" /></fieldset></form>',
	'ACP_CAT_DELETE_CONFIRM_ELSE'		=> 'There are no other categories to move these games to.<br />Are you sure you wish to remove this category and all of its games?<br /><form method="post"><fieldset class="submit-buttons"><br /><input class="button2" type="submit" name="deleteall" value="Yes" />&nbsp;<input type="submit" class="button2" name="cancelcat" value="No" /></fieldset></form>',
	'ACP_CAT_DELETE_GOOD'				=> 'This category, all of its contents, and all of its contents that were awarded were deleted successfully',
	'ACP_CAT_DELETE_MOVE_GOOD'			=> 'All games from "%1$s" have been moved to "%2$s" and the category has been deleted successfully.',
	'ACP_NO_CATS'						=> 'No Categories',
	'UPLOAD_GAME_FILE'					=> 'Upload game image',
// Upload errors
	'GAME_GENERAL_UPLOAD_ERROR'			=> 'Could not upload game image to %s.',
	'GAME_DISALLOWED_CONTENT'			=> 'The upload was rejected because the uploaded file was identified as a possible attack vector.',
	'GAME_DISALLOWED_EXTENSION'			=> 'This file cannot be uploaded because the extension <strong>%s</strong> is not allowed.',
	'GAME_EMPTY_REMOTE_DATA'			=> 'The specified game image could not be uploaded because the remote data appears to be invalid or corrupted.',
	'GAME_EMPTY_FILEUPLOAD'				=> 'The uploaded game image file is empty.',
	'GAME_INVALID_FILENAME'				=> '%s is an invalid filename.',
	'GAME_NOT_UPLOADED'					=> 'Game image could not be uploaded.',
	'GAME_NO_SIZE'						=> 'The width or height of the linked game image could not be determined. Please enter them manually.',
	'GAME_PARTIAL_UPLOAD'				=> 'The specified file was only partially uploaded.',
	'GAME_PHP_SIZE_NA'					=> 'The game image filesize is too large.<br />The maximum allowed filesize set in php.ini could not be determined.',
	'GAME_PHP_SIZE_OVERRUN'				=> 'The game image filesize is too large. The maximum allowed upload size is %1$d %2$s.<br />Please note this is set in php.ini and cannot be overridden.',
	'GAME_URL_INVALID'					=> 'The URL you specified is invalid.',
	'GAME_URL_NOT_FOUND'				=> 'The file specified could not be found.',
	'GAME_WRONG_FILESIZE'				=> 'The game image filesize must be between 0 and %1d %2s.',
	'GAME_WRONG_SIZE'					=> 'The submitted game image is %5$d pixels wide and %6$d pixels high. Games must be at least %1$d pixels wide and %2$d pixels high, but no larger than %3$d pixels wide and %4$d pixels high.',
// Games Configuration
	'ACP_GAMES_CONFIG_TITLE'			=> 'Games Configuration',
	'ACP_GAMES_CONFIG_DESC'				=> 'Here you can set options for the Games Mod',
	'ACP_GAMES_CONF_SETTINGS'			=> 'Games Configuration Settings',
	'ACP_GAMES_CONF_SAVED'				=> 'Games configuration saved',
	'ACP_GAMES_SM_IMG_WIDTH'			=> 'Small game image width',
	'ACP_GAMES_SM_IMG_WIDTH_EXPLAIN'	=> 'The width (in pixels) for games displayed in the viewtopic and profile game information section.<br />Set to 0 to not define a width.',
	'ACP_GAMES_SM_IMG_HT'				=> 'Small game image height',
	'ACP_GAMES_SM_IMG_HT_EXPLAIN'		=> 'The height (in pixels) for games displayed in the viewtopic and profile game information section.<br />Set to 0 to not define a height.',
	'ACP_GAMES_VT_SETTINGS'				=> 'Viewtopic Display Settings',
	'ACP_GAMES_TOPIC_DISPLAY'			=> 'Allow Game Display in Viewtopic',
	'ACP_GAMES_ACTIVATE' 				=> 'Games MOD Activated',
	'ACP_GAMES_DESCRIPTION'				=> 'Use a description field',
	'ACP_GAMES_TOPIC_SEPARATE'			=> 'Display each catergory on a separate row',
	'ACP_GAMES_RECENT'					=> 'How many recently added games to show on the games page',
	'ACP_GAMES_RECENT_EXPLAIN'			=> 'Leave at zero to disable',
	'ACP_GAMES_POPULAR'					=> 'How many most popular games to show on the games page',
	'ACP_GAMES_POPULAR_EXPLAIN'			=> 'Leave at zero to disable',
	'ACP_GAMES_PLAY_ALLOW'				=> 'Games being played enabled',
	'ACP_GAMES_PLAY'					=> 'How many games being played to show on the games page',
	'ACP_GAMES_PLAY_EXPLAIN'			=> 'Leave at zero to disable',
	'ACP_GAMES_SHARE_ALLOW'				=> 'Games sharing enabled',
	'ACP_GAMES_PAGE_SETTINGS'			=> 'Games page settings',
	'ACP_GAMES_PAGE_INDEX_SETTINGS'		=> 'Index page settings',
	'ACP_GAMES_PLAY_INDEX'				=> 'How many games being played to show on the index page',
	'ACP_GAMES_PLAY_INDEX_EXPLAIN'		=> 'Leave at zero to disable',
	'ACP_GAMES_RECENT_INDEX'			=> 'How many recently added games to show on the index page',
	'ACP_GAMES_RECENT_INDEX_EXPLAIN'	=> 'Leave at zero to disable',
	'ACP_GAMES_POPULAR_INDEX'			=> 'How many most popular games to show on the index page',
	'ACP_GAMES_POPULAR_INDEX_EXPLAIN'	=> 'Leave at zero to disable',
	'ACP_GAMES_PAGINATION'				=> 'Games per page',
	'ACP_GAMES_PAGINATION_EXPLAIN'		=> 'This is for pagination on the main games page, ACP and UCP',
	'ACP_GAMES_INDEX_EXT_STATS'			=> 'Display newest game and most popular game with total games owned',
	'ACP_GAMES_TOPIC_LIMIT'				=> 'Limit how many games show in viewtopic',
	'ACP_GAMES_TOPIC_LIMIT_EXPLAIN'		=> 'Leave at zero to disable',
	// Portal settings
	'ACP_GAMES_PORTAL_SETTINGS'			=> 'Portal center block settings',
	'ACP_GAMES_RECENT_PORTAL'			=> 'How many recently added games to show on the portal page',
	'ACP_GAMES_RECENT_PORTAL_EXPLAIN'	=> 'Leave at zero to disable',
	'ACP_GAMES_POPULAR_PORTAL'			=> 'How many most popular games to show on the portal page',
	'ACP_GAMES_POPULAR_PORTAL_EXPLAIN'	=> 'Leave at zero to disable',
	'ACP_GAMES_PLAY_PORTAL'				=> 'How many games being played to show on the portal page',
	'ACP_GAMES_PLAY_PORTAL_EXPLAIN'		=> 'Leave at zero to disable',
	//Portal side block settings
	'ACP_GAMES_SIDE_PORTAL_SETTINGS'	=> 'Portal side block settings',
	'ACP_GAMES_PORTAL_THUMB_WIDTH'			=> 'Side portal block thumbnail width',
	'ACP_GAMES_PORTAL_THUMB_WIDTH_EXPLAIN'	=> 'The width (in pixels) for games displayed in the portal side block.<br />Set to 0 to not define a width.',
	'ACP_GAMES_PORTAL_THUMB_HEIGHT'			=> 'Side portal block thumbnail height',
	'ACP_GAMES_PORTAL_THUMB_HEIGHT_EXPLAIN' => 'The height (in pixels) for games displayed in the portal side block.<br />Set to 0 to not define a height.',
	'ACP_GAMES_RECENT_SIDE_PORTAL'			=> 'How many recently added games to show on the portal side block',
	'ACP_GAMES_RECENT_SIDE_PORTAL_EXPLAIN'	=> 'Leave at zero to disable',
	'ACP_GAMES_POPULAR_SIDE_PORTAL'			=> 'How many most popular games to show on the portal side block',
	'ACP_GAMES_POPULAR_SIDE_PORTAL_EXPLAIN'	=> 'Leave at zero to disable',
	'ACP_GAMES_PLAY_SIDE_PORTAL'			=> 'How many games being played to show on the portal side block',
	'ACP_GAMES_PLAY_SIDE_PORTAL_EXPLAIN'	=> 'Leave at zero to disable',
	'ACP_GAMES_STATS_PORTAL'				=> 'Display games statistics',
	'ACP_GAMES_STATS_PORTAL_EXPLAIN'		=> 'Displays total games owned',


	//umil language
	'GAMES_MOD'					=> 'Games MOD',
	'INSTALL_GAMES_MOD'			=> 'Install Games MOD',
	'INSTALL_GAMES_MOD_CONFIRM'	=> 'Are you sure you want to install the Games MOD?',
	'UPDATE_GAMES_MOD'			=> 'Update Games MOD',
	'UPDATE_GAMES_MOD_CONFIRM'	=> 'Are you sure you want to update the Games MOD?',
	'UNINSTALL_GAMES_MOD'			=> 'Uninstall Games MOD',
	'UNINSTALL_GAMES_MOD_CONFIRM'	=> 'Are you sure you want to uninstall the Games MOD?',
	'UCP_GAMES_INDEX'				=> 'Games',
	'UCP_GAMES_INDEX_TITLE'             => 'Games Owned',
	'UCP_GAMES_ADD_TITLE'				=> 'Add Games',
	'GAME_TABLE_UPDATE'				=> 'Games table updated',
	//ACP User Games Managaement
	'ACP_USER_GAMES'				=> 'Users Games',
	'ACP_USER_GAMES_MANAGEMENT'		=> 'Manage Users Games',
	//Mod version check
	'ACP_MOD_VERSION_CHECK'	=> 'Check for MOD updates',
	'ANNOUNCEMENT_TOPIC'	=> 'Release Announcement',

	'CURRENT_VERSION'		=> 'Current Version',

	'DOWNLOAD_LATEST'		=> 'Download Latest Version',

	'LATEST_VERSION'		=> 'Latest Version',

	'NO_ACCESS_MODS_DIRECTORY'	=> 'Unable to open adm/mods, check to make sure that directory exists and you have read permission on that directory',
	'NO_INFO'					=> 'Version server could not be contacted',
	'NOT_UP_TO_DATE'			=> '%s is not up to date',

	'RELEASE_ANNOUNCEMENT'	=> 'Announcement Topic',
	'UP_TO_DATE'			=> '%s is up to date',

	'VERSION_CHECK'			=> 'MOD Version Check',
	'VERSION_CHECK_EXPLAIN'	=> 'Checks to see if your mods are up to date',
));

?>
