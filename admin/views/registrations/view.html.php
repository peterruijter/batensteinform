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
 * View to display registrations list
 */
class BatensteinformViewRegistrations extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $sidebar;

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
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            $app = JFactory::getApplication();
            foreach ($errors as $error) {
                $app->enqueueMessage($error, 'error');
            }
            return false;
        }

        // Only add submenu on certain layouts
        if ($this->getLayout() !== 'modal') {
            BatensteinformHelper::addSubmenu('registrations');
        }

        $this->addToolbar();
        
        // Set sidebar for J3
        if (class_exists('JHtmlSidebar')) {
            $this->sidebar = JHtmlSidebar::render();
        } else {
            $this->sidebar = '';
        }

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar
     *
     * @return  void
     */
    protected function addToolbar()
    {
        $canDo = BatensteinformHelper::getActions();

        JToolbarHelper::title(JText::_('COM_BATENSTEINFORM_MANAGER_REGISTRATIONS'), 'batensteinform');

        if ($canDo->get('core.create')) {
            JToolbarHelper::addNew('registration.add');
        }

        if ($canDo->get('core.edit') && isset($this->items[0])) {
            JToolbarHelper::editList('registration.edit');
        }

        // Removed publish/unpublish buttons as they are not needed for registrations
        // if ($canDo->get('core.edit.state')) {
        //     JToolbarHelper::divider();
        //     JToolbarHelper::publish('registrations.publish', 'JTOOLBAR_PUBLISH', true);
        //     JToolbarHelper::unpublish('registrations.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        // }

        if ($canDo->get('core.delete')) {
            JToolbarHelper::divider();
            JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'registrations.delete', 'JTOOLBAR_DELETE');
        }

        if ($canDo->get('core.admin')) {
            JToolbarHelper::divider();
            JToolbarHelper::custom('registrations.exportCSV', 'download', 'download', 'COM_BATENSTEINFORM_EXPORT_CSV', false);
        }

        if ($canDo->get('core.admin') || $canDo->get('core.options')) {
            JToolbarHelper::preferences('com_batensteinform');
        }

        // Add filters for J3
        if (class_exists('JHtmlSidebar')) {
            JHtmlSidebar::setAction('index.php?option=com_batensteinform&view=registrations');

            JHtmlSidebar::addFilter(
                JText::_('COM_BATENSTEINFORM_SELECT_SCOUT_SECTION'),
                'filter_scout_section',
                JHtml::_('select.options', $this->getScoutSectionOptions(), 'value', 'text', $this->state->get('filter.scout_section'), true)
            );
        }
    }

    /**
     * Get scout section options for filter
     *
     * @return  array
     */
    protected function getScoutSectionOptions()
    {
        $options = array(
            JHtml::_('select.option', '', JText::_('JOPTION_SELECT_SCOUT_SECTION')),
            JHtml::_('select.option', 'welpen', JText::_('COM_BATENSTEINFORM_WELPEN')),
            JHtml::_('select.option', 'scouts', JText::_('COM_BATENSTEINFORM_SCOUTS')),
            JHtml::_('select.option', 'explorers', JText::_('COM_BATENSTEINFORM_EXPLORERS')),
            JHtml::_('select.option', 'stam', JText::_('COM_BATENSTEINFORM_STAM')),
            JHtml::_('select.option', 'sikas', JText::_('COM_BATENSTEINFORM_SIKAS')),
            JHtml::_('select.option', 'plus', JText::_('COM_BATENSTEINFORM_PLUS_SCOUTS')),
        );

        return $options;
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     */
    protected function getSortFields()
    {
        return array(
            'a.id' => JText::_('JGRID_HEADING_ID'),
            'a.calling_name' => JText::_('COM_BATENSTEINFORM_CALLING_NAME_LABEL'),
            'a.last_name' => JText::_('COM_BATENSTEINFORM_LAST_NAME_LABEL'),
            'a.birth_date' => JText::_('COM_BATENSTEINFORM_BIRTH_DATE_LABEL'),
            'a.scout_section' => JText::_('COM_BATENSTEINFORM_SCOUT_SECTION_LABEL'),
            'a.created_at' => JText::_('JGLOBAL_CREATED_DATE_LABEL')
        );
    }
}