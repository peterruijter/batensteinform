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
 * Registration Table class
 */
class BatensteinformTableRegistration extends JTable
{
    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__batenstein_registrations', 'id', $db);
    }

    /**
     * Overloaded bind function to pre-process the params.
     *
     * @param   array  $array   Named array
     * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  mixed  Null if operation was satisfactory, otherwise returns an error
     */
    public function bind($array, $ignore = '')
    {
        return parent::bind($array, $ignore);
    }

    /**
     * Validates Dutch IBAN format
     * Accepts format: NL46 ASNB 0708 4337 23 (with or without spaces)
     * 
     * @param string $iban The IBAN to validate
     * @return bool True if valid Dutch IBAN format, false otherwise
     */
    function validateDutchIban($iban)
    {
        // Remove all spaces from the IBAN
        $cleanIban = str_replace(' ', '', $iban);

        // Check if it starts with NL (case insensitive)
        if (strtoupper(substr($cleanIban, 0, 2)) !== 'NL') {
            return false;
        }

        // Check total length (Dutch IBAN should be 18 characters)
        if (strlen($cleanIban) !== 18) {
            return false;
        }

        // Check if characters 3-4 are digits (check digits)
        if (!ctype_digit(substr($cleanIban, 2, 2))) {
            return false;
        }

        // Check if characters 5-8 are letters (bank code)
        if (!ctype_alpha(substr($cleanIban, 4, 4))) {
            return false;
        }

        // Check if characters 9-18 are digits (account number)
        if (!ctype_digit(substr($cleanIban, 8, 10))) {
            return false;
        }

        return true;
    }

    /**
     * Enhanced IBAN validation with mod-97 checksum verification
     * 
     * @param string $iban The IBAN to validate
     * @return bool True if valid IBAN with correct checksum, false otherwise
     */
    function validateIbanWithChecksum($iban)
    {
        // First check the format
        if (!validateDutchIban($iban)) {
            return false;
        }

        // Remove spaces and convert to uppercase
        $cleanIban = strtoupper(str_replace(' ', '', $iban));

        // Move first 4 characters to the end
        $rearranged = substr($cleanIban, 4) . substr($cleanIban, 0, 4);

        // Replace letters with numbers (A=10, B=11, ..., Z=35)
        $numeric = '';
        for ($i = 0; $i < strlen($rearranged); $i++) {
            $char = $rearranged[$i];
            if (ctype_alpha($char)) {
                $numeric .= (ord($char) - ord('A') + 10);
            } else {
                $numeric .= $char;
            }
        }

        // Calculate mod 97
        $remainder = bcmod($numeric, '97');

        // Valid IBAN should have remainder of 1
        return $remainder === '1';
    }




    /**
     * Overloaded check function
     *
     * @return  boolean  True on success
     */
    public function check()
    {
        // Check for valid calling name
        if (trim($this->calling_name) == '') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_CALLING_NAME_EMPTY'));
            return false;
        }

        // Check for valid last name
        if (trim($this->last_name) == '') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_LAST_NAME_EMPTY'));
            return false;
        }

        // Check for valid email (parent 1)
        if (trim($this->parent1_email_address) == '') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_PARENT1_EMAIL_EMPTY'));
            return false;
        }

        // Validate email format (parent 1)
        if (!JMailHelper::isEmailAddress($this->parent1_email_address)) {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_PARENT1_EMAIL_INVALID'));
            return false;
        }

        // Validate email format (child if provided)
        if (!empty($this->email_address) && !JMailHelper::isEmailAddress($this->email_address)) {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_EMAIL_INVALID'));
            return false;
        }

        // Validate email format (parent 2 if provided)
        if (!empty($this->parent2_email_address) && !JMailHelper::isEmailAddress($this->parent2_email_address)) {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_PARENT2_EMAIL_INVALID'));
            return false;
        }

        // Check for valid IBAN
        if (trim($this->iban) == '') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_IBAN_EMPTY'));
            return false;
        }

        // Validate IBAN format and checksum
        if (!$this->validateDutchIban($this->iban) || !$this->validateIbanWithChecksum($this->iban)) {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_IBAN_INVALID'));
            return false;
        }

        // Health Information Validation
        
        // Check emergency contact information
        if (trim($this->emergency_contact_name) == '') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_EMERGENCY_CONTACT_NAME_EMPTY'));
            return false;
        }
        
        if (trim($this->emergency_contact_relation) == '') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_EMERGENCY_CONTACT_RELATION_EMPTY'));
            return false;
        }
        
        if (trim($this->emergency_contact_phone) == '') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_EMERGENCY_CONTACT_PHONE_EMPTY'));
            return false;
        }

        // Validate health-related required fields
        if (trim($this->health_insurance) == '') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_HEALTH_INSURANCE_EMPTY'));
            return false;
        }
        
        if (trim($this->policy_number) == '') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_POLICY_NUMBER_EMPTY'));
            return false;
        }
        
        if (trim($this->gp_name) == '') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_GP_NAME_EMPTY'));
            return false;
        }
        
        if (trim($this->gp_address) == '') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_GP_ADDRESS_EMPTY'));
            return false;
        }
        
        if (trim($this->gp_phone) == '') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_GP_PHONE_EMPTY'));
            return false;
        }

        // Validate conditional fields based on Yes/No responses
        
        // If can_swim is Yes, swim_diplomas should have content
        if ($this->can_swim === 'Yes' && trim($this->swim_diplomas) == '') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_SWIM_DIPLOMAS_REQUIRED'));
            return false;
        }
        
        // If special_health_care is Yes, details should be provided
        if ($this->special_health_care === 'Yes' && trim($this->special_health_care_details) == '') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_SPECIAL_HEALTH_CARE_DETAILS_REQUIRED'));
            return false;
        }
        
        // If medication is Yes, details should be provided
        if ($this->medication === 'Yes' && trim($this->medication_details) == '') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_MEDICATION_DETAILS_REQUIRED'));
            return false;
        }
        
        // If allergies is Yes, details should be provided
        if ($this->allergies === 'Yes' && trim($this->allergies_details) == '') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_ALLERGIES_DETAILS_REQUIRED'));
            return false;
        }
        
        // If diet is Yes, details should be provided
        if ($this->diet === 'Yes' && trim($this->diet_details) == '') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_DIET_DETAILS_REQUIRED'));
            return false;
        }

        // Emergency treatment consent validation
        if ($this->emergency_treatment_consent !== 'Yes') {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_EMERGENCY_CONSENT_REQUIRED'));
            return false;
        }

        // Validate enum values for health fields
        $validYesNo = array('Yes', 'No');
        $validScoutSections = array('welpen', 'scouts', 'explorers', 'stam', 'sikas', 'plus');
        
        if (!in_array($this->can_swim, $validYesNo)) {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_INVALID_CAN_SWIM'));
            return false;
        }
        
        if (!in_array($this->special_health_care, $validYesNo)) {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_INVALID_SPECIAL_HEALTH_CARE'));
            return false;
        }
        
        if (!in_array($this->medication, $validYesNo)) {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_INVALID_MEDICATION'));
            return false;
        }
        
        if (!in_array($this->allergies, $validYesNo)) {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_INVALID_ALLERGIES'));
            return false;
        }
        
        if (!in_array($this->diet, $validYesNo)) {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_INVALID_DIET'));
            return false;
        }
        
        if (!in_array($this->scout_section, $validScoutSections)) {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_INVALID_SCOUT_SECTION'));
            return false;
        }
        
        if (!in_array($this->images_website, $validYesNo)) {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_INVALID_IMAGES_WEBSITE'));
            return false;
        }
        
        if (!in_array($this->images_social, $validYesNo)) {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_INVALID_IMAGES_SOCIAL'));
            return false;
        }
        
        if (!in_array($this->images_newspaper, $validYesNo)) {
            $this->setError(JText::_('COM_BATENSTEINFORM_ERROR_INVALID_IMAGES_NEWSPAPER'));
            return false;
        }

        return true;
    }

    /**
     * Overloaded store function
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     */
    public function store($updateNulls = false)
    {
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        if ($this->id) {
            // Existing item
            $this->updated_at = $date->toSql();
        } else {
            // New item
            if (!(int) $this->created_at) {
                $this->created_at = $date->toSql();
            }
        }

        return parent::store($updateNulls);
    }
}
