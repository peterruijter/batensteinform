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
 * Registrations Controller
 */
class BatensteinformControllerRegistrations extends JControllerAdmin
{
    /**
     * Proxy for getModel
     *
     * @param   string  $name    The model name
     * @param   string  $prefix  The model prefix
     * @param   array   $config  The configuration array for the model
     *
     * @return  JModelLegacy
     */
    public function getModel($name = 'Registration', $prefix = 'BatensteinformModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

        /**
     * Method to delete one or more records
     *
     * @return  void
     */
    public function delete()
    {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
        
        // Get items to remove from the request
        $cid = JFactory::getApplication()->input->get('cid', array(), 'array');
        
        if (!is_array($cid) || count($cid) < 1) {
            JLog::add(JText::_('COM_BATENSTEINFORM_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
        } else {
            // Get the model
            $model = $this->getModel();
            
            // Make sure the item ids are integers
            $cid = array_map('intval', $cid);
            
            // Remove the items
            if ($model->delete($cid)) {
                $this->setMessage(JText::plural('COM_BATENSTEINFORM_N_ITEMS_DELETED', count($cid)));
            } else {
                $this->setMessage($model->getError(), 'error');
            }
        }
        
        $this->setRedirect(JRoute::_('index.php?option=com_batensteinform&view=registrations', false));
    }
    
    /**
     * Method to publish a list of items
     *
     * @return  void
     */
    public function publish()
    {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
        
        // Get items to publish from the request
        $cid = JFactory::getApplication()->input->get('cid', array(), 'array');
        $data = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3);
        $task = $this->getTask();
        $value = JArrayHelper::getValue($data, $task, 0, 'int');
        
        if (empty($cid)) {
            JLog::add(JText::_('COM_BATENSTEINFORM_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
        } else {
            // Get the model
            $model = $this->getModel();
            
            // Make sure the item ids are integers
            $cid = array_map('intval', $cid);
            
            // Publish the items
            if (!$model->publish($cid, $value)) {
                JLog::add($model->getError(), JLog::WARNING, 'jerror');
            } else {
                if ($value == 1) {
                    $ntext = 'COM_BATENSTEINFORM_N_ITEMS_PUBLISHED';
                } elseif ($value == 0) {
                    $ntext = 'COM_BATENSTEINFORM_N_ITEMS_UNPUBLISHED';
                } elseif ($value == 2) {
                    $ntext = 'COM_BATENSTEINFORM_N_ITEMS_ARCHIVED';
                } else {
                    $ntext = 'COM_BATENSTEINFORM_N_ITEMS_TRASHED';
                }
                $this->setMessage(JText::plural($ntext, count($cid)));
            }
        }
        
        $this->setRedirect(JRoute::_('index.php?option=com_batensteinform&view=registrations', false));
    }
    
    /**
     * Method to export registrations to CSV
     *
     * @return  void
     */
    public function exportCSV()
    {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
        
        // Check if user has permission to export
        if (!JFactory::getUser()->authorise('core.admin', 'com_batensteinform')) {
            $app = JFactory::getApplication();
            $app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $this->setRedirect(JRoute::_('index.php?option=com_batensteinform&view=registrations', false));
            return false;
        }
        
        // Get the model
        $model = $this->getModel('Registrations');
        
        // Get items (all registrations)
        $items = $model->getItems();
        
        // Prepare CSV data
        $csv = array();
        
        // CSV Header - Extended with all health fields
        $headers = array(
            // Basic Information
            'ID',
            'First Names',
            'Calling Name', 
            'Name Prefix',
            'Last Name',
            'Address',
            'Postal Code + City',
            'Birth Date',
            'Birth Place',
            'Scout Section',
            
            // Personal Contact
            'Phone Number',
            'Email Address',
            
            // Parent/Guardian Contact
            'Parent1 Name',
            'Parent1 Phone Number',
            'Parent1 Email Address',
            'Parent2 Name',
            'Parent2 Phone Number',
            'Parent2 Email Address',
            
            // Payment Information
            'IBAN',
            'Account Name',
            'Sign Date',
            
            // Image/Video Permissions
            'Images Website',
            'Images Social',
            'Images Newspaper',
            
            // Health Information - Swimming
            'Can Swim',
            'Swim Diplomas',
            
            // Emergency Contact
            'Emergency Contact Name',
            'Emergency Contact Relation',
            'Emergency Contact Phone',
            
            // Medical Information
            'Special Health Care',
            'Special Health Care Details',
            'Medication',
            'Medication Details',
            'Allergies',
            'Allergies Details',
            'Diet',
            'Diet Details',
            
            // Insurance and Medical Contacts
            'Health Insurance',
            'Policy Number',
            'GP Name',
            'GP Address',
            'GP Phone',
            'Dentist Name',
            'Dentist Address',
            'Dentist Phone',
            
            // Emergency Treatment Consent
            'Emergency Treatment Consent',
            
            // Additional Information
            'Comments',
            
            // System fields
            'Created At',
            'Updated At',
        );
        
        $csv[] = '"' . implode('","', $headers) . '"';
        
        // CSV Data
        foreach ($items as $item) {
            $row = array(
                // Basic Information
                $item->id ?? '',
                $item->first_name ?? '',
                $item->calling_name ?? '',
                $item->name_prefix ?? '',
                $item->last_name ?? '',
                $item->address ?? '',
                $item->postal_code_city ?? '',
                $item->birth_date ?? '',
                $item->birth_place ?? '',
                $item->scout_section ?? '',
                
                // Personal Contact
                $item->phone_number ?? '',
                $item->email_address ?? '',
                
                // Parent/Guardian Contact
                $item->parent1_name ?? '',
                $item->parent1_phone_number ?? '',
                $item->parent1_email_address ?? '',
                $item->parent2_name ?? '',
                $item->parent2_phone_number ?? '',
                $item->parent2_email_address ?? '',
                
                // Payment Information
                $item->iban ?? '',
                $item->account_name ?? '',
                $item->sign_date ?? '',
                
                // Image/Video Permissions
                $item->images_website ?? '',
                $item->images_social ?? '',
                $item->images_newspaper ?? '',
                
                // Health Information - Swimming
                $item->can_swim ?? '',
                $item->swim_diplomas ?? '',
                
                // Emergency Contact
                $item->emergency_contact_name ?? '',
                $item->emergency_contact_relation ?? '',
                $item->emergency_contact_phone ?? '',
                
                // Medical Information
                $item->special_health_care ?? '',
                $item->special_health_care_details ?? '',
                $item->medication ?? '',
                $item->medication_details ?? '',
                $item->allergies ?? '',
                $item->allergies_details ?? '',
                $item->diet ?? '',
                $item->diet_details ?? '',
                
                // Insurance and Medical Contacts
                $item->health_insurance ?? '',
                $item->policy_number ?? '',
                $item->gp_name ?? '',
                $item->gp_address ?? '',
                $item->gp_phone ?? '',
                $item->dentist_name ?? '',
                $item->dentist_address ?? '',
                $item->dentist_phone ?? '',
                
                // Emergency Treatment Consent
                $item->emergency_treatment_consent ?? '',
                
                // Additional Information
                $item->comments ?? '',
                
                // System fields
                $item->created_at ?? '',
                $item->updated_at ?? '',
            );
            
            // Escape quotes in data and handle newlines in text fields
            foreach ($row as &$field) {
                // Convert newlines to spaces for CSV readability
                $field = str_replace(array("\n", "\r"), ' ', $field);
                // Escape quotes
                $field = str_replace('"', '""', $field);
            }
            
            $csv[] = '"' . implode('","', $row) . '"';
        }
        
        // Set headers for download with UTF-8 BOM for Excel compatibility
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="batenstein_registrations_' . date('Y-m-d_H-i-s') . '.csv"');
        
        // Output UTF-8 BOM for Excel compatibility
        echo "\xEF\xBB\xBF";
        
        // Output CSV data
        echo implode("\n", $csv);
        
        // Close the application
        JFactory::getApplication()->close();
    }
}