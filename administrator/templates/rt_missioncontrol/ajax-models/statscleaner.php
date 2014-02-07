<?php
/**
 * @version RocketTheme 2.6 April 10, 2012
 * @author  RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2012 RocketTheme, LLC
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted index access' );
jimport('joomla.cache.cache');

global $mctrl;
$table = false;

$action = JRequest::getWord('action',null);
if ($action == 'user') {
	$table = 'rokuserstats';
}
if ($action == 'admin') {
	$table = 'rokadminaudit';
}

if ($table) {
	$db = JFactory::getDBO();

	$query = 'delete from #__'.$table;
	$db->setQuery($query);
	$db->query();
	echo "1";
} else {
	echo "0";
}


?>
