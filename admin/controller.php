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

/**
 * General Controller of Batensteinform component - Fixed version
 */
class BatensteinformController extends JControllerLegacy
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $default_view = 'registrations';

    /**
     * Method to display a view
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types
     *
     * @return  BatensteinformController  This object to support chaining
     */
    public function display($cachable = false, $urlparams = array())
    {
        $view   = $this->input->get('view', 'registrations');
        $layout = $this->input->get('layout', 'default');
        $id     = $this->input->getInt('id');

        // Check for edit form
        if ($view == 'registration' && $layout == 'edit' && !$this->checkEditId('com_batensteinform.edit.registration', $id)) {
            // Somehow the person just went to the form - we don't allow that
            $app = JFactory::getApplication();
            $app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            $this->setRedirect(JRoute::_('index.php?option=com_batensteinform&view=registrations', false));

            return false;
        }

        return parent::display($cachable, $urlparams);
    }
}