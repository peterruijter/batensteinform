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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', '.advancedSelect');

$document = Factory::getDocument();
$app = Factory::getApplication();
$input = $app->input;

// Get current page from URL parameter, default to 1
$currentPage = $input->getInt('page', 1);
$totalPages = 5;
$formId = 'batenstein-form';
$formStorageKey = 'batenstein_form_data';

$document->addScriptDeclaration('
    // Configuration variables
    var currentPage = ' . $currentPage . ';
    var totalPages = ' . $totalPages . ';
    var FORM_STORAGE_KEY = "' . $formStorageKey . '";

    // Centralized field labels object - single source of truth
    var FIELD_LABELS = {
        "first_name": "' . Text::_('COM_BATENSTEINFORM_FIRST_NAMES_LABEL') . '",
        "calling_name": "' . Text::_('COM_BATENSTEINFORM_CALLING_NAME_LABEL') . '",
        "name_prefix": "' . Text::_('COM_BATENSTEINFORM_NAME_PREFIX_LABEL') . '",
        "last_name": "' . Text::_('COM_BATENSTEINFORM_LAST_NAME_LABEL') . '",
        "address": "' . Text::_('COM_BATENSTEINFORM_ADDRESS_LABEL') . '",
        "postal_code_city": "' . Text::_('COM_BATENSTEINFORM_POSTAL_CODE_CITY_LABEL') . '",
        "birth_date": "' . Text::_('COM_BATENSTEINFORM_BIRTH_DATE_LABEL') . '",
        "birth_place": "' . Text::_('COM_BATENSTEINFORM_BIRTH_PLACE_LABEL') . '",
        "scout_section": "' . Text::_('COM_BATENSTEINFORM_SCOUT_SECTION_LABEL') . '",
        "phone_number": "' . Text::_('COM_BATENSTEINFORM_PHONE_NUMBER_LABEL') . '",
        "email_address": "' . Text::_('COM_BATENSTEINFORM_EMAIL_ADDRESS_LABEL') . '",
        "parent1_name": "' . Text::_('COM_BATENSTEINFORM_PARENT1_NAME_LABEL') . '",
        "parent1_phone_number": "' . Text::_('COM_BATENSTEINFORM_PARENT1_PHONE_NUMBER_LABEL') . '",
        "parent1_email_address": "' . Text::_('COM_BATENSTEINFORM_PARENT1_EMAIL_ADDRESS_LABEL') . '",
        "parent2_name": "' . Text::_('COM_BATENSTEINFORM_PARENT2_NAME_LABEL') . '",
        "parent2_phone_number": "' . Text::_('COM_BATENSTEINFORM_PARENT2_PHONE_NUMBER_LABEL') . '",
        "parent2_email_address": "' . Text::_('COM_BATENSTEINFORM_PARENT2_EMAIL_ADDRESS_LABEL') . '",
        "images_website": "' . Text::_('COM_BATENSTEINFORM_IMAGES_WEBSITE_LABEL') . '",
        "images_social": "' . Text::_('COM_BATENSTEINFORM_IMAGES_SOCIAL_LABEL') . '",
        "images_newspaper": "' . Text::_('COM_BATENSTEINFORM_IMAGES_NEWSPAPER_LABEL') . '",
        "can_swim": "' . Text::_('COM_BATENSTEINFORM_CAN_SWIM_LABEL') . '",
        "swim_diplomas": "' . Text::_('COM_BATENSTEINFORM_SWIM_DIPLOMAS_LABEL') . '",
        "emergency_contact_name": "' . Text::_('COM_BATENSTEINFORM_EMERGENCY_CONTACT_NAME_LABEL') . '",
        "emergency_contact_relation": "' . Text::_('COM_BATENSTEINFORM_EMERGENCY_CONTACT_RELATION_LABEL') . '",
        "emergency_contact_phone": "' . Text::_('COM_BATENSTEINFORM_EMERGENCY_CONTACT_PHONE_LABEL') . '",
        "special_health_care": "' . Text::_('COM_BATENSTEINFORM_SPECIAL_HEALTH_CARE_LABEL') . '",
        "special_health_care_details": "' . Text::_('COM_BATENSTEINFORM_SPECIAL_HEALTH_CARE_DETAILS_LABEL') . '",
        "medication": "' . Text::_('COM_BATENSTEINFORM_MEDICATION_LABEL') . '",
        "medication_details": "' . Text::_('COM_BATENSTEINFORM_MEDICATION_DETAILS_LABEL') . '",
        "allergies": "' . Text::_('COM_BATENSTEINFORM_ALLERGIES_LABEL') . '",
        "allergies_details": "' . Text::_('COM_BATENSTEINFORM_ALLERGIES_DETAILS_LABEL') . '",
        "diet": "' . Text::_('COM_BATENSTEINFORM_DIET_LABEL') . '",
        "diet_details": "' . Text::_('COM_BATENSTEINFORM_DIET_DETAILS_LABEL') . '",
        "health_insurance": "' . Text::_('COM_BATENSTEINFORM_HEALTH_INSURANCE_LABEL') . '",
        "policy_number": "' . Text::_('COM_BATENSTEINFORM_POLICY_NUMBER_LABEL') . '",
        "gp_name": "' . Text::_('COM_BATENSTEINFORM_GP_NAME_LABEL') . '",
        "gp_address": "' . Text::_('COM_BATENSTEINFORM_GP_ADDRESS_LABEL') . '",
        "gp_phone": "' . Text::_('COM_BATENSTEINFORM_GP_PHONE_LABEL') . '",
        "dentist_name": "' . Text::_('COM_BATENSTEINFORM_DENTIST_NAME_LABEL') . '",
        "dentist_address": "' . Text::_('COM_BATENSTEINFORM_DENTIST_ADDRESS_LABEL') . '",
        "dentist_phone": "' . Text::_('COM_BATENSTEINFORM_DENTIST_PHONE_LABEL') . '",
        "emergency_treatment_consent": "' . Text::_('COM_BATENSTEINFORM_EMERGENCY_TREATMENT_CONSENT_LABEL') . '",
        "iban": "' . Text::_('COM_BATENSTEINFORM_IBAN_LABEL') . '",
        "account_name": "' . Text::_('COM_BATENSTEINFORM_ACCOUNT_NAME_LABEL') . '",
        "sign_date": "' . Text::_('COM_BATENSTEINFORM_SIGN_DATE_LABEL') . '",
        "comments": "' . Text::_('COM_BATENSTEINFORM_COMMENTS_LABEL') . '"
    };

    // Translation values object
    var TRANSLATIONS = {
        "Yes": "' . Text::_('COM_BATENSTEINFORM_YES') . '",
        "No": "' . Text::_('COM_BATENSTEINFORM_NO') . '",
        "welpen": "' . Text::_('COM_BATENSTEINFORM_WELPEN') . '",
        "scouts": "' . Text::_('COM_BATENSTEINFORM_SCOUTS') . '",
        "explorers": "' . Text::_('COM_BATENSTEINFORM_EXPLORERS') . '",
        "stam": "' . Text::_('COM_BATENSTEINFORM_STAM') . '",
        "sikas": "' . Text::_('COM_BATENSTEINFORM_SIKAS') . '",
        "plus": "' . Text::_('COM_BATENSTEINFORM_PLUS_SCOUTS') . '"
    };

    // Load validation translations
    var VALIDATION_TRANSLATIONS = {
        "COM_BATENSTEINFORM_ERROR_PHONE_REQUIRED": "' . Text::_('COM_BATENSTEINFORM_ERROR_PHONE_REQUIRED') . '",
        "COM_BATENSTEINFORM_ERROR_PHONE_INVALID_CHARS": "' . Text::_('COM_BATENSTEINFORM_ERROR_PHONE_INVALID_CHARS') . '",
        "COM_BATENSTEINFORM_ERROR_PHONE_INVALID_FORMAT": "' . Text::_('COM_BATENSTEINFORM_ERROR_PHONE_INVALID_FORMAT') . '",
        "COM_BATENSTEINFORM_PHONE_LABEL": "' . Text::_('COM_BATENSTEINFORM_PHONE_LABEL') . '",
        "COM_BATENSTEINFORM_ERROR_EMAIL_REQUIRED": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_REQUIRED') . '",
        "COM_BATENSTEINFORM_ERROR_EMAIL_SINGLE_AT": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_SINGLE_AT') . '",
        "COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_EMPTY": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_EMPTY') . '",
        "COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_TOO_LONG": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_TOO_LONG') . '",
        "COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_INVALID_CHARS": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_INVALID_CHARS') . '",
        "COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_DOT_POSITION": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_DOT_POSITION') . '",
        "COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_CONSECUTIVE_DOTS": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_CONSECUTIVE_DOTS') . '",
        "COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_EMPTY": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_EMPTY') . '",
        "COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_TOO_LONG": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_TOO_LONG') . '",
        "COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_NO_DOT": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_NO_DOT') . '",
        "COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_INVALID_POSITION": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_INVALID_POSITION') . '",
        "COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_EMPTY_LABEL": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_EMPTY_LABEL') . '",
        "COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_LABEL_TOO_LONG": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_LABEL_TOO_LONG') . '",
        "COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_INVALID_CHARS": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_INVALID_CHARS') . '",
        "COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_LABEL_HYPHEN": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_LABEL_HYPHEN') . '",
        "COM_BATENSTEINFORM_ERROR_EMAIL_TLD_INVALID": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_TLD_INVALID') . '",
        "COM_BATENSTEINFORM_ERROR_EMAIL_TYPO_SUGGESTION": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_TYPO_SUGGESTION') . '",
        "COM_BATENSTEINFORM_EMAIL_LABEL": "' . Text::_('COM_BATENSTEINFORM_EMAIL_LABEL') . '",
        "COM_BATENSTEINFORM_FORM_REQUIRED_FIELDS": "' . Text::_('COM_BATENSTEINFORM_FORM_REQUIRED_FIELDS') . '",
        "COM_BATENSTEINFORM_ERROR_EMERGENCY_CONSENT": "' . Text::_('COM_BATENSTEINFORM_ERROR_EMERGENCY_CONSENT') . '",
        "COM_BATENSTEINFORM_IBAN_ERROR_INVALID_FORMAT": "' . Text::_('COM_BATENSTEINFORM_IBAN_ERROR_INVALID_FORMAT') . '"
    };

    // Make translations globally available
    window.VALIDATION_TRANSLATIONS = VALIDATION_TRANSLATIONS;

    // Navigation URLs for page switching
    var NEXT_PAGE_URL = "' . Route::_('index.php?option=com_batensteinform&view=form') . '";
    var CANCEL_URL = "' . Route::_('index.php?option=com_batensteinform&task=form.cancel') . '";
');

$document->addScriptDeclaration('
    var currentPage = ' . $currentPage . ';
    var totalPages = ' . $totalPages . ';

    // Global storage key for the form data
    var FORM_STORAGE_KEY = "' . $formStorageKey . '";

    // Centralized field labels object - single source of truth
    var FIELD_LABELS = {
        "first_name": "' . Text::_('COM_BATENSTEINFORM_FIRST_NAMES_LABEL') . '",
        "calling_name": "' . Text::_('COM_BATENSTEINFORM_CALLING_NAME_LABEL') . '",
        "name_prefix": "' . Text::_('COM_BATENSTEINFORM_NAME_PREFIX_LABEL') . '",
        "last_name": "' . Text::_('COM_BATENSTEINFORM_LAST_NAME_LABEL') . '",
        "address": "' . Text::_('COM_BATENSTEINFORM_ADDRESS_LABEL') . '",
        "postal_code_city": "' . Text::_('COM_BATENSTEINFORM_POSTAL_CODE_CITY_LABEL') . '",
        "birth_date": "' . Text::_('COM_BATENSTEINFORM_BIRTH_DATE_LABEL') . '",
        "birth_place": "' . Text::_('COM_BATENSTEINFORM_BIRTH_PLACE_LABEL') . '",
        "scout_section": "' . Text::_('COM_BATENSTEINFORM_SCOUT_SECTION_LABEL') . '",
        "phone_number": "' . Text::_('COM_BATENSTEINFORM_PHONE_NUMBER_LABEL') . '",
        "email_address": "' . Text::_('COM_BATENSTEINFORM_EMAIL_ADDRESS_LABEL') . '",
        "parent1_name": "' . Text::_('COM_BATENSTEINFORM_PARENT1_NAME_LABEL') . '",
        "parent1_phone_number": "' . Text::_('COM_BATENSTEINFORM_PARENT1_PHONE_NUMBER_LABEL') . '",
        "parent1_email_address": "' . Text::_('COM_BATENSTEINFORM_PARENT1_EMAIL_ADDRESS_LABEL') . '",
        "parent2_name": "' . Text::_('COM_BATENSTEINFORM_PARENT2_NAME_LABEL') . '",
        "parent2_phone_number": "' . Text::_('COM_BATENSTEINFORM_PARENT2_PHONE_NUMBER_LABEL') . '",
        "parent2_email_address": "' . Text::_('COM_BATENSTEINFORM_PARENT2_EMAIL_ADDRESS_LABEL') . '",
        "images_website": "' . Text::_('COM_BATENSTEINFORM_IMAGES_WEBSITE_LABEL') . '",
        "images_social": "' . Text::_('COM_BATENSTEINFORM_IMAGES_SOCIAL_LABEL') . '",
        "images_newspaper": "' . Text::_('COM_BATENSTEINFORM_IMAGES_NEWSPAPER_LABEL') . '",
        "can_swim": "' . Text::_('COM_BATENSTEINFORM_CAN_SWIM_LABEL') . '",
        "swim_diplomas": "' . Text::_('COM_BATENSTEINFORM_SWIM_DIPLOMAS_LABEL') . '",
        "emergency_contact_name": "' . Text::_('COM_BATENSTEINFORM_EMERGENCY_CONTACT_NAME_LABEL') . '",
        "emergency_contact_relation": "' . Text::_('COM_BATENSTEINFORM_EMERGENCY_CONTACT_RELATION_LABEL') . '",
        "emergency_contact_phone": "' . Text::_('COM_BATENSTEINFORM_EMERGENCY_CONTACT_PHONE_LABEL') . '",
        "special_health_care": "' . Text::_('COM_BATENSTEINFORM_SPECIAL_HEALTH_CARE_LABEL') . '",
        "special_health_care_details": "' . Text::_('COM_BATENSTEINFORM_SPECIAL_HEALTH_CARE_DETAILS_LABEL') . '",
        "medication": "' . Text::_('COM_BATENSTEINFORM_MEDICATION_LABEL') . '",
        "medication_details": "' . Text::_('COM_BATENSTEINFORM_MEDICATION_DETAILS_LABEL') . '",
        "allergies": "' . Text::_('COM_BATENSTEINFORM_ALLERGIES_LABEL') . '",
        "allergies_details": "' . Text::_('COM_BATENSTEINFORM_ALLERGIES_DETAILS_LABEL') . '",
        "diet": "' . Text::_('COM_BATENSTEINFORM_DIET_LABEL') . '",
        "diet_details": "' . Text::_('COM_BATENSTEINFORM_DIET_DETAILS_LABEL') . '",
        "health_insurance": "' . Text::_('COM_BATENSTEINFORM_HEALTH_INSURANCE_LABEL') . '",
        "policy_number": "' . Text::_('COM_BATENSTEINFORM_POLICY_NUMBER_LABEL') . '",
        "gp_name": "' . Text::_('COM_BATENSTEINFORM_GP_NAME_LABEL') . '",
        "gp_address": "' . Text::_('COM_BATENSTEINFORM_GP_ADDRESS_LABEL') . '",
        "gp_phone": "' . Text::_('COM_BATENSTEINFORM_GP_PHONE_LABEL') . '",
        "dentist_name": "' . Text::_('COM_BATENSTEINFORM_DENTIST_NAME_LABEL') . '",
        "dentist_address": "' . Text::_('COM_BATENSTEINFORM_DENTIST_ADDRESS_LABEL') . '",
        "dentist_phone": "' . Text::_('COM_BATENSTEINFORM_DENTIST_PHONE_LABEL') . '",
        "emergency_treatment_consent": "' . Text::_('COM_BATENSTEINFORM_EMERGENCY_TREATMENT_CONSENT_LABEL') . '",
        "iban": "' . Text::_('COM_BATENSTEINFORM_IBAN_LABEL') . '",
        "account_name": "' . Text::_('COM_BATENSTEINFORM_ACCOUNT_NAME_LABEL') . '",
        "sign_date": "' . Text::_('COM_BATENSTEINFORM_SIGN_DATE_LABEL') . '",
        "comments": "' . Text::_('COM_BATENSTEINFORM_COMMENTS_LABEL') . '"
    };

    // Translation values object
    var TRANSLATIONS = {
        "Yes": "' . Text::_('COM_BATENSTEINFORM_YES') . '",
        "No": "' . Text::_('COM_BATENSTEINFORM_NO') . '",
        "welpen": "' . Text::_('COM_BATENSTEINFORM_WELPEN') . '",
        "scouts": "' . Text::_('COM_BATENSTEINFORM_SCOUTS') . '",
        "explorers": "' . Text::_('COM_BATENSTEINFORM_EXPLORERS') . '",
        "stam": "' . Text::_('COM_BATENSTEINFORM_STAM') . '",
        "sikas": "' . Text::_('COM_BATENSTEINFORM_SIKAS') . '",
        "plus": "' . Text::_('COM_BATENSTEINFORM_PLUS_SCOUTS') . '"
    };

    // Global variable to store cached form data to minimize sessionStorage access
    var cachedFormData = null;
    var cacheTimestamp = 0;
    const CACHE_DURATION = 1000; // Cache for 1 second to prevent excessive sessionStorage calls

    /**
     * Centralized function to get form data from sessionStorage with caching
     * Reduces repeated sessionStorage access and improves performance
     * @return {Object|null} Parsed form data or null if no data
     */
    function getFormDataFromStorage() {
        var currentTime = Date.now();
        
        // Return cached data if still valid
        if (cachedFormData && (currentTime - cacheTimestamp) < CACHE_DURATION) {
            return cachedFormData;
        }
        
        try {
            var storedData = sessionStorage.getItem(FORM_STORAGE_KEY);
            if (storedData) {
                cachedFormData = JSON.parse(storedData);
                cacheTimestamp = currentTime;
                return cachedFormData;
            }
        } catch (e) {
            console.warn(\'Could not retrieve form data from sessionStorage:\', e);
        }
        
        cachedFormData = null;
        return null;
    }

    /**
     * Invalidate the cache when form data changes
     */
    function invalidateFormDataCache() {
        cachedFormData = null;
        cacheTimestamp = 0;
    }

    /**
     * Enhanced function to get field value from both DOM and sessionStorage
     * Prioritizes current DOM values over stored values
     * @param {string} fieldName - The field name to retrieve
     * @param {boolean} isRadio - Whether it\'s a radio button field
     * @return {string} Field value or empty string
     */
    function getFieldValue(fieldName, isRadio = false) {
        // First try to get value from current DOM (current page)
        var form = document.getElementById(\'batenstein-form\');
        if (form) {
            var selector = isRadio 
                ? `input[name="jform[${fieldName}]"]:checked`
                : `[name="jform[${fieldName}]"]`;
            
            var field = form.querySelector(selector);
            if (field && field.value) {
                return field.value;
            }
        }
        
        // If not found in DOM, try sessionStorage
        var formData = getFormDataFromStorage();
        if (!formData) return "";
        
        var fieldKey = `jform[${fieldName}]`;
        return formData[fieldKey] || "";
    }

    /**
     * Get field label from centralized FIELD_LABELS object
     * @param {string} fieldName - The field name
     * @return {string} Human-readable field label
     */
    function getFieldLabel(fieldName) {
        return FIELD_LABELS[fieldName] || fieldName;
    }

    /**
     * Function to add all stored form data as hidden fields before submission
     * This ensures all data from previous pages is included in the final submit
     */
    function addStoredDataToForm() {
        var form = document.getElementById(\'batenstein-form\');
        if (!form) {
            console.error(\'Form not found\');
            return false;
        }
        
        // Get all stored form data
        var formData = getFormDataFromStorage();
        if (!formData) {
            console.warn(\'No stored form data found\');
            return true; // Continue with submit even if no stored data
        }
        
        // Remove any existing hidden fields we might have added before
        var existingHiddenFields = form.querySelectorAll(\'input[data-stored-field="true"]\');
        existingHiddenFields.forEach(function(field) {
            field.remove();
        });
        
        // Add hidden fields for each stored data item
        Object.keys(formData).forEach(function(fieldName) {
            var value = formData[fieldName];
            
            // Skip empty values and system fields
            if (!value || fieldName === \'option\' || fieldName === \'task\' || fieldName === \'page\' || fieldName.includes(\'token\')) {
                return;
            }
            
            // Check if field already exists in current form (to avoid duplicates)
            var existingField = form.querySelector(\'[name="\' + fieldName + \'"]\');
            if (existingField && existingField.value) {
                return; // Field exists and has value, don\'t override
            }
            
            // Create hidden input field
            var hiddenField = document.createElement(\'input\');
            hiddenField.type = \'hidden\';
            hiddenField.name = fieldName;
            hiddenField.value = value;
            hiddenField.setAttribute(\'data-stored-field\', \'true\');
            
            form.appendChild(hiddenField);
        });
        
        console.log(\'Added stored form data to submission\');
        return true;
    }

function debugFormData() {
    var form = document.getElementById(\'batenstein-form\');
    if (!form) {
        console.error(\'Form not found\');
        return;
    }
    
    console.log(\'=== FORM SUBMISSION DEBUG ===\');
    
    // Get sessionStorage data
    var storedData = getFormDataFromStorage();
    console.log(\'Stored data in sessionStorage:\', storedData);
    
    // Get current form data
    var formData = new FormData(form);
    var currentFormData = {};
    
    for (let [key, value] of formData.entries()) {
        currentFormData[key] = value;
    }
    
    console.log(\'Current form data (will be submitted):\', currentFormData);
    
    // Check for missing fields
    if (storedData) {
        var missingFields = [];
        Object.keys(storedData).forEach(function(key) {
            if (!currentFormData[key] && storedData[key]) {
                missingFields.push(key);
            }
        });
        
        if (missingFields.length > 0) {
            console.warn(\'Fields in storage but not in current form:\', missingFields);
        }
    }
    
    console.log(\'=== END DEBUG ===\');
}

    /**
     * Enhanced Joomla.submitbutton function with sessionStorage data integration
     */
    var originalSubmitbutton = Joomla.submitbutton;
    Joomla.submitbutton = function(task) {
        if (task == "form.cancel") {
            Joomla.submitform(task, document.getElementById(\'batenstein-form\'));
            clearStoredFormData();
            return;
        }
        
        if (task == "form.nextPage") {
            if (validateCurrentPage()) {
                nextPage();
            }
            return;
        }
        
        if (task == "form.previousPage") {
            previousPage();
            return;
        }
        
        if (task == "form.save") {
            // First save current page data
            saveFormData();
            debugFormData();
            // Validate form
            if (document.formvalidator.isValid(document.getElementById(\'batenstein-form\'))) {
                // Add all stored data to form before submission
                if (addStoredDataToForm()) {
                    // Submit form
                    Joomla.submitform(task, document.getElementById(\'batenstein-form\'));
                
                    // Clear stored data after successful submission
                    clearStoredFormData();
                }
            }
            return;
        }
        
        // Fallback to original function for other tasks
        if (originalSubmitbutton) {
            originalSubmitbutton(task);
        }
    };

    /**
     * Validates Dutch IBAN format
     * Accepts format: NL46 ASNB 0708 4337 23 (with or without spaces)
     *
     * @param {string} iban - The IBAN number to validate
     * @return {boolean} True if valid IBAN format, false otherwise
     */
    function validateDutchIban(iban) {
        const cleanIban = iban.replace(/\s/g, \'\');
        if (!/^[A-Z]{2}$/.test(cleanIban.toUpperCase().substring(0, 2))) return false;
        if (cleanIban.length !== 18) return false;
        if (!/^\d{2}$/.test(cleanIban.substring(2, 4))) return false;
        if (!/^[A-Za-z]{4}$/.test(cleanIban.substring(4, 8))) return false;
        if (!/^\d{10}$/.test(cleanIban.substring(8, 18))) return false;
        
        return true;
    }

    /**
     * Enhanced IBAN validation with mod-97 checksum verification
     *
     * @param {string} iban - The IBAN number to validate
     * @return {boolean} True if valid IBAN with correct checksum, false otherwise
     */
    function validateIbanWithChecksum(iban) {
        // First check the format
        if (!validateDutchIban(iban)) return false;
        
        const cleanIban = iban.toUpperCase().replace(/\s/g, \'\');
        const rearranged = cleanIban.substring(4) + cleanIban.substring(0, 4);
        
        // Replace letters with numbers (A=10, B=11, ..., Z=35)
        let numeric = \'\';
        for (let i = 0; i < rearranged.length; i++) {
            const char = rearranged[i];
            if (/[A-Z]/.test(char)) {
                // Convert letter to number
                numeric += (char.charCodeAt(0) - \'A\'.charCodeAt(0) + 10).toString();
            } else {
                numeric += char;
            }
        }
        
        // Calculate mod 97 for large numbers
        let remainder = 0;
        for (let i = 0; i < numeric.length; i++) {
            remainder = (remainder * 10 + parseInt(numeric[i])) % 97;
        }
        
        // Valid IBAN should have remainder of 1
        return remainder === 1;
    }
    
    // Global variable to store the timeout ID for debouncing
    var birthdateValidationTimeout;

    /**
     * Debounced birthdate validation to prevent excessive calls while user is typing
     */
    function debouncedBirthdateValidation() {
        // Clear any existing timeout
        if (birthdateValidationTimeout) {
            clearTimeout(birthdateValidationTimeout);
        }
        
        // Set a new timeout to call validation after user stops typing/changing
        birthdateValidationTimeout = setTimeout(function() {
            validateBirthdateAndSection();
        }, 3000); // Wait 3 seconds after user stops typing
    }

    /**
    * Debounced auto-selection for birthdate changes
    * Only triggers auto-selection, not validation
    */
    function debouncedAutoSelection() {
        if (birthdateValidationTimeout) {
            clearTimeout(birthdateValidationTimeout);
        }
        
        birthdateValidationTimeout = setTimeout(function() {
            var birthDateValue = jQuery("#jform_birth_date").val();
            var age = calculateAge(birthDateValue);
            if (age !== null) {
                autoSelectScoutSection(age);
            }
        }, 500); // Shorter delay for auto-selection
    }

    /**
     * Calculates age based on reaching minimum age within the current year
     * A scout section is suitable for persons that will become the minimal age 
     * of that section within the current year.
     * 
     * @param {string} birthDateValue - The birthdate value from form
     * @return {number|null} Age the person will reach this year, or null if invalid date
     */
    function calculateAge(birthDateValue) {
        if (!birthDateValue) return null;
        
        var birthDate;
        
        // Check regex for American date notation (yyyy-mm-dd) and European date notation (dd-mm-yyyy)
        var americanNotation = /^\d{4}-\d{2}-\d{2}$/;
        var europeanNotation = /^\d{2}-\d{2}-\d{4}$/;
        
        if (birthDateValue.includes("-") && (americanNotation.test(birthDateValue) || europeanNotation.test(birthDateValue))) {
            var parts = birthDateValue.split("-");
            if (parts[0].length === 4) {
                // Format: yyyy-mm-dd (American notation)
                birthDate = new Date(parts[0], parts[1] - 1, parts[2]);
            } else {
                // Format: dd-mm-yyyy (European notation)
                birthDate = new Date(parts[2], parts[1] - 1, parts[0]);
            }
        } else {
            return null; // Invalid format
        }
        
        // Check if date is valid
        if (isNaN(birthDate.getTime())) return null;
        
        // Calculate age based on reaching minimum age within current year
        // This means we only look at the year difference, not the exact birth date
        var today = new Date();
        var currentYear = today.getFullYear();
        var birthYear = birthDate.getFullYear();
        
        // Age the person will reach (or has reached) this year
        var ageThisYear = currentYear - birthYear;
        
        return ageThisYear;
    }

    /**
     * Automatically selects the correct scout section based on age
     * Only selects if age fits within defined ranges, otherwise does nothing
     * @param {number} age - The calculated age of the person
     */
    function autoSelectScoutSection(age) {
        if (age === null || age < 0) return;
        
        var scoutSections = document.getElementsByName("jform[scout_section]");
        var selectedValue = "";
        
        // Determine appropriate section based on age
        if (age >= 7 && age < 11) {
            selectedValue = "welpen";
        } else if (age >= 11 && age < 15) {
            selectedValue = "scouts";
        } else if (age >= 15 && age < 18) {
            selectedValue = "explorers";
        } else if (age >= 18) {
            selectedValue = "stam";
        }
        
        // Only proceed if we have a valid selection for this age
        if (selectedValue) {
            // First, uncheck all radio buttons
            for (var i = 0; i < scoutSections.length; i++) {
                scoutSections[i].checked = false;
            }
            
            // Find and check the correct radio button
            for (var i = 0; i < scoutSections.length; i++) {
                if (scoutSections[i].value === selectedValue) {
                    scoutSections[i].checked = true;
                    // Trigger change event to update form visibility
                    jQuery(scoutSections[i]).trigger("change");
                    break;
                }
            }
        }
        // If no valid selection can be made (age < 7), do nothing
    }


    /**
     * Validates if the selected scout section matches the birthdate/age
     * Shows warning if there\'s a mismatch
     * @return {boolean} True if valid or no validation needed, false if invalid
     */
    function validateBirthdateAndSection() {
        var birthDateValue = jQuery("#jform_birth_date").val();
        var scoutSectionValue = jQuery("input[name=\'jform[scout_section]\']:checked").val();
        
        // If no birthdate or no section selected, no validation needed
        if (!birthDateValue || !scoutSectionValue) return true;
        
        var age = calculateAge(birthDateValue);
        if (age === null) return true; // Invalid date, let other validation handle it
        
        var alertPrefix = "De ingevulde geboortedatum past bij een persoon die dit jaar " + age + " jaar oud wordt. Dit past niet bij de gekozen scoutingafdeling.\\n\\n";
        
        // Validate age against selected section
        if (scoutSectionValue === "welpen" && (age < 7 || age >= 11)) {
            alert(alertPrefix + "Welpen is voor kinderen die tussen de 7 en 10 jaar oud zijn.");
            return false;
        }
        
        if (scoutSectionValue === "scouts" && (age < 11 || age >= 15)) {
            alert(alertPrefix + "Scouts is voor jongeren tussen 11 en 14 jaar oud.");
            return false;
        }
        
        if (scoutSectionValue === "explorers" && (age < 15 || age >= 18)) {
            alert(alertPrefix + "Explorers is voor jongeren tussen 15 en 17 jaar oud.");
            return false;
        }
        
        if ((scoutSectionValue === "stam" || scoutSectionValue === "plus" || scoutSectionValue === "sikas") && age < 18) {
            alert(alertPrefix + "Leden van de BaVianenstam, Plus en Sika\'s moeten 18 jaar of ouder zijn.");
            return false;
        }
        
        return true;
    }

    /**
     * Automatically selects the correct scout section based on age
     */
    function autoSelectScoutSection(age) {
        var scoutSections = document.getElementsByName("jform[scout_section]");
        
        // First, uncheck all radio buttons
        for (var i = 0; i < scoutSections.length; i++) {
            scoutSections[i].checked = false;
        }
        
        // Select the appropriate section based on age
        var selectedValue = "";
        
        if (age >= 7 && age < 11) {
            selectedValue = "welpen";
        } else if (age >= 11 && age < 15) {
            selectedValue = "scouts";
        } else if (age >= 15 && age < 18) {
            selectedValue = "explorers";
        } else if (age >= 18) {
            // Default to "stam" for adults (18+)
            selectedValue = "stam";
        }
        
        // Find and check the correct radio button
        if (selectedValue) {
            for (var i = 0; i < scoutSections.length; i++) {
                if (scoutSections[i].value === selectedValue) {
                    scoutSections[i].checked = true;
                    // Trigger change event to update form visibility
                    jQuery(scoutSections[i]).trigger("change");
                    break;
                }
            }
        }
    }

    /**
     * Validates emergency treatment consent
     * Shows appropriate alert message based on the consent value
     * @return {boolean} True if consent is given (Yes), false otherwise
     */
    function validateEmergencyConsent() {
        var consent = document.querySelector("[name=\"jform[emergency_treatment_consent]\\"]:checked");
        if (!consent || consent.value === "No") {
            confirm(`' . Text::_('COM_BATENSTEINFORM_ERROR_EMERGENCY_CONSENT') . '`);
            return false;
        }
        return true;
    }

    /**
     * Validates Dutch phone numbers with comprehensive format support
     * Accepts mobile (06), landline, and international formats
     * 
     * @param {string} phoneNumber - The phone number to validate
     * @return {object} Validation result with isValid boolean and formatted number
     */
    function validateDutchPhoneNumber(phoneNumber) {
        // Return object structure
        const result = {
            isValid: false,
            formatted: \'\',
            type: \'\', // \'mobile\', \'landline\', or \'international\'
            errors: []
        };
        
        // Check if phone number is provided
        if (!phoneNumber || typeof phoneNumber !== \'string\') {
            result.errors.push(\'Telefoonnummer is verplicht\');
            return result;
        }
        
        // Clean the input: remove all spaces, dashes, dots, and parentheses
        const cleanNumber = phoneNumber.replace(/[\s\-\.\(\)]/g, \'\');
        
        // Check if only contains valid characters (digits and + at start)
        if (!/^[\+]?[0-9]+$/.test(cleanNumber)) {
            result.errors.push(\'Telefoonnummer mag alleen cijfers, spaties, streepjes en + bevatten\');
            return result;
        }
        
        // Dutch mobile number patterns
        const mobilePatterns = [
            /^06[0-9]{8}$/,           // 0612345678
            /^\+316[0-9]{8}$/,        // +31612345678
            /^00316[0-9]{8}$/         // 0031612345678
        ];
        
        // Dutch landline patterns (area codes: 010, 013, 015, 020, 023, 024, 026, 030, 033, 035, 036, 038, 040, 043, 045, 046, 050, 053, 055, 058, 070, 071, 072, 073, 074, 075, 076, 077, 078, 079)
        const landlinePatterns = [
            /^0(10|13|15|20|23|24|26|30|33|35|36|38|40|43|45|46|50|53|55|58|70|71|72|73|74|75|76|77|78|79)[0-9]{7}$/,  // 0101234567
            /^\+31(10|13|15|20|23|24|26|30|33|35|36|38|40|43|45|46|50|53|55|58|70|71|72|73|74|75|76|77|78|79)[0-9]{7}$/, // +31101234567
            /^0031(10|13|15|20|23|24|26|30|33|35|36|38|40|43|45|46|50|53|55|58|70|71|72|73|74|75|76|77|78|79)[0-9]{7}$/ // 003110234567
        ];
        
        // Check mobile patterns
        for (const pattern of mobilePatterns) {
            if (pattern.test(cleanNumber)) {
                result.isValid = true;
                result.type = \'mobile\';
                
                // Format as 06-12345678
                if (cleanNumber.startsWith(\'06\')) {
                    result.formatted = cleanNumber.substring(0, 2) + \'-\' + cleanNumber.substring(2);
                } else if (cleanNumber.startsWith(\'+316\')) {
                    const nationalNumber = \'0\' + cleanNumber.substring(3);
                    result.formatted = nationalNumber.substring(0, 2) + \'-\' + nationalNumber.substring(2);
                } else if (cleanNumber.startsWith(\'00316\')) {
                    const nationalNumber = \'0\' + cleanNumber.substring(4);
                    result.formatted = nationalNumber.substring(0, 2) + \'-\' + nationalNumber.substring(2);
                }
                
                return result;
            }
        }
        
        // Check landline patterns
        for (const pattern of landlinePatterns) {
            if (pattern.test(cleanNumber)) {
                result.isValid = true;
                result.type = \'landline\';
                
                // Format based on area code length
                if (cleanNumber.startsWith(\'0\')) {
                    // National format: 010-1234567 or 020-1234567
                    const areaCodeLength = cleanNumber.substring(1, 3) === \'20\' || 
                                        cleanNumber.substring(1, 3) === \'70\' || 
                                        cleanNumber.substring(1, 3) === \'30\' ? 3 : 3;
                    result.formatted = cleanNumber.substring(0, areaCodeLength) + \'-\' + cleanNumber.substring(areaCodeLength);
                } else if (cleanNumber.startsWith(\'+31\')) {
                    // Convert to national format first
                    const nationalNumber = \'0\' + cleanNumber.substring(3);
                    const areaCodeLength = nationalNumber.substring(1, 3) === \'20\' || 
                                        nationalNumber.substring(1, 3) === \'70\' || 
                                        nationalNumber.substring(1, 3) === \'30\' ? 3 : 3;
                    result.formatted = nationalNumber.substring(0, areaCodeLength) + \'-\' + nationalNumber.substring(areaCodeLength);
                } else if (cleanNumber.startsWith(\'0031\')) {
                    // Convert to national format first
                    const nationalNumber = \'0\' + cleanNumber.substring(4);
                    const areaCodeLength = nationalNumber.substring(1, 3) === \'20\' || 
                                        nationalNumber.substring(1, 3) === \'70\' || 
                                        nationalNumber.substring(1, 3) === \'30\' ? 3 : 3;
                    result.formatted = nationalNumber.substring(0, areaCodeLength) + \'-\' + nationalNumber.substring(areaCodeLength);
                }
                
                return result;
            }
        }
        
        // If no pattern matches, it\'s invalid
        result.errors.push(\'Ongeldig Nederlands telefoonnummer. Gebruik een geldig mobiel (06) of vast nummer.\');
        return result;
    }

    /**
     * Real-time phone number validation for form fields
     * Provides immediate feedback while user types
     * 
     * @param {HTMLElement} phoneField - The phone input field
     * @param {HTMLElement} errorContainer - Container for error messages (optional)
     */
    function setupPhoneValidation(phoneField, errorContainer = null) {
        if (!phoneField) return;
        
        // Create error container if not provided
        if (!errorContainer) {
            errorContainer = document.createElement(\'div\');
            errorContainer.className = \'phone-validation-error\';
            errorContainer.style.color = \'#d9534f\';
            errorContainer.style.fontSize = \'0.875em\';
            errorContainer.style.marginTop = \'5px\';
            phoneField.parentNode.appendChild(errorContainer);
        }
        
        // Validation timeout for debouncing
        let validationTimeout;
        
        /**
         * Perform validation with visual feedback
         */
        function performValidation() {
            const phoneValue = phoneField.value.trim();
            
            // Clear previous styling
            phoneField.classList.remove(\'valid-phone\', \'invalid-phone\');
            errorContainer.textContent = \'\';
            
            if (phoneValue === \'\') {
                // Empty field - remove validation styling
                return;
            }
            
            const validation = validateDutchPhoneNumber(phoneValue);
            
            if (validation.isValid) {
                // Valid phone number
                phoneField.classList.add(\'valid-phone\');
                phoneField.classList.remove(\'invalid\');
                
                // Optionally update field with formatted number
                if (validation.formatted !== phoneValue) {
                    phoneField.value = validation.formatted;
                }
                
                // Show success message
                errorContainer.innerHTML = \'<span style="color: #5cb85c;">✓ Geldig telefoonnummer</span>\';
            } else {
                // Invalid phone number
                phoneField.classList.add(\'invalid-phone\');
                phoneField.classList.add(\'invalid\');
                
                // Show error message
                errorContainer.textContent = validation.errors.join(\', \');
            }
        }
        
        /**
         * Debounced validation function
         */
        function debouncedValidation() {
            clearTimeout(validationTimeout);
            validationTimeout = setTimeout(performValidation, 800); // Wait 800ms after user stops typing
        }
        
        // Add event listeners
        phoneField.addEventListener(\'input\', debouncedValidation);
        phoneField.addEventListener(\'blur\', performValidation); // Immediate validation on blur
        phoneField.addEventListener(\'paste\', function() {
            // Validate after paste with short delay
            setTimeout(performValidation, 100);
        });
    }

    /**
     * Validates current page fields and requirements
     * @return {boolean} True if current page is valid, false otherwise
     */
    function validateCurrentPage() {
        var page = currentPage;
        var valid = true;
        var requiredFields = [];
        
        // Get required fields for current page
        if (page == 1) {
            var scoutSection = jQuery("input[name=\'jform[scout_section]\']:checked").val();
            var requiredFields = ["first_name", "calling_name", "last_name", "address", "postal_code_city", "birth_date", "birth_place", "scout_section"];
            
            // Parent fields required for Welpen, Scouts, Explorers
            if (scoutSection === "welpen" || scoutSection === "scouts" || scoutSection === "explorers") {
                requiredFields = requiredFields.concat(["parent1_name", "parent1_phone_number", "parent1_email_address"]);
            }
            
            // Personal contact fields for all except Welpen
            if (scoutSection !== "welpen") {
                // Email always required for non-Welpen
                requiredFields.push("email_address");
                
                // Phone required for Explorers, Stam, Sikas, Plus (not required for Scouts)
                if (scoutSection === "explorers" || scoutSection === "stam" || scoutSection === "sikas" || scoutSection === "plus") {
                    requiredFields.push("phone_number");
                }
            }
    
            // Validate birthdate and section combination on page 1
            if (!validateBirthdateAndSection()) {
                return false;
            }

        } else if (page == 2) {
            requiredFields = ["images_website", "images_social", "images_newspaper"];
        } else if (page == 3) {
            requiredFields = ["can_swim", "emergency_contact_name", "emergency_contact_relation", "emergency_contact_phone", "special_health_care", "medication", "allergies", "diet", "health_insurance", "policy_number", "gp_name", "gp_address", "gp_phone", "emergency_treatment_consent"];

            // Validate emergency consent on page 3  
            if (!validateEmergencyConsent()) {
                return false;
            }
        } else if (page == 4) {
            requiredFields = ["iban", "account_name"];

           // Validate IBAN format and checksum
            var ibanField = document.querySelector("[name=\"jform[iban]\"]");
            if (ibanField && 
                ibanField.value && 
                (!validateDutchIban(ibanField.value) || 
                !validateIbanWithChecksum(ibanField.value)
               )) {
                valid = false;
                alert(`' . Text::_('COM_BATENSTEINFORM_IBAN_ERROR_INVALID_FORMAT') . '`);
            }
        } 
        
        // Validate required fields
        for (var i = 0; i < requiredFields.length; i++) {
            var fieldName = requiredFields[i];
            var field = document.querySelector("[name=\"jform[" + fieldName + "]\"]");
            if (!field) {
                // Try radio buttons
                field = document.querySelector("[name=\"jform[" + fieldName + "]\\"]:checked");
            }
            
            if (!field || !field.value || field.value.trim() === "") {
                valid = false;
               // Translate field name to user-friendly label
               var fieldLabel = getFieldLabel(fieldName);

                alert("' . Text::_('COM_BATENSTEINFORM_FORM_REQUIRED_FIELDS') . ' " + fieldLabel);
                if (field) field.focus();
                break;
            }
        }
        
        return valid;
    }
    
    /**
     * Populates review content on final page
     * Uses centralized data retrieval and field labeling
     */
    function populateReviewContent() {
        const reviewContainer = document.getElementById("review-content");
        if (!reviewContainer) {
            console.error("Review container not found");
            return;
        }

        let reviewHtml = "";

        // Personal Information Section
        reviewHtml += generateReviewSection("' . Text::_('COM_BATENSTEINFORM_REVIEW_PERSONAL') . '", [
            "first_name", "calling_name", "name_prefix", "last_name", 
            "address", "postal_code_city", "birth_date", "birth_place", "scout_section"
        ]);

        // Personal Contact Details (if applicable)
        const scoutSection = getFieldValue("scout_section", true);
        if (scoutSection && scoutSection !== "welpen") {
            reviewHtml += generateReviewSection("' . Text::_('COM_BATENSTEINFORM_PERSONAL_CONTACT_LABEL') . '", [
                "phone_number", "email_address"
            ]);
        }

        // Parent/Guardian Contact Details (if applicable)
        if (scoutSection === "welpen" || scoutSection === "scouts" || scoutSection === "explorers") {
            reviewHtml += generateReviewSection("' . Text::_('COM_BATENSTEINFORM_PARENT1_LABEL') . '", [
                "parent1_name", "parent1_phone_number", "parent1_email_address"
            ]);

            const parent2Name = getFieldValue("parent2_name");
            if (parent2Name && parent2Name.trim() !== "") {
                reviewHtml += generateReviewSection("' . Text::_('COM_BATENSTEINFORM_PARENT2_LABEL') . '", [
                    "parent2_name", "parent2_phone_number", "parent2_email_address"
                ]);
            }
        }

        // Image/Video Permissions
        reviewHtml += generateReviewSection("' . Text::_('COM_BATENSTEINFORM_REVIEW_IMAGES') . '", [
            "images_website", "images_social", "images_newspaper"
        ]);

        // Health Information
        reviewHtml += generateReviewSection("' . Text::_('COM_BATENSTEINFORM_REVIEW_HEALTH') . '", [
            "can_swim", "swim_diplomas", "emergency_contact_name", "emergency_contact_relation", 
            "emergency_contact_phone", "special_health_care", "special_health_care_details",
            "medication", "medication_details", "allergies", "allergies_details", 
            "diet", "diet_details", "health_insurance", "policy_number", 
            "gp_name", "gp_address", "gp_phone", "dentist_name", "dentist_address", 
            "dentist_phone", "emergency_treatment_consent"
        ]);

        // Payment Information  
        reviewHtml += generateReviewSection("' . Text::_('COM_BATENSTEINFORM_REVIEW_PAYMENT') . '", [
            "iban", "account_name", "sign_date"
        ]);

        // Comments (if filled)
        const comments = getFieldValue("comments");
        if (comments && comments.trim() !== "") {
            reviewHtml += generateReviewSection("' . Text::_('COM_BATENSTEINFORM_REVIEW_COMMENTS') . '", [
                "comments"
            ]);
        }

        reviewContainer.innerHTML = reviewHtml;
    }

    /**
     * Generates HTML for a review section
     * Uses centralized field labels and translations
     * @param {string} sectionTitle - The section title
     * @param {Array} fields - Array of field names to include
     * @return {string} Generated HTML string
     */
    function generateReviewSection(sectionTitle, fields) {
        let sectionHtml = `<div class="review-section">
            <h3 class="review-section-title">${sectionTitle}</h3>
            <div class="review-fields">`;

        fields.forEach(fieldName => {
            const value = getFieldValue(fieldName, ["scout_section", "images_website", "images_social", "images_newspaper", 
                                                "can_swim", "special_health_care", "medication", "allergies", 
                                                "diet", "emergency_treatment_consent"].includes(fieldName));
            
            // Handle conditional fields
            if ((fieldName === "swim_diplomas" && getFieldValue("can_swim", true) !== "Yes") ||
                (fieldName === "special_health_care_details" && getFieldValue("special_health_care", true) !== "Yes") ||
                (fieldName === "medication_details" && getFieldValue("medication", true) !== "Yes") ||
                (fieldName === "allergies_details" && getFieldValue("allergies", true) !== "Yes") ||
                (fieldName === "diet_details" && getFieldValue("diet", true) !== "Yes")) {
                return;
            }

            if (value && value.trim() !== "") {
                const label = getFieldLabel(fieldName);
                let displayValue = value;

                // Translate values using centralized TRANSLATIONS object
                if (TRANSLATIONS[value]) {
                    displayValue = TRANSLATIONS[value];
                }

                // Format textarea content
                if (fieldName.includes("details") || fieldName === "comments" || fieldName.includes("address")) {
                    displayValue = displayValue.replace(/\\n/g, "<br>");
                }

                const specialClass = fieldName === "emergency_treatment_consent" && value === "No" ? " style=\"color: #d9534f; font-weight: bold;\"" : "";

                sectionHtml += `<div class="review-field" data-field="${fieldName}">
                    <span class="review-field-label">${label}:</span>
                    <span class="review-field-value"${specialClass}>${displayValue}</span>
                </div>`;
            }
        });

        sectionHtml += `</div></div>`;
        return sectionHtml;
    }

    /**
     * Saves all form data to sessionStorage
     */
    function saveFormData() {
        var existingData = {};
        var form = document.getElementById("' . $formId . '");

        if (!form) return;
        
        // First, load existing data from sessionStorage
        try {
            var storedData = sessionStorage.getItem(FORM_STORAGE_KEY);
            if (storedData) {
                existingData = JSON.parse(storedData);
            }
        } catch (e) {
            console.warn(\'Could not load existing form data from sessionStorage:\', e);
            existingData = {};
        }
        
        // Get all form elements from current page
        var elements = form.querySelectorAll(\'input, select, textarea\');
        
        elements.forEach(function(element) {
            var name = element.name;
            if (!name) return;
            
            if (element.type === \'radio\' || element.type === \'checkbox\') {
                if (element.checked) {
                    existingData[name] = element.value;
                }
            } else {
                // Only save non-empty values or explicitly preserve empty values
                if (element.value !== \'\' || existingData[name] !== undefined) {
                    existingData[name] = element.value;
                }
            }
        });
        
        // Save merged data back to sessionStorage
        try {
            sessionStorage.setItem(FORM_STORAGE_KEY, JSON.stringify(existingData));
            invalidateFormDataCache(); 
        } catch (e) {
            console.warn(\'Could not save form data to sessionStorage:\', e);
        }
    }

    /**
     * Restores form data from sessionStorage
     */
    function restoreFormData() {
        try {
            var formData = getFormDataFromStorage();
            if (!formData) return;
            
            var form = document.getElementById("' . $formId . '");  

            // Restore each field
            Object.keys(formData).forEach(function(fieldName) {
                var value = formData[fieldName];
                if (!value) return;
                
                // Try to find the field
                var field = form.querySelector(\'[name="\' + fieldName + \'"]\');
                
                if (field) {
                    if (field.type === \'radio\') {
                        // For radio buttons, find the one with matching value
                        var radioButton = form.querySelector(\'[name="\' + fieldName + \'"][value="\' + value + \'"]\');
                        if (radioButton) {
                            radioButton.checked = true;
                            // Trigger change event to update form visibility
                            jQuery(radioButton).trigger(\'change\');
                        }
                    } else if (field.type === \'checkbox\') {
                        field.checked = (value === field.value);
                    } else {
                        // Text inputs, textareas, selects
                        field.value = value;
                    }
                }
            });
            
            // Trigger events to update conditional field visibility
            setTimeout(function() {
                // Trigger scout section change to update visibility
                var scoutSectionField = form.querySelector(\'input[name="jform[scout_section]"]:checked\');
                if (scoutSectionField) {
                    jQuery(scoutSectionField).trigger(\'change\');
                }
                
                // Trigger health form conditional fields
                jQuery(\'input[name="jform[can_swim]"]:checked\').trigger(\'change\');
                jQuery(\'input[name="jform[special_health_care]"]:checked\').trigger(\'change\');
                jQuery(\'input[name="jform[medication]"]:checked\').trigger(\'change\');
                jQuery(\'input[name="jform[allergies]"]:checked\').trigger(\'change\');
                jQuery(\'input[name="jform[diet]"]:checked\').trigger(\'change\');
            }, 100);
            
        } catch (e) {
            console.warn(\'Could not restore form data from sessionStorage:\', e);
        }
    }

    /**
     * Auto-save form data periodically and on field changes
     */
    function initializeAutoSave() {
        var autoSaveTimeout;
        
        function triggerAutoSave() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(function() {
                saveFormData();
            }, 1000); // Auto-save after 1 second of inactivity
        }
        
        // Save on form field changes
        jQuery(document).on(\'change input\', \'#batenstein-form input, #batenstein-form textarea, #batenstein-form select\', function() {
            triggerAutoSave();
        });
        
        // Save before page unload
        window.addEventListener(\'beforeunload\', function() {
            saveFormData();
        });
    }
    
    /**
     * Integration with Joomla\'s form validation system
     * Extends the existing validation to include phone number checks
     */
    function integrateBatensteinPhoneValidation() {
        // Override the existing validateCurrentPage function to include phone validation
        const originalValidateCurrentPage = window.validateCurrentPage;
        
        window.validateCurrentPage = function() {
            // First run the original validation
            let isValid = originalValidateCurrentPage ? originalValidateCurrentPage() : true;
            
            // Then add phone number validation for current page
            const currentPagePhoneFields = document.querySelectorAll(
                \'input[name*="phone"]:not([type="hidden"]), input[name*="Phone"]:not([type="hidden"])\'
            );
            
            currentPagePhoneFields.forEach(field => {
                const phoneValue = field.value.trim();
                
                // Skip validation if field is empty and not required
                if (phoneValue === \'\') {
                    return;
                }
                
                const validation = validateDutchPhoneNumber(phoneValue);
                
                if (!validation.isValid) {
                    isValid = false;
                    
                    // Get field label for error message
                    const fieldLabel = getFieldLabel(field.name.replace(\'jform[\', \'\').replace(\']\', \'\')) || \'Telefoonnummer\';
                    
                    alert(`${fieldLabel}: ${validation.errors.join(\', \')}`);
                    field.focus();
                    return false; // Stop at first invalid field
                }
            });
            
            return isValid;
        };
    }

    /**
     * Batch setup for multiple phone fields in the Batenstein form
     * Automatically finds and sets up validation for all phone fields
     */
    function initializeBatensteinPhoneValidation() {
        // Phone field selectors for the Batenstein form
        const phoneFieldSelectors = [
            \'input[name="jform[phone_number]"]\',
            \'input[name="jform[parent1_phone_number]"]\', 
            \'input[name="jform[parent2_phone_number]"]\',
            \'input[name="jform[emergency_contact_phone]"]\',
            \'input[name="jform[gp_phone]"]\',
            \'input[name="jform[dentist_phone]"]\'
        ];
        
        phoneFieldSelectors.forEach(selector => {
            const field = document.querySelector(selector);
            if (field) {
                setupPhoneValidation(field);
            }
        });
    }

    /**
     * Clears stored form data
     */
    function clearStoredFormData() {
        try {
            sessionStorage.removeItem(FORM_STORAGE_KEY);
            invalidateFormDataCache();
        } catch (e) {
            console.warn(\'Could not clear form data from sessionStorage:\', e);
        }
    }

    function nextPage() {
        // Save current page data before moving to the next page
        saveFormData();

        if (currentPage < totalPages) {
            window.location.href = NEXT_PAGE_URL + "&page=" + (currentPage + 1);
        }
    }
    
    function previousPage() {
        // Save current page data before moving to the previous page    
        saveFormData();
    
        if (currentPage > 1) {
            window.location.href = NEXT_PAGE_URL + "&page=" + (currentPage - 1);
        }
    }

    // In your jQuery ready function, replace the birthdate event listener with:
    jQuery(document).ready(function($) {
        // Initialize phone validation
        initializeBatensteinPhoneValidation();
      
        // Integrate with form validation
        integrateBatensteinPhoneValidation();

        // Auto-select scout section when birthdate changes
        $("#jform_birth_date").on("input change", function() {
            debouncedAutoSelection();
        });

        // Restore form data when page loads
        restoreFormData();
        
        // Initialize auto-save functionality
        initializeAutoSave();
        
        // Auto-select immediately when user leaves the birthdate field
        $("#jform_birth_date").on("blur", function() {
            if (birthdateValidationTimeout) {
                clearTimeout(birthdateValidationTimeout);
            }
            var birthDateValue = $(this).val();
            var age = calculateAge(birthDateValue);
            if (age !== null) {
                autoSelectScoutSection(age);
            }
        });
        
        // Validate when scout section is manually changed (clicked)
        $("input[name=\'jform[scout_section]\']").on("click", function() {
            // Small delay to ensure the change event completes
            setTimeout(function() {
                validateBirthdateAndSection();
            }, 100);
            
            // Also trigger your existing form visibility functions
            togglePersonalContact();
            toggleParentContact();
        });
        
        // Initial auto-selection if birthdate is already filled
        var initialBirthdate = $("#jform_birth_date").val();
        if (initialBirthdate) {
            var initialAge = calculateAge(initialBirthdate);
            if (initialAge !== null) {
                autoSelectScoutSection(initialAge);
            }
        }

        // Personal contact fields visibility
        togglePersonalContact();
        toggleParentContact();
        
        $("input[name=\'jform[scout_section]\']").change(function() {
            togglePersonalContact();
            toggleParentContact();
        });
        
        // Initial validation if birthdate is already filled
        if ($("#jform_birth_date").val()) {
            validateBirthdateAndSection();
        }

        function togglePersonalContact() {
            var scoutSection = $("input[name=\'jform[scout_section]\']:checked").val();
            if (scoutSection === "scouts" || scoutSection === "explorers" || scoutSection === "stam" || scoutSection === "sikas" || scoutSection === "plus") {
                $("#personal_contact").show();
                $("#personal_contact_note").show();
            } else {
                $("#personal_contact").hide();
                $("#personal_contact_note").hide();
            }

            var star = \'<span class="star" aria-hidden="true">&nbsp;*</span>\';     

            // Add or remove * from email address label
            if ($("#jform_email_address-lbl").length > 0) {              
                if (scoutSection === "scouts" || scoutSection === "explorers" || scoutSection === "stam" || scoutSection === "sikas" || scoutSection === "plus") {
                    // Add * to label if it\s not already there
                    $("#jform_email_address-lbl").html($("#jform_email_address-lbl").html().replace(star, \'\'));
                    $("#jform_email_address-lbl").html($("#jform_email_address-lbl").html() + star);
                } else {
                    // Remove * from label
                    $("#jform_email_address-lbl").html($("#jform_email_address-lbl").html().replace(star, \'\'));
                }
            }
            // Add or remove * from phone number label
            if ($("#jform_phone_number-lbl").length > 0) {              
                if (scoutSection === "explorers" || scoutSection === "stam" || scoutSection === "sikas" || scoutSection === "plus") {
                    $("#jform_phone_number-lbl").html($("#jform_phone_number-lbl").html().replace(star, \'\'));
                    $("#jform_phone_number-lbl").html($("#jform_phone_number-lbl").html() + star);
                } else {
                    // Remove * from label
                    $("#jform_phone_number-lbl").html($("#jform_phone_number-lbl").html().replace(star, \'\'));
                }
            }
        }

        function toggleParentContact() {
            var scoutSection = $("input[name=\'jform[scout_section]\']:checked").val();
            if (scoutSection === "welpen" || scoutSection === "scouts" || scoutSection === "explorers") {
                $("#parent1_fieldset").show();
                $("#parent2_fieldset").show();
            } else {
                $("#parent1_fieldset").hide();
                $("#parent2_fieldset").hide();
            }
        }
        
        // Health form conditional fields
        $("input[name=\'jform[can_swim]\']").change(function() {
            if ($(this).val() === "Yes") {
                $("#swim_diplomas_container").show();
            } else {
                $("#swim_diplomas_container").hide();
            }
        });
        
        $("input[name=\'jform[special_health_care]\']").change(function() {
            if ($(this).val() === "Yes") {
                $("#special_health_care_details_container").show();
            } else {
                $("#special_health_care_details_container").hide();
            }
        });
        
        $("input[name=\'jform[medication]\']").change(function() {
            if ($(this).val() === "Yes") {
                $("#medication_details_container").show();
            } else {
                $("#medication_details_container").hide();
            }
        });
        
        $("input[name=\'jform[allergies]\']").change(function() {
            if ($(this).val() === "Yes") {
                $("#allergies_details_container").show();
            } else {
                $("#allergies_details_container").hide();
            }
        });
        
        $("input[name=\'jform[diet]\']").change(function() {
            if ($(this).val() === "Yes") {
                $("#diet_details_container").show();
            } else {
                $("#diet_details_container").hide();
            }
        });
        
        // Initial state
        $("input[name=\'jform[can_swim]\']:checked").trigger("change");
        $("input[name=\'jform[special_health_care]\']:checked").trigger("change");
        $("input[name=\'jform[medication]\']:checked").trigger("change");
        $("input[name=\'jform[allergies]\']:checked").trigger("change");
        $("input[name=\'jform[diet]\']:checked").trigger("change");

        // if Page 5 is reached, populate the review content
        if (currentPage === 5) {
            setTimeout(function() {
                populateReviewContent();
            }, 100);
        }
    });
');
?>

<div class="<?php echo $formId; ?><?php echo $this->pageclass_sfx; ?>">
    <div class="page-header">
        <h1>
            <?php echo Text::_('COM_BATENSTEINFORM_FORM_TITLE'); ?>
        </h1>
        <p class="lead"><?php echo Text::_('COM_BATENSTEINFORM_FORM_INTRO'); ?></p>

        <!-- Page indicator -->
        <div class="progress-indicator">
            <span><?php echo Text::sprintf('COM_BATENSTEINFORM_PAGE_OF', $currentPage, $totalPages); ?></span>
            <div class="progress">
                <div class="progress-bar" style="width: <?php echo ($currentPage / $totalPages) * 100; ?>%"></div>
            </div>
        </div>
    </div>

    <form id="<?php echo $formId; ?>" action="<?php echo Route::_('index.php?option=com_batensteinform&task=form.save'); ?>"
        method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

        <div class="batenstein-form-container">

            <?php if ($currentPage == 1) : ?>
                <!-- Page 1: Personal Information & Contact Details -->
                <h2><?php echo Text::_('COM_BATENSTEINFORM_PAGE1_TITLE'); ?></h2>

                <!-- Personal Information -->
                <fieldset>
                    <legend><?php echo Text::_('COM_BATENSTEINFORM_PERSONAL_LABEL'); ?></legend>
                    <?php foreach ($this->form->getFieldset('personal') as $field) : ?>
                        <div class="control-group">
                            <div class="control-label">
                                <?php echo $field->label; ?>
                            </div>
                            <div class="controls">
                                <?php echo $field->input; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

                <!-- Personal Contact Details (for Scouts and Explorers) -->
                <div id="personal_contact_note" class="alert alert-info">
                    <?php echo Text::_('COM_BATENSTEINFORM_PERSONAL_CONTACT_INTRO'); ?>
                </div>

                <fieldset id="personal_contact">
                    <legend><?php echo Text::_('COM_BATENSTEINFORM_PERSONAL_CONTACT_LABEL'); ?></legend>
                    <?php foreach ($this->form->getFieldset('personal_contact') as $field) : ?>
                        <div class="control-group">
                            <div class="control-label">
                                <?php echo $field->label; ?>
                            </div>
                            <div class="controls">
                                <?php echo $field->input; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

                <!-- Parent/Guardian 1 Contact Details -->
                <fieldset id="parent1_fieldset">
                    <legend><?php echo Text::_('COM_BATENSTEINFORM_PARENT1_LABEL'); ?></legend>
                    <?php foreach ($this->form->getFieldset('parent1') as $field) : ?>
                        <div class="control-group">
                            <div class="control-label">
                                <?php echo $field->label; ?>
                            </div>
                            <div class="controls">
                                <?php echo $field->input; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

                <!-- Parent/Guardian 2 Contact Details -->
                <fieldset id="parent2_fieldset">
                    <legend><?php echo Text::_('COM_BATENSTEINFORM_PARENT2_LABEL'); ?></legend>
                    <?php foreach ($this->form->getFieldset('parent2') as $field) : ?>
                        <div class="control-group">
                            <div class="control-label">
                                <?php echo $field->label; ?>
                            </div>
                            <div class="controls">
                                <?php echo $field->input; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

            <?php elseif ($currentPage == 2) : ?>
                <!-- Page 2: Photo/Video Material -->
                <h2><?php echo Text::_('COM_BATENSTEINFORM_PAGE2_TITLE'); ?></h2>

                <fieldset>
                    <legend><?php echo Text::_('COM_BATENSTEINFORM_IMAGES_LABEL'); ?></legend>
                    <div class="alert alert-info">
                        <?php echo Text::_('COM_BATENSTEINFORM_IMAGES_INTRO'); ?>
                    </div>
                    <?php foreach ($this->form->getFieldset('images') as $field) : ?>
                        <div class="control-group">
                            <div class="control-label">
                                <?php echo $field->label; ?>
                            </div>
                            <div class="controls">
                                <?php echo $field->input; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

            <?php elseif ($currentPage == 3) : ?>
                <!-- Page 3: Health Information -->
                <h2><?php echo Text::_('COM_BATENSTEINFORM_PAGE3_TITLE'); ?></h2>


                <div class="alert alert-info">
                    <?php echo Text::_('COM_BATENSTEINFORM_HEALTH_INTRO'); ?>
                </div>

                <!-- Swimming Ability -->
                <fieldset>
                    <legend><?php echo Text::_('COM_BATENSTEINFORM_HEALTH_SWIMMING_LABEL'); ?></legend>
                    <?php foreach ($this->form->getFieldset('health_swimming') as $field) : ?>
                        <div class="control-group" <?php if ($field->fieldname === 'swim_diplomas') echo 'id="' . $field->fieldname . '_container" style="display:none;"'; ?>>
                            <div class="control-label">
                                <?php echo $field->label; ?>
                            </div>
                            <div class="controls">
                                <?php echo $field->input; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

                <!-- Emergency Contact -->
                <fieldset>
                    <legend><?php echo Text::_('COM_BATENSTEINFORM_HEALTH_EMERGENCY_LABEL'); ?></legend>
                    <div class="alert alert-info">
                        <?php echo Text::_('COM_BATENSTEINFORM_EMERGENCY_CONTACT_NAME_INRO'); ?>
                    </div>
                    <?php foreach ($this->form->getFieldset('health_emergency') as $field) : ?>
                        <div class="control-group">
                            <div class="control-label">
                                <?php echo $field->label; ?>
                            </div>
                            <div class="controls">
                                <?php echo $field->input; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

                <!-- Medical Information -->
                <fieldset>
                    <legend><?php echo Text::_('COM_BATENSTEINFORM_HEALTH_MEDICAL_LABEL'); ?></legend>
                    <?php foreach ($this->form->getFieldset('health_medical') as $field) : ?>
                        <div class="control-group" <?php if (in_array($field->fieldname, ['special_health_care_details', 'medication_details'])) echo 'id="' . $field->fieldname . '_container" style="display:none;"'; ?>>
                            <div class="control-label">
                                <?php echo $field->label; ?>
                            </div>
                            <div class="controls">
                                <?php echo $field->input; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

                <!-- Allergies & Diet -->
                <fieldset>
                    <legend><?php echo Text::_('COM_BATENSTEINFORM_HEALTH_ALLERGIES_LABEL'); ?></legend>
                    <?php foreach ($this->form->getFieldset('health_allergies') as $field) : ?>
                        <div class="control-group" <?php if (in_array($field->fieldname, ['allergies_details', 'diet_details'])) echo 'id="' . $field->fieldname . '_container" style="display:none;"'; ?>>
                            <div class="control-label">
                                <?php echo $field->label; ?>
                            </div>
                            <div class="controls">
                                <?php echo $field->input; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

                <!-- Insurance -->
                <fieldset>
                    <legend><?php echo Text::_('COM_BATENSTEINFORM_HEALTH_INSURANCE_LABEL'); ?></legend>
                    <?php foreach ($this->form->getFieldset('insurance') as $field) : ?>
                        <div class="control-group">
                            <div class="control-label">
                                <?php echo $field->label; ?>
                            </div>
                            <div class="controls">
                                <?php echo $field->input; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

                <!-- Doctors -->
                <fieldset>
                    <legend><?php echo Text::_('COM_BATENSTEINFORM_DOCTORS_LABEL'); ?></legend>
                    <?php foreach ($this->form->getFieldset('doctors') as $field) : ?>
                        <div class="control-group">
                            <div class="control-label">
                                <?php echo $field->label; ?>
                            </div>
                            <div class="controls">
                                <?php echo $field->input; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
                <!-- Emergency treatment consent -->
                <fieldset>
                    <legend><?php echo Text::_('COM_BATENSTEINFORM_EMERGENCY_TREATMENT_LABEL'); ?></legend>
                    <div class="alert alert-info">
                        <?php echo Text::_('COM_BATENSTEINFORM_EMERGENCY_TREATMENT_CONSENT_DESC'); ?>
                    </div>
                    <?php foreach ($this->form->getFieldset('emergency_treatment_consent') as $field) : ?>
                        <div class="control-group">
                            <div class="control-label">
                                <?php echo $field->label; ?>
                            </div>
                            <div class="controls">
                                <?php echo $field->input; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

            <?php elseif ($currentPage == 4) : ?>
                <!-- Page 4: Payment Details -->
                <h2><?php echo Text::_('COM_BATENSTEINFORM_PAGE4_TITLE'); ?></h2>

                <fieldset>
                    <legend><?php echo Text::_('COM_BATENSTEINFORM_PAYMENT_LABEL'); ?></legend>
                    <div class="alert alert-info">
                        <?php echo Text::_('COM_BATENSTEINFORM_PAYMENT_INTRO'); ?>
                    </div>
                    <?php foreach ($this->form->getFieldset('payment') as $field) : ?>
                        <div class="control-group">
                            <div class="control-label">
                                <?php echo $field->label; ?>
                            </div>
                            <div class="controls">
                                <?php echo $field->input; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

            <?php elseif ($currentPage == 5) : ?>
                <!-- Page 5: Review & Submit -->
                <h2><?php echo Text::_('COM_BATENSTEINFORM_PAGE5_TITLE'); ?></h2>

                <div class="alert alert-info">
                    <?php echo Text::_('COM_BATENSTEINFORM_REVIEW_INTRO'); ?>
                </div>

                <!-- Review sections will be populated by JavaScript -->
                <div id="review-content">
                    <!-- This will be filled with form data for review -->
                </div>

                <!-- Comments -->
                <fieldset>
                    <?php foreach ($this->form->getFieldset('comments') as $field) : ?>
                        <div class="control-group">
                            <div class="control-label">
                                <?php echo $field->label; ?>
                            </div>
                            <div class="controls">
                                <?php echo $field->input; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

            <?php endif; ?>
        </div>

        <!-- Navigation buttons -->
        <div class="btn-toolbar">
            <?php if ($currentPage > 1) : ?>
                <div class="btn-group">
                    <button type="button" class="btn" onclick="Joomla.submitbutton('form.previousPage')">
                        <span class="icon-arrow-left"></span>
                        <?php echo Text::_('COM_BATENSTEINFORM_PREVIOUS_PAGE'); ?>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($currentPage < $totalPages) : ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('form.nextPage')">
                        <?php echo Text::_('COM_BATENSTEINFORM_NEXT_PAGE'); ?>
                        <span class="icon-arrow-right"></span>
                    </button>
                </div>
            <?php else : ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-success" onclick="Joomla.submitbutton('form.save')">
                        <span class="icon-ok"></span>
                        <?php echo Text::_('JSUBMIT'); ?>
                    </button>
                </div>
            <?php endif; ?>

            <div class="btn-group">
                <a class="btn" href="<?php echo Route::_('index.php?option=com_batensteinform&task=form.cancel'); ?>">
                    <span class="icon-cancel"></span>
                    <?php echo Text::_('JCANCEL'); ?>
                </a>
            </div>
        </div>

        <input type="hidden" name="option" value="com_batensteinform" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="page" value="<?php echo $currentPage; ?>" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>

    <?php if ($currentPage == $totalPages) : ?>
        <div class="alert alert-info privacy-notice">
            <?php echo Text::_('COM_BATENSTEINFORM_PRIVACY_NOTICE'); ?>
        </div>
    <?php endif; ?>
</div>