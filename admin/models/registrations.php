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
 * Registrations Model
 */
class BatensteinformModelRegistrations extends JModelList
{
    /**
     * Constructor
     *
     * @param   array  $config  An optional associative array of configuration settings
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'first_name', 'a.first_name',
                'calling_name', 'a.calling_name',
                'last_name', 'a.last_name',
                'birth_date', 'a.birth_date',
                'scout_section', 'a.scout_section',
                'created_at', 'a.created_at'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     */
    protected function populateState($ordering = 'a.id', $direction = 'desc')
    {
        $app = JFactory::getApplication('administrator');
        
        // Get the filter and search values
        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);
        
        $scoutSection = $app->getUserStateFromRequest($this->context . '.filter.scout_section', 'filter_scout_section', '', 'string');
        $this->setState('filter.scout_section', $scoutSection);
        
        // List state information
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a store id based on the model configuration state.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.scout_section');

        return parent::getStoreId($id);
    }

    /**
     * Method to get a JDatabaseQuery object for retrieving the data set from a database.
     *
     * @return  JDatabaseQuery  A JDatabaseQuery object to retrieve the data set.
     */
     protected function getListQuery()
    {
        // Create a new query object
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Select all fields from the table for admin interface
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.first_name, a.calling_name, a.name_prefix, a.last_name, ' .
                'a.address, a.postal_code_city, a.birth_date, a.birth_place, a.scout_section, ' .
                'a.phone_number, a.email_address, ' .
                'a.parent1_name, a.parent1_phone_number, a.parent1_email_address, ' .
                'a.parent2_name, a.parent2_phone_number, a.parent2_email_address, ' .
                'a.iban, a.account_name, a.sign_date, ' .
                'a.images_website, a.images_social, a.images_newspaper, ' .
                'a.can_swim, a.swim_diplomas, ' .
                'a.emergency_contact_name, a.emergency_contact_relation, a.emergency_contact_phone, ' .
                'a.special_health_care, a.special_health_care_details, ' .
                'a.medication, a.medication_details, ' .
                'a.allergies, a.allergies_details, ' .
                'a.diet, a.diet_details, ' .
                'a.health_insurance, a.policy_number, ' .
                'a.gp_name, a.gp_address, a.gp_phone, ' .
                'a.dentist_name, a.dentist_address, a.dentist_phone, ' .
                'a.emergency_treatment_consent, ' .
                'a.comments, ' .
                'a.created_at, a.updated_at'
            )
        );
        $query->from($db->quoteName('#__batenstein_registrations') . ' AS a');

        // Filter by search in name or email
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where(
                    '(a.first_name LIKE ' . $search . ' OR a.calling_name LIKE ' . $search . 
                    ' OR a.last_name LIKE ' . $search . ' OR a.email_address LIKE ' . $search .
                    ' OR a.parent1_email_address LIKE ' . $search . ' OR a.parent2_email_address LIKE ' . $search .
                    ' OR a.emergency_contact_name LIKE ' . $search . ')'
                );
            }
        }
        
        // Filter by scout section
        $scoutSection = $this->getState('filter.scout_section');
        if (!empty($scoutSection)) {
            $query->where('a.scout_section = ' . $db->quote($scoutSection));
        }

        // Add the list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'a.id');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        
        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
        }

        return $query;
    }
    
    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     */
    public function getItems()
    {
        $items = parent::getItems();
        
        // If no items, return empty array instead of false
        if (!$items) {
            return array();
        }
        
        return $items;
    }
    
    /**
     * Method to delete one or more records.
     *
     * @param   array  &$pks  An array of record primary keys.
     *
     * @return  boolean  True if successful, false if an error occurs.
     */
    public function delete(&$pks)
    {
        $pks = (array) $pks;
        $table = $this->getTable();

        // Check if user has permission to delete
        $user = JFactory::getUser();
        if (!$user->authorise('core.delete', 'com_batensteinform')) {
            $this->setError(JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
            return false;
        }

        // Iterate through the items and delete each one
        foreach ($pks as $i => $pk) {
            if ($table->load($pk)) {
                if (!$table->delete($pk)) {
                    $this->setError($table->getError());
                    return false;
                }
            } else {
                $this->setError($table->getError());
                return false;
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }
    
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
     * Method to change the state of one or more records.
     *
     * @param   array    &$pks   A list of the primary keys to change.
     * @param   integer  $value  The value of the state.
     *
     * @return  boolean  True on success.
     */
    public function publish(&$pks, $value = 1)
    {
        $user = JFactory::getUser();
        $table = $this->getTable();
        $pks = (array) $pks;

        // Check if the user has permission to change state
        if (!$user->authorise('core.edit.state', 'com_batensteinform')) {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
            return false;
        }

        // Include the plugins for the change of state event
        JPluginHelper::importPlugin('content');

        // Access checks
        foreach ($pks as $i => $pk) {
            if ($table->load($pk)) {
                if (!$user->authorise('core.edit.state', 'com_batensteinform')) {
                    // Prune items that you can't change
                    unset($pks[$i]);
                    JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
                }
            }
        }

        // Attempt to change the state of the records
        if (!$table->publish($pks, $value, $user->get('id'))) {
            $this->setError($table->getError());
            return false;
        }

        return true;
    }
}