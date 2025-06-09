<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_batensteinform
 *
 * @copyright   Copyright (C) 2025 Scouting Batenstein. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Access check
if (!JFactory::getUser()->authorise('core.manage', 'com_batensteinform')) {
    throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

// Require helper file
JLoader::register('BatensteinformHelper', JPATH_COMPONENT . '/helpers/batensteinform.php');

// Get an instance of the controller prefixed by Batensteinform
$controller = JControllerLegacy::getInstance('Batensteinform');

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();
