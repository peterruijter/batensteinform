<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_batensteinform
 *
 * @copyright   Copyright (C) 2025 Scouting Batenstein. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Log\Log;

/**
 * Batensteinform Form Controller - Fixed version
 */
class BatensteinFormControllerForm extends FormController
{
    /**
     * Method to save a record.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key.
     *
     * @return  boolean  True if successful, false otherwise.
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        $this->checkToken();        
        
        $app   = Factory::getApplication();
        $model = $this->getModel('Form');
        $data  = $this->input->post->get('jform', array(), 'array');

        // Check the full POST data structure
        $allPost = $this->input->post->getArray();

        // Validate the posted data.
        $form = $model->getForm();
        if (!$form) {
            $app->enqueueMessage(Text::_('COM_BATENSTEINFORM_ERROR_FORM_LOAD'), 'error');
            Log::add('Failed to load form', Log::ERROR_LEVEL, 'com_batensteinform');
            return false;
        }
        
        // Additional server-side validation before model validation
        $validationErrors = $this->validateFormData($data);
        if (!empty($validationErrors)) {
            foreach ($validationErrors as $error) {
                $app->enqueueMessage($error, 'error');
            }
            
            // Save the data in the session.
            $app->setUserState('com_batensteinform.edit.form.data', $data);
            
            // Redirect back to the form.
            $this->setRedirect(Route::_('index.php?option=com_batensteinform&view=form', false));
            
            return false;
        }
        
        // Filter and validate the form data with Joomla's form validation
        $validData = $model->validate($form, $data);
        
        // Check for validation errors.
        if ($validData === false) {
            // Get the validation messages.
            $errors = $model->getErrors();
            
            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'error');
                } else {
                    $app->enqueueMessage($errors[$i], 'error');
                }
            }
            
            // Save the data in the session.
            $app->setUserState('com_batensteinform.edit.form.data', $data);
            
            // Redirect back to the form.
            $this->setRedirect(Route::_('index.php?option=com_batensteinform&view=form', false));
            
            return false;
        }
        
        // Attempt to save the data.
        if (!$model->save($validData)) {
            // Save failed, go back to the form and display a notice.
            $app->setUserState('com_batensteinform.edit.form.data', $validData);
            
            // Get model errors
            $modelErrors = $model->getErrors();
            if (!empty($modelErrors)) {
                foreach ($modelErrors as $error) {
                    if ($error instanceof Exception) {
                        $app->enqueueMessage($error->getMessage(), 'error');
                    } else {
                        $app->enqueueMessage($error, 'error');
                    }
                }
            } else {
                $app->enqueueMessage(Text::_('COM_BATENSTEINFORM_SAVE_FAILED'), 'error');
            }
            
            $this->setRedirect(Route::_('index.php?option=com_batensteinform&view=form', false));
            
            return false;
        }
        
        // Clear the data from the form.
        $app->setUserState('com_batensteinform.edit.form.data', null);
        
        // $app->enqueueMessage(Text::_('COM_BATENSTEINFORM_SAVE_SUCCESS'), 'success');
        $this->setRedirect(Route::_('index.php?option=com_batensteinform&view=confirmation', false));
        
        return true;
    }
    
    /**
     * Additional server-side validation for form data
     * This method validates business rules that can't be handled by the form XML
     *
     * @param   array  $data  The form data to validate
     *
     * @return  array  Array of error messages (empty if valid)
     */
    protected function validateFormData($data)
    {
        $errors = array();
        
        // Required field validation
        $requiredFields = array(
            'first_name' => 'COM_BATENSTEINFORM_ERROR_FIRST_NAME_REQUIRED',
            'calling_name' => 'COM_BATENSTEINFORM_ERROR_CALLING_NAME_REQUIRED', 
            'last_name' => 'COM_BATENSTEINFORM_ERROR_LAST_NAME_REQUIRED',
            'address' => 'COM_BATENSTEINFORM_ERROR_ADDRESS_REQUIRED',
            'postal_code_city' => 'COM_BATENSTEINFORM_ERROR_POSTAL_CODE_CITY_REQUIRED',
            'birth_date' => 'COM_BATENSTEINFORM_ERROR_BIRTH_DATE_REQUIRED',
            'birth_place' => 'COM_BATENSTEINFORM_ERROR_BIRTH_PLACE_REQUIRED',
            'scout_section' => 'COM_BATENSTEINFORM_ERROR_SCOUT_SECTION_REQUIRED',
            'emergency_contact_name' => 'COM_BATENSTEINFORM_ERROR_EMERGENCY_CONTACT_NAME_REQUIRED',
            'emergency_contact_relation' => 'COM_BATENSTEINFORM_ERROR_EMERGENCY_CONTACT_RELATION_REQUIRED',
            'emergency_contact_phone' => 'COM_BATENSTEINFORM_ERROR_EMERGENCY_CONTACT_PHONE_REQUIRED',
            'health_insurance' => 'COM_BATENSTEINFORM_ERROR_HEALTH_INSURANCE_REQUIRED',
            'policy_number' => 'COM_BATENSTEINFORM_ERROR_POLICY_NUMBER_REQUIRED',
            'gp_name' => 'COM_BATENSTEINFORM_ERROR_GP_NAME_REQUIRED',
            'gp_address' => 'COM_BATENSTEINFORM_ERROR_GP_ADDRESS_REQUIRED',
            'gp_phone' => 'COM_BATENSTEINFORM_ERROR_GP_PHONE_REQUIRED',
            'iban' => 'COM_BATENSTEINFORM_ERROR_IBAN_REQUIRED',
            'account_name' => 'COM_BATENSTEINFORM_ERROR_ACCOUNT_NAME_REQUIRED'
        );
        
        foreach ($requiredFields as $field => $errorMessage) {
            if (empty($data[$field]) || trim($data[$field]) === '') {
                $errors[] = Text::_($errorMessage);
            }
        }
        
        // Scout section dependent validation
        if (!empty($data['scout_section'])) {
            $scoutSection = $data['scout_section'];
            
            // Parent contact required for Welpen, Scouts, Explorers
            if (in_array($scoutSection, array('welpen', 'scouts', 'explorers'))) {
                $parentFields = array(
                    'parent1_name' => 'COM_BATENSTEINFORM_ERROR_PARENT1_NAME_REQUIRED',
                    'parent1_phone_number' => 'COM_BATENSTEINFORM_ERROR_PARENT1_PHONE_REQUIRED',
                    'parent1_email_address' => 'COM_BATENSTEINFORM_ERROR_PARENT1_EMAIL_REQUIRED'
                );
                
                foreach ($parentFields as $field => $errorMessage) {
                    if (empty($data[$field]) || trim($data[$field]) === '') {
                        $errors[] = Text::_($errorMessage);
                    }
                }
            }
            
            // Personal contact validation for non-Welpen
            if ($scoutSection !== 'welpen') {
                if (empty($data['email_address']) || trim($data['email_address']) === '') {
                    $errors[] = Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_REQUIRED');
                }
                
                // Phone required for older scouts
                if (in_array($scoutSection, array('explorers', 'stam', 'sikas', 'plus'))) {
                    if (empty($data['phone_number']) || trim($data['phone_number']) === '') {
                        $errors[] = Text::_('COM_BATENSTEINFORM_ERROR_PHONE_REQUIRED');
                    }
                }
            }
        }
        
        // Email validation
        if (!empty($data['email_address']) && !filter_var($data['email_address'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_INVALID');
        }
        
        if (!empty($data['parent1_email_address']) && !filter_var($data['parent1_email_address'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = Text::_('COM_BATENSTEINFORM_ERROR_PARENT1_EMAIL_INVALID');
        }
        
        if (!empty($data['parent2_email_address']) && !filter_var($data['parent2_email_address'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = Text::_('COM_BATENSTEINFORM_ERROR_PARENT2_EMAIL_INVALID');
        }
        
        // IBAN validation
        if (!empty($data['iban'])) {
            if (!$this->validateDutchIban($data['iban']) || !$this->validateIbanWithChecksum($data['iban'])) {
                $errors[] = Text::_('COM_BATENSTEINFORM_ERROR_IBAN_INVALID');
            }
        }
        
        // Emergency consent validation
        if (empty($data['emergency_treatment_consent']) || $data['emergency_treatment_consent'] !== 'Yes') {
            $errors[] = Text::_('COM_BATENSTEINFORM_ERROR_EMERGENCY_CONSENT_REQUIRED');
        }
        
        // Conditional field validation
        if (!empty($data['can_swim']) && $data['can_swim'] === 'Yes') {
            if (empty($data['swim_diplomas']) || trim($data['swim_diplomas']) === '') {
                $errors[] = Text::_('COM_BATENSTEINFORM_ERROR_SWIM_DIPLOMAS_REQUIRED');
            }
        }
        
        if (!empty($data['special_health_care']) && $data['special_health_care'] === 'Yes') {
            if (empty($data['special_health_care_details']) || trim($data['special_health_care_details']) === '') {
                $errors[] = Text::_('COM_BATENSTEINFORM_ERROR_SPECIAL_HEALTH_CARE_DETAILS_REQUIRED');
            }
        }
        
        if (!empty($data['medication']) && $data['medication'] === 'Yes') {
            if (empty($data['medication_details']) || trim($data['medication_details']) === '') {
                $errors[] = Text::_('COM_BATENSTEINFORM_ERROR_MEDICATION_DETAILS_REQUIRED');
            }
        }
        
        if (!empty($data['allergies']) && $data['allergies'] === 'Yes') {
            if (empty($data['allergies_details']) || trim($data['allergies_details']) === '') {
                $errors[] = Text::_('COM_BATENSTEINFORM_ERROR_ALLERGIES_DETAILS_REQUIRED');
            }
        }
        
        if (!empty($data['diet']) && $data['diet'] === 'Yes') {
            if (empty($data['diet_details']) || trim($data['diet_details']) === '') {
                $errors[] = Text::_('COM_BATENSTEINFORM_ERROR_DIET_DETAILS_REQUIRED');
            }
        }
        
        return $errors;
    }
    
    /**
     * Validates Dutch IBAN format
     *
     * @param   string  $iban  The IBAN number to validate
     *
     * @return  boolean  True if valid Dutch IBAN format, false otherwise
     */
    protected function validateDutchIban($iban)
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
     * @param   string  $iban  The IBAN number to validate
     *
     * @return  boolean  True if valid IBAN with correct checksum, false otherwise
     */
    protected function validateIbanWithChecksum($iban)
    {
        // First check the format
        if (!$this->validateDutchIban($iban)) {
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
     * Method to cancel an edit.
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     *
     * @return  boolean  True if access level checks pass, false otherwise.
     */
    public function cancel($key = null)
    {
        $app = Factory::getApplication();
        
        // Clear the form data.
        $app->setUserState('com_batensteinform.edit.form.data', null);
        
        // Redirect to the main page.
        $this->setRedirect(Route::_('index.php', false));
        
        return true;
    }
}