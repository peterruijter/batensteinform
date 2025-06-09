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
 * Registration Model
 */
class BatensteinformModelRegistration extends JModelAdmin
{
    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JTable  A JTable object
     */
    public function getTable($type = 'Registration', $prefix = 'BatensteinformTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm|boolean  A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form
        $form = $this->loadForm(
            'com_batensteinform.registration',
            'registration',
            array(
                'control' => 'jform',
                'load_data' => $loadData
            )
        );

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data
        $data = JFactory::getApplication()->getUserState(
            'com_batensteinform.edit.registration.data',
            array()
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed  Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);
        
        if ($item) {
            // Convert the params field to an array if it exists
            if (property_exists($item, 'params')) {
                $registry = new JRegistry($item->params);
                $item->params = $registry->toArray();
            }
        }
        
        return $item;
    }
    
    /**
     * Prepare and sanitize the table prior to saving.
     *
     * @param   JTable  $table  A reference to a JTable object.
     *
     * @return  void
     */
    protected function prepareTable($table)
    {
        $date = JFactory::getDate();
        $user = JFactory::getUser();
        
        if (empty($table->id)) {
            // Set the created_at for new records
            if (!(int) $table->created_at) {
                $table->created_at = $date->toSql();
            }
        } else {
            // Set updated_at for existing records
            $table->updated_at = $date->toSql();
        }
    }
}
