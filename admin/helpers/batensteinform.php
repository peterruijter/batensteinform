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

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * Batensteinform component helper
 */
class BatensteinformHelper extends ContentHelper
{
    /**
     * Configure the Linkbar
     *
     * @param   string  $vName  The name of the active view
     *
     * @return  void
     */
    public static function addSubmenu($vName = 'registrations')
    {
        HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

        HTMLHelper::_('sidebar.addEntry',
            Text::_('COM_BATENSTEINFORM_SUBMENU_REGISTRATIONS'),
            'index.php?option=com_batensteinform&view=registrations',
            $vName == 'registrations'
        );
        
        // Add categories submenu if needed
        if (Factory::getApplication()->isClient('administrator')) {
            HTMLHelper::_('sidebar.addEntry',
                Text::_('COM_BATENSTEINFORM_SUBMENU_CATEGORIES'),
                'index.php?option=com_categories&extension=com_batensteinform',
                $vName == 'categories'
            );
        }
        
        if ($vName == 'categories') {
            JToolbarHelper::title(
                Text::sprintf('COM_CATEGORIES_CATEGORIES_TITLE', Text::_('COM_BATENSTEINFORM')),
                'batensteinform-categories'
            );
        }
    }
    
    /**
     * Gets a list of the actions that can be performed
     *
     * @param   string  $component  The component name
     * @param   string  $section    The access section name
     * @param   int     $id         The item ID
     *
     * @return  \JObject
     */
    public static function getActions($component = 'com_batensteinform', $section = '', $id = 0)
    {
        $user   = Factory::getUser();
        $result = new \JObject;

        if (empty($component)) {
            $component = 'com_batensteinform';
        }

        $actions = array(
            'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
        );

        foreach ($actions as $action) {
            if ($id) {
                $assetName = $component . '.' . $section . '.' . (int) $id;
            } else {
                $assetName = $component;
            }
            
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }
    
    /**
     * Get a list of scout section options
     *
     * @return  array
     */
    public static function getScoutSectionOptions()
    {
        $options = array(
            '' => Text::_('JOPTION_SELECT_SCOUT_SECTION'),
            'Welpen' => Text::_('COM_BATENSTEINFORM_WELPEN'),
            'Scouts' => Text::_('COM_BATENSTEINFORM_SCOUTS'),
            'Explorers' => Text::_('COM_BATENSTEINFORM_EXPLORERS'),
            'Stam' => Text::_('COM_BATENSTEINFORM_STAM'),
            'Sikas' => Text::_('COM_BATENSTEINFORM_SIKAS')
        );

        return $options;
    }
}