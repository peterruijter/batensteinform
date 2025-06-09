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
 * Registration Controller
 */
class BatensteinformControllerRegistration extends JControllerForm
{
    /**
     * Method to check if you can add a new record
     *
     * @param   array  $data  An array of input data
     *
     * @return  boolean
     */
    protected function allowAdd($data = array())
    {
        return JFactory::getUser()->authorise('core.create', 'com_batensteinform');
    }

    /**
     * Method to check if you can edit a record
     *
     * @param   array   $data  An array of input data
     * @param   string  $key   The name of the key for the primary key; default is id
     *
     * @return  boolean
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        $id = isset($data[$key]) ? $data[$key] : 0;
        
        if (!empty($id)) {
            // Get record owner
            $record = $this->getModel()->getItem($id);
            
            // Check edit permission
            if ($record && JFactory::getUser()->authorise('core.edit', 'com_batensteinform')) {
                return true;
            }
            
            // Check edit own permission
            if ($record && JFactory::getUser()->authorise('core.edit.own', 'com_batensteinform')) {
                // Check if created by current user
                $ownerId = $record->created_by;
                
                if (empty($ownerId) && $this->input->get('id') == $id) {
                    $ownerId = JFactory::getUser()->id;
                }
                
                if ($ownerId == JFactory::getUser()->id) {
                    return true;
                }
            }
        }
        
        return parent::allowEdit($data, $key);
    }
}
