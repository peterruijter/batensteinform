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
 * View to edit a registration
 */
class BatensteinformViewRegistration extends JViewLegacy
{
    protected $state;
    protected $item;
    protected $form;

    /**
     * Display the view
     *
     * @param   string  $tpl  Template name
     *
     * @return  void
     *
     * @throws  Exception
     */
    public function display($tpl = null)
    {
        $this->state = $this->get('State');
        $this->item  = $this->get('Item');
        $this->form  = $this->get('Form');

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            $app = JFactory::getApplication();
            foreach ($errors as $error) {
                $app->enqueueMessage($error, 'error');
            }
            return false;
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar
     *
     * @return  void
     *
     * @throws  Exception
     */
    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user       = JFactory::getUser();
        $isNew      = ($this->item->id == 0);
        
        // Since we don't have checkout functionality, just check if item exists
        $checkedOut = false;

        // Get permissions
        $canDo = BatensteinformHelper::getActions();

        JToolbarHelper::title(
            JText::_('COM_BATENSTEINFORM_MANAGER_REGISTRATION'),
            'batensteinform'
        );

        // If not checked out, can save the item.
        if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create')))) {
            JToolbarHelper::apply('registration.apply', 'JTOOLBAR_APPLY');
            JToolbarHelper::save('registration.save', 'JTOOLBAR_SAVE');
        }

        if (!$checkedOut && ($canDo->get('core.create'))) {
            JToolbarHelper::custom('registration.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
        }

        // If an existing item, can save to a copy.
        if (!$isNew && $canDo->get('core.create')) {
            JToolbarHelper::custom('registration.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
        }

        if (empty($this->item->id)) {
            JToolbarHelper::cancel('registration.cancel', 'JTOOLBAR_CANCEL');
        } else {
            JToolbarHelper::cancel('registration.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}