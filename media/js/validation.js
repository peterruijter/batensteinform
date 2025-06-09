/**
 * @package     Joomla.Site
 * @subpackage  com_batensteinform
 *
 * @copyright   Copyright (C) 2025 Scouting Batenstein. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Form validation and functionality script for Batenstein Scout Form
// Compatible with Joomla 5.3.1 - No jQuery dependencies

// Global variables for caching
var cachedFormData = null;
var cacheTimestamp = 0;
var birthdateValidationTimeout;

/**
 * Helper function to get language strings from the global TRANSLATIONS object
 * Falls back to English text if translation is not available
 * 
 * @param {string} key - The language key to retrieve
 * @return {string} Translated text or fallback English text
 */
function getLanguageString(key) {
    // Access global TRANSLATIONS object from the main script
    if (typeof window.VALIDATION_TRANSLATIONS !== 'undefined' && window.VALIDATION_TRANSLATIONS[key]) {
        return window.VALIDATION_TRANSLATIONS[key];
    }

    // Fallback translations in case language file is not loaded
    const fallbackTranslations = {
        // Phone validation errors
        'COM_BATENSTEINFORM_ERROR_PHONE_REQUIRED': 'Phone number is required',
        'COM_BATENSTEINFORM_ERROR_PHONE_INVALID_CHARS': 'Phone number may only contain digits, spaces, dashes and +',
        'COM_BATENSTEINFORM_ERROR_PHONE_INVALID_FORMAT': 'Invalid Dutch phone number. Use a valid mobile (06) or landline number.',

        // Email validation errors  
        'COM_BATENSTEINFORM_ERROR_EMAIL_REQUIRED': 'Email address is required',
        'COM_BATENSTEINFORM_ERROR_EMAIL_SINGLE_AT': 'Email address must contain exactly one @',
        'COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_EMPTY': 'Email address must contain text before the @',
        'COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_TOO_LONG': 'The part before @ may be maximum 64 characters long',
        'COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_INVALID_CHARS': 'Invalid character in email address before @',
        'COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_DOT_POSITION': 'Email address may not start or end with a dot',
        'COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_CONSECUTIVE_DOTS': 'Email address may not contain consecutive dots',
        'COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_EMPTY': 'Email address must contain a domain after @',
        'COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_TOO_LONG': 'Domain name may be maximum 253 characters long',
        'COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_NO_DOT': 'Domain name must contain at least one dot',
        'COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_INVALID_POSITION': 'Domain name may not start or end with a dot or dash',
        'COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_EMPTY_LABEL': 'Domain name may not contain empty parts',
        'COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_LABEL_TOO_LONG': 'Each part of domain name may be maximum 63 characters long',
        'COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_INVALID_CHARS': 'Domain name may only contain letters, digits and dashes',
        'COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_LABEL_HYPHEN': 'Parts of domain name may not start or end with a dash',
        'COM_BATENSTEINFORM_ERROR_EMAIL_TLD_INVALID': 'Top-level domain must contain minimum 2 letters',
        'COM_BATENSTEINFORM_ERROR_EMAIL_TYPO_SUGGESTION': 'Did you possibly mean %s?',

        // General form errors
        'COM_BATENSTEINFORM_ERROR_EMERGENCY_CONSENT': 'Emergency treatment consent is required.',
        'COM_BATENSTEINFORM_IBAN_ERROR_INVALID_FORMAT': 'Invalid IBAN format. Please enter a valid Dutch IBAN.',
        'COM_BATENSTEINFORM_FORM_REQUIRED_FIELDS': 'Please fill in the required field:',
        'COM_BATENSTEINFORM_PHONE_NUMBER_LABEL': 'Phone number',
        'COM_BATENSTEINFORM_EMAIL_ADDRESS_LABEL': 'Email address'
    };

    return fallbackTranslations[key] || key;
}

/**
 * Validates if the selected scout section matches the birthdate/age
 * Shows warning if there's a mismatch
 * @return {boolean} True if valid or no validation needed, false if invalid
 */
function validateBirthdateAndSection() {
    var birthDateField = document.querySelector("#jform_birth_date");
    var scoutSectionField = document.querySelector("input[name='jform[scout_section]']:checked");

    if (!birthDateField || !scoutSectionField) return true;

    var birthDateValue = birthDateField.value;
    var scoutSectionValue = scoutSectionField.value;

    // If no birthdate or no section selected, no validation needed
    if (!birthDateValue || !scoutSectionValue) return true;

    var age = calculateAge(birthDateValue);
    console.log("Calculated age:", age);

    if (age === null) return true; // Invalid date, let other validation handle it

    var alertPrefix = getLanguageString('COM_BATENSTEINFORM_SECTION_ALERT_PREFIX').replace('%s', age) + getLanguageString('COM_BATENSTEINFORM_SECTION_ALERT_DOES_NOT_MATCH');


    // Validate age against selected section
    if (scoutSectionValue === "welpen" && (age < 7 || age >= 11)) {
        alert(alertPrefix + getLanguageString('COM_BATENSTEINFORM_SECTION_WELPEN_ALERT'));
        return false;
    }

    if (scoutSectionValue === "scouts" && (age < 11 || age >= 15)) {
        alert(alertPrefix + getLanguageString('COM_BATENSTEINFORM_SECTION_SCOUTS_ALERT'));
        return false;
    }

    if (scoutSectionValue === "explorers" && (age < 15 || age >= 18)) {
        alert(alertPrefix + getLanguageString('COM_BATENSTEINFORM_SECTION_EXPLORERS_ALERT'));
        return false;
    }

    if ((scoutSectionValue === "stam" || scoutSectionValue === "plus" || scoutSectionValue === "sikas") && age < 18) {
        alert(alertPrefix + getLanguageString('COM_BATENSTEINFORM_SECTION_STAM_ALERT'));
        return false;
    }

    return true;
}

/**
 * Validates emergency treatment consent
 * Shows appropriate alert message based on the consent value
 * @return {boolean} True if consent is given (Yes), false otherwise
 */
function validateEmergencyConsent() {
    var consent = document.querySelector("[name=\"jform[emergency_treatment_consent]\"]:checked");
    if (!consent || consent.value === "No") {
        confirm(getLanguageString('COM_BATENSTEINFORM_ERROR_EMERGENCY_CONSENT'));
        return false;
    }
    return true;
}

/**
 * Debounced auto-selection for birthdate changes
 * Only triggers auto-selection, not validation
 */
function debouncedAutoSelection() {
    if (birthdateValidationTimeout) {
        clearTimeout(birthdateValidationTimeout);
    }

    birthdateValidationTimeout = setTimeout(function () {
        var birthDateField = document.querySelector("#jform_birth_date");
        if (birthDateField) {
            var birthDateValue = birthDateField.value;
            var age = calculateAge(birthDateValue);
            if (age !== null) {
                if (age < 7) {
                    alert(getLanguageString('COM_BATENSTEINFORM_SECTION_ALERT_PREFIX').replace('%s', age) + getLanguageString('COM_BATENSTEINFORM_SECTION_ALERT_NO_COMBINATION'));
                } else {
                    autoSelectScoutSection(age);
                }
            }
        }
    }, 500); // Shorter delay for auto-selection
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
        var scoutSectionField = document.querySelector("input[name='jform[scout_section]']:checked");
        var scoutSection = scoutSectionField ? scoutSectionField.value : null;
        requiredFields = ["first_name", "calling_name", "last_name", "address", "postal_code_city", "birth_date", "birth_place", "scout_section"];

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
    }

    // Validate required fields
    for (var i = 0; i < requiredFields.length; i++) {
        var fieldName = requiredFields[i];
        var field = document.querySelector("[name=\"jform[" + fieldName + "]\"]");
        if (!field) {
            // Try radio buttons
            field = document.querySelector("[name=\"jform[" + fieldName + "]\"]:checked");
        }

        if (!field || !field.value || field.value.trim() === "") {
            valid = false;
            // Translate field name to user-friendly label
            var fieldLabel = getFieldLabel(fieldName);

            alert(getLanguageString('COM_BATENSTEINFORM_FORM_REQUIRED_FIELDS') + " " + fieldLabel);
            if (field) field.focus();
            break;
        }
    }

    return valid;
}

/**
 * Integration with Joomla's form validation system
 * Extends the existing validation to include phone and email checks
 */
function integrateBatensteinValidation() {
    // Override the existing validateCurrentPage function to include validation
    var originalValidateCurrentPage = window.validateCurrentPage;

    window.validateCurrentPage = function () {
        // First run the original validation
        var isValid = originalValidateCurrentPage ? originalValidateCurrentPage() : true;

        // Then add phone number validation for current page
        var currentPagePhoneFields = document.querySelectorAll(
            'input[name*="phone"]:not([type="hidden"]), input[name*="Phone"]:not([type="hidden"])'
        );

        currentPagePhoneFields.forEach(function (field) {
            var phoneValue = field.value.trim();

            // Skip validation if field is empty and not required
            if (phoneValue === '') {
                return;
            }

            var validation = validateDutchPhoneNumber(phoneValue);

            if (!validation.isValid) {
                isValid = false;

                // Get field label for error message
                var fieldLabel = getFieldLabel(field.name.replace('jform[', '').replace(']', '')) || getLanguageString('COM_BATENSTEINFORM_PHONE_NUMBER_LABEL');

                alert(fieldLabel + ': ' + validation.errors.join(', '));
                field.focus();
                return false; // Stop at first invalid field
            }
        });

        // Add email validation for current page
        var currentPageEmailFields = document.querySelectorAll(
            'input[name*="email"]:not([type="hidden"]), input[name*="Email"]:not([type="hidden"])'
        );

        currentPageEmailFields.forEach(function (field) {
            var emailValue = field.value.trim();

            // Skip validation if field is empty and not required
            if (emailValue === '') {
                return;
            }

            var validation = validateEmailAddress(emailValue);

            if (!validation.isValid) {
                isValid = false;

                // Get field label for error message
                var fieldLabel = getFieldLabel(field.name.replace('jform[', '').replace(']', '')) || getLanguageString('COM_BATENSTEINFORM_EMAIL_ADDRESS_LABEL');

                alert(fieldLabel + ': ' + validation.errors.join(', '));
                field.focus();
                return false; // Stop at first invalid field
            }
        });

        // Add iban validation for current page
        var currentPageIbanFields = document.querySelectorAll(
            'input[name*="iban"]:not([type="hidden"]), input[name*="iban"]:not([type="hidden"])'
        );

        currentPageIbanFields.forEach(function (field) {
            var ibanValue = field.value.trim();

            // Skip validation if field is empty and not required
            if (ibanValue === '') {
                return;
            }

            var validation = validateIbanWithChecksum(ibanValue);

            if (!validation.isValid) {
                isValid = false;

                // Get field label for error message
                var fieldLabel = getFieldLabel(field.name.replace('jform[', '').replace(']', '')) || getLanguageString('COM_BATENSTEINFORM_IBAN_LABEL');

                alert(fieldLabel + ': ' + validation.errors.join(', '));
                field.focus();
                return false; // Stop at first invalid field
            }
        });

        return isValid;
    };
}

/**
 * Function to add all stored form data as hidden fields before submission
 * This ensures all data from previous pages is included in the final submit
 */
function addStoredDataToForm() {
    var form = document.getElementById('batenstein-form');
    if (!form) {
        console.error('Form not found');
        return false;
    }

    // Get all stored form data
    var formData = getFormDataFromStorage();
    if (!formData) {
        console.warn('No stored form data found');
        return true; // Continue with submit even if no stored data
    }

    // Remove any existing hidden fields we might have added before
    var existingHiddenFields = form.querySelectorAll('input[data-stored-field="true"]');
    existingHiddenFields.forEach(function (field) {
        field.remove();
    });

    // Add hidden fields for each stored data item
    Object.keys(formData).forEach(function (fieldName) {
        var value = formData[fieldName];

        // Skip empty values and system fields
        if (!value || fieldName === 'option' || fieldName === 'task' || fieldName === 'page' || fieldName.includes('token')) {
            return;
        }

        // Check if field already exists in current form (to avoid duplicates)
        var existingField = form.querySelector('[name="' + fieldName + '"]');
        if (existingField && existingField.value) {
            return; // Field exists and has value, don't override
        }

        // Create hidden input field
        var hiddenField = document.createElement('input');
        hiddenField.type = 'hidden';
        hiddenField.name = fieldName;
        hiddenField.value = value;
        hiddenField.setAttribute('data-stored-field', 'true');

        form.appendChild(hiddenField);
    });

    console.log('Added stored form data to submission');
    return true;
}

/**
 * Debug function to log form data
 */
function debugFormData() {
    var form = document.getElementById('batenstein-form');
    if (!form) {
        console.error('Form not found');
        return;
    }

    console.log('=== FORM SUBMISSION DEBUG ===');

    // Get sessionStorage data
    var storedData = getFormDataFromStorage();
    console.log('Stored data in sessionStorage:', storedData);

    // Get current form data
    var formData = new FormData(form);
    var currentFormData = {};

    for (var entry of formData.entries()) {
        currentFormData[entry[0]] = entry[1];
    }

    console.log('Current form data (will be submitted):', currentFormData);

    // Check for missing fields
    if (storedData) {
        var missingFields = [];
        Object.keys(storedData).forEach(function (key) {
            if (!currentFormData[key] && storedData[key]) {
                missingFields.push(key);
            }
        });

        if (missingFields.length > 0) {
            console.warn('Fields in storage but not in current form:', missingFields);
        }
    }

    console.log('=== END DEBUG ===');
}

/**
 * Saves all form data to sessionStorage
 */
function saveFormData() {
    var existingData = {};
    var form = document.getElementById('batenstein-form');

    if (!form) return;

    // First, load existing data from sessionStorage
    try {
        var storedData = sessionStorage.getItem(FORM_STORAGE_KEY);
        if (storedData) {
            existingData = JSON.parse(storedData);
        }
    } catch (e) {
        console.warn('Could not load existing form data from sessionStorage:', e);
        existingData = {};
    }

    // Get all form elements from current page
    var elements = form.querySelectorAll('input, select, textarea');

    elements.forEach(function (element) {
        var name = element.name;
        if (!name) return;

        if (element.type === 'radio' || element.type === 'checkbox') {
            if (element.checked) {
                existingData[name] = element.value;
            }
        } else {
            // Only save non-empty values or explicitly preserve empty values
            if (element.value !== '' || existingData[name] !== undefined) {
                existingData[name] = element.value;
            }
        }
    });

    // Save merged data back to sessionStorage
    try {
        sessionStorage.setItem(FORM_STORAGE_KEY, JSON.stringify(existingData));
        invalidateFormDataCache();
    } catch (e) {
        console.warn('Could not save form data to sessionStorage:', e);
    }
}

/**
 * Restores form data from sessionStorage
 */
function restoreFormData() {
    try {
        var formData = getFormDataFromStorage();
        if (!formData) return;

        var form = document.getElementById('batenstein-form');

        // Restore each field
        Object.keys(formData).forEach(function (fieldName) {
            var value = formData[fieldName];
            if (!value) return;

            // Try to find the field
            var field = form.querySelector('[name="' + fieldName + '"]');

            if (field) {
                if (field.type === 'radio') {
                    // For radio buttons, find the one with matching value
                    var radioButton = form.querySelector('[name="' + fieldName + '"][value="' + value + '"]');
                    if (radioButton) {
                        radioButton.checked = true;
                        // Trigger change event to update form visibility
                        var event = new Event('change', { bubbles: true });
                        radioButton.dispatchEvent(event);
                    }
                } else if (field.type === 'checkbox') {
                    field.checked = (value === field.value);
                } else {
                    // Text inputs, textareas, selects
                    field.value = value;
                }
            }
        });

        // Trigger events to update conditional field visibility
        setTimeout(function () {
            // Trigger scout section change to update visibility
            var scoutSectionField = form.querySelector('input[name="jform[scout_section]"]:checked');
            if (scoutSectionField) {
                var changeEvent = new Event('change', { bubbles: true });
                scoutSectionField.dispatchEvent(changeEvent);
            }

            // Trigger health form conditional fields
            var conditionalFields = [
                'input[name="jform[can_swim]"]:checked',
                'input[name="jform[special_health_care]"]:checked',
                'input[name="jform[medication]"]:checked',
                'input[name="jform[allergies]"]:checked',
                'input[name="jform[diet]"]:checked'
            ];

            conditionalFields.forEach(function (selector) {
                var field = form.querySelector(selector);
                if (field) {
                    var changeEvent = new Event('change', { bubbles: true });
                    field.dispatchEvent(changeEvent);
                }
            });
        }, 100);

    } catch (e) {
        console.warn('Could not restore form data from sessionStorage:', e);
    }
}

/**
 * Auto-save form data periodically and on field changes
 */
function initializeAutoSave() {
    var autoSaveTimeout;

    function triggerAutoSave() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(function () {
            saveFormData();
        }, 1000); // Auto-save after 1 second of inactivity
    }

    // Save on form field changes
    document.addEventListener('change', function (e) {
        if (e.target.closest('#batenstein-form')) {
            triggerAutoSave();
        }
    });

    document.addEventListener('input', function (e) {
        if (e.target.closest('#batenstein-form')) {
            triggerAutoSave();
        }
    });

    // Save before page unload
    window.addEventListener('beforeunload', function () {
        saveFormData();
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
        console.warn('Could not clear form data from sessionStorage:', e);
    }
}

/**
 * Navigation functions
 */
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

/**
 * Populates review content on final page
 * Uses centralized data retrieval and field labeling
 */
function populateReviewContent() {
    var reviewContainer = document.getElementById("review-content");
    if (!reviewContainer) {
        console.error("Review container not found");
        return;
    }

    var reviewHtml = "";

    // Personal Information Section
    reviewHtml += generateReviewSection("Personal Information", [
        "first_name", "calling_name", "name_prefix", "last_name",
        "address", "postal_code_city", "birth_date", "birth_place", "scout_section"
    ]);

    // Personal Contact Details (if applicable)
    var scoutSection = getFieldValue("scout_section", true);
    if (scoutSection && scoutSection !== "welpen") {
        reviewHtml += generateReviewSection("Personal Contact", [
            "phone_number", "email_address"
        ]);
    }

    // Parent/Guardian Contact Details (if applicable)
    if (scoutSection === "welpen" || scoutSection === "scouts" || scoutSection === "explorers") {
        reviewHtml += generateReviewSection("Parent/Guardian 1", [
            "parent1_name", "parent1_phone_number", "parent1_email_address"
        ]);

        var parent2Name = getFieldValue("parent2_name");
        if (parent2Name && parent2Name.trim() !== "") {
            reviewHtml += generateReviewSection("Parent/Guardian 2", [
                "parent2_name", "parent2_phone_number", "parent2_email_address"
            ]);
        }
    }

    // Image/Video Permissions
    reviewHtml += generateReviewSection("Image/Video Permissions", [
        "images_website", "images_social", "images_newspaper"
    ]);

    // Health Information
    reviewHtml += generateReviewSection("Health Information", [
        "can_swim", "swim_diplomas", "emergency_contact_name", "emergency_contact_relation",
        "emergency_contact_phone", "special_health_care", "special_health_care_details",
        "medication", "medication_details", "allergies", "allergies_details",
        "diet", "diet_details", "health_insurance", "policy_number",
        "gp_name", "gp_address", "gp_phone", "dentist_name", "dentist_address",
        "dentist_phone", "emergency_treatment_consent"
    ]);

    // Payment Information  
    reviewHtml += generateReviewSection("Payment Information", [
        "iban", "account_name", "sign_date"
    ]);

    // Comments (if filled)
    var comments = getFieldValue("comments");
    if (comments && comments.trim() !== "") {
        reviewHtml += generateReviewSection("Comments", [
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
    var sectionHtml = '<div class="review-section">' +
        '<h3 class="review-section-title">' + sectionTitle + '</h3>' +
        '<div class="review-fields">';

    fields.forEach(function (fieldName) {
        var radioFields = ["scout_section", "images_website", "images_social", "images_newspaper",
            "can_swim", "special_health_care", "medication", "allergies",
            "diet", "emergency_treatment_consent"];
        var isRadio = radioFields.indexOf(fieldName) !== -1;
        var value = getFieldValue(fieldName, isRadio);

        // Handle conditional fields
        if ((fieldName === "swim_diplomas" && getFieldValue("can_swim", true) !== "Yes") ||
            (fieldName === "special_health_care_details" && getFieldValue("special_health_care", true) !== "Yes") ||
            (fieldName === "medication_details" && getFieldValue("medication", true) !== "Yes") ||
            (fieldName === "allergies_details" && getFieldValue("allergies", true) !== "Yes") ||
            (fieldName === "diet_details" && getFieldValue("diet", true) !== "Yes")) {
            return;
        }

        if (value && value.trim() !== "") {
            var label = getFieldLabel(fieldName);
            var displayValue = value;

            // Translate values using centralized TRANSLATIONS object
            if (typeof TRANSLATIONS !== 'undefined' && TRANSLATIONS[value]) {
                displayValue = TRANSLATIONS[value];
            }

            // Format textarea content
            if (fieldName.includes("details") || fieldName === "comments" || fieldName.includes("address")) {
                displayValue = displayValue.replace(/\n/g, "<br>");
            }

            var specialClass = fieldName === "emergency_treatment_consent" && value === "No" ? ' style="color: #d9534f; font-weight: bold;"' : "";

            sectionHtml += '<div class="review-field" data-field="' + fieldName + '">' +
                '<span class="review-field-label">' + label + ':</span>' +
                '<span class="review-field-value"' + specialClass + '>' + displayValue + '</span>' +
                '</div>';
        }
    });

    sectionHtml += '</div></div>';
    return sectionHtml;
}

/**
 * Enhanced Joomla.submitbutton function with sessionStorage data integration
 */
function setupJoomlaSubmitButton() {
    var originalSubmitbutton = window.Joomla ? window.Joomla.submitbutton : null;

    if (!window.Joomla) {
        window.Joomla = {};
    }

    window.Joomla.submitbutton = function (task) {
        if (task == "form.cancel") {
            if (window.Joomla.submitform) {
                window.Joomla.submitform(task, document.getElementById('batenstein-form'));
            }
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
            if (document.formvalidator && document.formvalidator.isValid(document.getElementById('batenstein-form'))) {
                // Add all stored data to form before submission
                if (addStoredDataToForm()) {
                    // Submit form
                    if (window.Joomla.submitform) {
                        window.Joomla.submitform(task, document.getElementById('batenstein-form'));
                    }

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
}

/**
 * Show/hide personal contact fields based on scout section
 */
function togglePersonalContact() {
    var scoutSectionField = document.querySelector("input[name='jform[scout_section]']:checked");
    var scoutSection = scoutSectionField ? scoutSectionField.value : null;

    var personalContactSection = document.getElementById("personal_contact");
    var personalContactNote = document.getElementById("personal_contact_note");

    if (scoutSection === "scouts" || scoutSection === "explorers" || scoutSection === "stam" || scoutSection === "sikas" || scoutSection === "plus") {
        if (personalContactSection) personalContactSection.style.display = "block";
        if (personalContactNote) personalContactNote.style.display = "block";
    } else {
        if (personalContactSection) personalContactSection.style.display = "none";
        if (personalContactNote) personalContactNote.style.display = "none";
    }

    var star = '<span class="star" aria-hidden="true">&nbsp;*</span>';

    // Add or remove * from email address label
    var emailLabel = document.getElementById("jform_email_address-lbl");
    if (emailLabel) {
        if (scoutSection === "scouts" || scoutSection === "explorers" || scoutSection === "stam" || scoutSection === "sikas" || scoutSection === "plus") {
            // Add * to label if it's not already there
            if (emailLabel.innerHTML.indexOf(star) === -1) {
                emailLabel.innerHTML = emailLabel.innerHTML + star;
            }
        } else {
            // Remove * from label
            emailLabel.innerHTML = emailLabel.innerHTML.replace(star, '');
        }
    }

    // Add or remove * from phone number label
    var phoneLabel = document.getElementById("jform_phone_number-lbl");
    if (phoneLabel) {
        if (scoutSection === "explorers" || scoutSection === "stam" || scoutSection === "sikas" || scoutSection === "plus") {
            // Add * to label if it's not already there
            phoneLabel.innerHTML = phoneLabel.innerHTML.replace(star, '');
            if (phoneLabel.innerHTML.indexOf(star) === -1) {
                phoneLabel.innerHTML = phoneLabel.innerHTML + star;
            }
        } else {
            // Remove * from label
            phoneLabel.innerHTML = phoneLabel.innerHTML.replace(star, '');
        }
    }
}

/**
 * Show/hide parent contact fields based on scout section
 */
function toggleParentContact() {
    var scoutSectionField = document.querySelector("input[name='jform[scout_section]']:checked");
    var scoutSection = scoutSectionField ? scoutSectionField.value : null;

    var parent1Fieldset = document.getElementById("parent1_fieldset");
    var parent2Fieldset = document.getElementById("parent2_fieldset");

    if (scoutSection === "welpen" || scoutSection === "scouts" || scoutSection === "explorers") {
        if (parent1Fieldset) parent1Fieldset.style.display = "block";
        if (parent2Fieldset) parent2Fieldset.style.display = "block";
    } else {
        if (parent1Fieldset) parent1Fieldset.style.display = "none";
        if (parent2Fieldset) parent2Fieldset.style.display = "none";
    }
}

/**
 * Show/hide conditional health fields
 */
function setupHealthConditionalFields() {
    // Swimming diplomas field
    var canSwimFields = document.querySelectorAll("input[name='jform[can_swim]']");
    canSwimFields.forEach(function (field) {
        field.addEventListener('change', function () {
            var swimDiplomasContainer = document.getElementById("swim_diplomas_container");
            if (swimDiplomasContainer) {
                if (this.value === "Yes") {
                    swimDiplomasContainer.style.display = "block";
                } else {
                    swimDiplomasContainer.style.display = "none";
                }
            }
        });
    });

    // Special health care details
    var specialHealthCareFields = document.querySelectorAll("input[name='jform[special_health_care]']");
    specialHealthCareFields.forEach(function (field) {
        field.addEventListener('change', function () {
            var detailsContainer = document.getElementById("special_health_care_details_container");
            if (detailsContainer) {
                if (this.value === "Yes") {
                    detailsContainer.style.display = "block";
                } else {
                    detailsContainer.style.display = "none";
                }
            }
        });
    });

    // Medication details
    var medicationFields = document.querySelectorAll("input[name='jform[medication]']");
    medicationFields.forEach(function (field) {
        field.addEventListener('change', function () {
            var detailsContainer = document.getElementById("medication_details_container");
            if (detailsContainer) {
                if (this.value === "Yes") {
                    detailsContainer.style.display = "block";
                } else {
                    detailsContainer.style.display = "none";
                }
            }
        });
    });

    // Allergies details
    var allergiesFields = document.querySelectorAll("input[name='jform[allergies]']");
    allergiesFields.forEach(function (field) {
        field.addEventListener('change', function () {
            var detailsContainer = document.getElementById("allergies_details_container");
            if (detailsContainer) {
                if (this.value === "Yes") {
                    detailsContainer.style.display = "block";
                } else {
                    detailsContainer.style.display = "none";
                }
            }
        });
    });

    // Diet details
    var dietFields = document.querySelectorAll("input[name='jform[diet]']");
    dietFields.forEach(function (field) {
        field.addEventListener('change', function () {
            var detailsContainer = document.getElementById("diet_details_container");
            if (detailsContainer) {
                if (this.value === "Yes") {
                    detailsContainer.style.display = "block";
                } else {
                    detailsContainer.style.display = "none";
                }
            }
        });
    });

    // Initial state - trigger change events for checked fields
    var conditionalFieldSelectors = [
        "input[name='jform[can_swim]']:checked",
        "input[name='jform[special_health_care]']:checked",
        "input[name='jform[medication]']:checked",
        "input[name='jform[allergies]']:checked",
        "input[name='jform[diet]']:checked"
    ];

    conditionalFieldSelectors.forEach(function (selector) {
        var field = document.querySelector(selector);
        if (field) {
            var changeEvent = new Event('change', { bubbles: true });
            field.dispatchEvent(changeEvent);
        }
    });
}

/**
 * Initialize all form functionality
 */
function initializeBatensteinForm() {
    // Initialize phone and email validation
    initializeBatensteinValidation();

    // Integrate with form validation
    integrateBatensteinValidation();

    // Setup Joomla submit button functionality
    setupJoomlaSubmitButton();

    // Restore form data when page loads
    restoreFormData();

    // Initialize auto-save functionality
    initializeAutoSave();

    // Setup birthdate auto-selection
    var birthDateField = document.querySelector("#jform_birth_date");
    if (birthDateField) {
        // Auto-select scout section when birthdate changes
        birthDateField.addEventListener("input", function () {
            debouncedAutoSelection();
        });

        birthDateField.addEventListener("change", function () {
            debouncedAutoSelection();
        });

        // Auto-select immediately when user leaves the birthdate field
        birthDateField.addEventListener("blur", function () {
            if (birthdateValidationTimeout) {
                clearTimeout(birthdateValidationTimeout);
            }
            var birthDateValue = this.value;
            var age = calculateAge(birthDateValue);
            if (age !== null) {
                autoSelectScoutSection(age);
            }
        });

        // Initial auto-selection if birthdate is already filled
        var initialBirthdate = birthDateField.value;
        if (initialBirthdate) {
            var initialAge = calculateAge(initialBirthdate);
            if (initialAge !== null) {
                autoSelectScoutSection(initialAge);
            }
        }
    }

    // Setup scout section change handlers
    var scoutSectionFields = document.querySelectorAll("input[name='jform[scout_section]']");
    scoutSectionFields.forEach(function (field) {
        field.addEventListener("click", function () {
            // Small delay to ensure the change event completes
            setTimeout(function () {
                validateBirthdateAndSection();
            }, 100);

            // Also trigger form visibility functions
            togglePersonalContact();
            toggleParentContact();
        });

        field.addEventListener("change", function () {
            togglePersonalContact();
            toggleParentContact();
        });
    });

    // Initial visibility setup
    togglePersonalContact();
    toggleParentContact();

    // Initial validation if birthdate is already filled
    if (birthDateField && birthDateField.value) {
        validateBirthdateAndSection();
    }

    // Setup health form conditional fields
    setupHealthConditionalFields();

    // If Page 5 is reached, populate the review content
    if (typeof currentPage !== 'undefined' && currentPage === 5) {
        setTimeout(function () {
            populateReviewContent();
        }, 100);
    }
}

/**
 * Validates email addresses with comprehensive format checking
 * Follows RFC 5322 standards with practical restrictions
 * 
 * @param {string} email - The email address to validate
 * @return {object} Validation result with isValid boolean and formatted email
 */
function validateEmailAddress(email) {
    // Return object structure
    const result = {
        isValid: false,
        formatted: '',
        errors: []
    };

    // Check if email is provided
    if (!email || typeof email !== 'string') {
        result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_EMAIL_REQUIRED'));
        return result;
    }

    // Clean the input: trim whitespace
    const cleanEmail = email.trim();

    // Basic structure check: must contain exactly one @
    const atCount = (cleanEmail.match(/@/g) || []).length;
    if (atCount !== 1) {
        result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_EMAIL_SINGLE_AT'));
        return result;
    }

    // Split into local and domain parts
    const localAndDomain = cleanEmail.split('@');
    const localPart = localAndDomain[0];
    const domainPart = localAndDomain[1];

    // Validate local part (before @)
    if (!localPart || localPart.length === 0) {
        result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_EMPTY'));
        return result;
    }

    if (localPart.length > 64) {
        result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_TOO_LONG'));
        return result;
    }

    // Local part character validation
    const validLocalChars = /^[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+(\.[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+)*$/;
    if (!validLocalChars.test(localPart)) {
        result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_INVALID_CHARS'));
        return result;
    }

    // Local part cannot start or end with dot
    if (localPart.startsWith('.') || localPart.endsWith('.')) {
        result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_DOT_POSITION'));
        return result;
    }

    // Local part cannot have consecutive dots
    if (localPart.includes('..')) {
        result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_CONSECUTIVE_DOTS'));
        return result;
    }

    // Validate domain part (after @)
    if (!domainPart || domainPart.length === 0) {
        result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_EMPTY'));
        return result;
    }

    if (domainPart.length > 253) {
        result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_TOO_LONG'));
        return result;
    }

    // Domain must contain at least one dot
    if (!domainPart.includes('.')) {
        result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_NO_DOT'));
        return result;
    }

    // Domain cannot start or end with dot or hyphen
    if (domainPart.startsWith('.') || domainPart.endsWith('.') ||
        domainPart.startsWith('-') || domainPart.endsWith('-')) {
        result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_INVALID_POSITION'));
        return result;
    }

    // Split domain into labels
    const domainLabels = domainPart.split('.');

    // Validate each domain label
    for (var i = 0; i < domainLabels.length; i++) {
        var label = domainLabels[i];
        if (label.length === 0) {
            result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_EMPTY_LABEL'));
            return result;
        }

        if (label.length > 63) {
            result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_LABEL_TOO_LONG'));
            return result;
        }

        // Label character validation (letters, numbers, hyphens)
        if (!/^[a-zA-Z0-9-]+$/.test(label)) {
            result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_INVALID_CHARS'));
            return result;
        }

        // Label cannot start or end with hyphen
        if (label.startsWith('-') || label.endsWith('-')) {
            result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_LABEL_HYPHEN'));
            return result;
        }
    }

    // Check top-level domain (last label)
    const tld = domainLabels[domainLabels.length - 1];
    if (!/^[a-zA-Z]{2,}$/.test(tld)) {
        result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_EMAIL_TLD_INVALID'));
        return result;
    }

    // Additional practical checks
    // Check for common typos in popular domains
    const commonDomainTypos = {
        'gmial.com': 'gmail.com',
        'gmai.com': 'gmail.com',
        'yahooo.com': 'yahoo.com',
        'hotmial.com': 'hotmail.com',
        'outlok.com': 'outlook.com'
    };

    if (commonDomainTypos[domainPart.toLowerCase()]) {
        const suggestion = commonDomainTypos[domainPart.toLowerCase()];
        result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_EMAIL_TYPO_SUGGESTION').replace('%s', suggestion));
        return result;
    }

    // If all validations pass
    result.isValid = true;
    result.formatted = cleanEmail.toLowerCase(); // Normalize to lowercase

    return result;
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
        formatted: '',
        type: '', // 'mobile', 'landline', or 'international'
        errors: []
    };

    // Check if phone number is provided
    if (!phoneNumber || typeof phoneNumber !== 'string') {
        result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_PHONE_REQUIRED'));
        return result;
    }

    // Clean the input: remove all spaces, dashes, dots, and parentheses
    const cleanNumber = phoneNumber.replace(/[\s\-\.\(\)]/g, '');

    // Check if only contains valid characters (digits and + at start)
    if (!/^[\+]?[0-9]+$/.test(cleanNumber)) {
        result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_PHONE_INVALID_CHARS'));
        return result;
    }

    // Dutch mobile number patterns - 06 numbers
    const mobilePatterns = [
        /^06[0-9]{8}$/,           // 0612345678
        /^\+316[0-9]{8}$/,        // +31612345678
        /^00316[0-9]{8}$/         // 0031612345678
    ];

    // Dutch landline patterns with proper area code handling
    // 3-digit area codes (2 digits after 0): 010, 013, 015, 020, etc.
    const landlinePatterns3Digit = [
        /^0(10|13|15|20|23|24|26|30|33|35|36|38|40|43|45|46|50|53|55|58|70|71|72|73|74|75|76|77|78|79)[0-9]{7}$/,
        /^\+31(10|13|15|20|23|24|26|30|33|35|36|38|40|43|45|46|50|53|55|58|70|71|72|73|74|75|76|77|78|79)[0-9]{7}$/,
        /^0031(10|13|15|20|23|24|26|30|33|35|36|38|40|43|45|46|50|53|55|58|70|71|72|73|74|75|76|77|78|79)[0-9]{7}$/
    ];

    // 4-digit area codes (3 digits after 0): 0117, 0118, 0161, 0162, etc.
    const landlinePatterns4Digit = [
        /^0(111|113|114|115|117|118|161|162|164|165|166|167|168|172|174|180|181|182|183|184|186|187|222|223|224|226|227|228|229|251|252|255|294|297|299|313|314|315|316|317|318|320|321|342|343|344|345|346|347|348|411|412|413|416|418|475|478|481|485|486|487|488|492|493|495|497|498|499|511|512|513|514|515|516|517|518|519|521|522|523|524|525|527|528|529|541|543|544|545|546|547|548|561|562|566|571|572|573574|575|577|578|591|592|593|594|595|596|597|598|599)[0-9]{6}$/,
        /^\+31(111|113|114|115|117|118|161|162|164|165|166|167|168|172|174|180|181|182|183|184|186|187|222|223|224|226|227|228|229|251|252|255|294|297|299|313|314|315|316|317|318|320|321|342|343|344|345|346|347|348|411|412|413|416|418|475|478|481|485|486|487|488|492|493|495|497|498|499|511|512|513|514|515|516|517|518|519|521|522|523|524|525|527|528|529|541|543|544|545|546|547|548|561|562|566|571|572|573|574|575|577|578|591|592|593|594|595|596|597|598|599)[0-9]{6}$/,
        /^0031(111|113|114|115|117|118|161|162|164|165|166|167|168|172|174|180|181|182|183|184|186|187|222|223|224|226|227|228|229|251|252|255|294|297|299|313|314|315|316|317|318|320|321|342|343|344|345|346|347|348|411|412|413|416|418|475|478|481|485|486|487|488|492|493|495|497|498|499|511|512|513|514|515|516|517|518|519|521|522|523|524|525|527|528|529|541|543|544|545|546|547|548|561|562|566|571|572|573|574|575|577|578|591|592|593|594|595|596|597|598|599)[0-9]{6}$/
    ];

    // Check mobile patterns first
    for (var i = 0; i < mobilePatterns.length; i++) {
        var pattern = mobilePatterns[i];
        if (pattern.test(cleanNumber)) {
            result.isValid = true;
            result.type = 'mobile';

            // Format mobile numbers as 06-12345678
            if (cleanNumber.startsWith('06')) {
                result.formatted = cleanNumber.substring(0, 2) + '-' + cleanNumber.substring(2);
            } else if (cleanNumber.startsWith('+316')) {
                const nationalNumber = '0' + cleanNumber.substring(3);
                result.formatted = nationalNumber.substring(0, 2) + '-' + nationalNumber.substring(2);
            } else if (cleanNumber.startsWith('00316')) {
                const nationalNumber = '0' + cleanNumber.substring(4);
                result.formatted = nationalNumber.substring(0, 2) + '-' + nationalNumber.substring(2);
            }

            return result;
        }
    }

    // Check 3-digit area code landline patterns first
    for (var i = 0; i < landlinePatterns3Digit.length; i++) {
        var pattern = landlinePatterns3Digit[i];
        if (pattern.test(cleanNumber)) {
            result.isValid = true;
            result.type = 'landline';

            // Format with 3-digit area code
            if (cleanNumber.startsWith('0')) {
                result.formatted = cleanNumber.substring(0, 3) + '-' + cleanNumber.substring(3);
            } else if (cleanNumber.startsWith('+31')) {
                const nationalNumber = '0' + cleanNumber.substring(3);
                result.formatted = nationalNumber.substring(0, 3) + '-' + nationalNumber.substring(3);
            } else if (cleanNumber.startsWith('0031')) {
                const nationalNumber = '0' + cleanNumber.substring(4);
                result.formatted = nationalNumber.substring(0, 3) + '-' + nationalNumber.substring(3);
            }

            return result;
        }
    }

    // Check 4-digit area code landline patterns
    for (var i = 0; i < landlinePatterns4Digit.length; i++) {
        var pattern = landlinePatterns4Digit[i];
        if (pattern.test(cleanNumber)) {
            result.isValid = true;
            result.type = 'landline';

            // Format with 4-digit area code
            if (cleanNumber.startsWith('0')) {
                result.formatted = cleanNumber.substring(0, 4) + '-' + cleanNumber.substring(4);
            } else if (cleanNumber.startsWith('+31')) {
                const nationalNumber = '0' + cleanNumber.substring(3);
                result.formatted = nationalNumber.substring(0, 4) + '-' + nationalNumber.substring(4);
            } else if (cleanNumber.startsWith('0031')) {
                const nationalNumber = '0' + cleanNumber.substring(4);
                result.formatted = nationalNumber.substring(0, 4) + '-' + nationalNumber.substring(4);
            }

            return result;
        }
    }

    // If no pattern matches, the number format is invalid
    result.errors.push(getLanguageString('COM_BATENSTEINFORM_ERROR_PHONE_INVALID_FORMAT'));
    return result;
}

/**
 * Real-time phone number validation for form fields
 * Shows error messages only for invalid input
 * 
 * @param {HTMLElement} phoneField - The phone input field
 * @param {HTMLElement} errorContainer - Container for error messages (optional)
 */
function setupPhoneValidation(phoneField, errorContainer) {
    if (!phoneField) return;

    // Create error container if not provided
    if (!errorContainer) {
        errorContainer = document.createElement('div');
        errorContainer.className = 'phone-validation-error';
        errorContainer.style.color = '#d9534f';
        errorContainer.style.fontSize = '0.875em';
        errorContainer.style.marginTop = '5px';
        phoneField.parentNode.appendChild(errorContainer);
    }

    // Validation timeout for debouncing
    var validationTimeout;

    /**
     * Perform validation with visual feedback
     */
    function performValidation() {
        const phoneValue = phoneField.value.trim();

        // Clear previous styling and errors
        phoneField.classList.remove('invalid-phone');
        errorContainer.textContent = '';

        if (phoneValue === '') {
            // Empty field - remove validation styling
            return;
        }

        const validation = validateDutchPhoneNumber(phoneValue);

        if (validation.isValid) {
            // Valid phone number - clear any error styling
            phoneField.classList.remove('invalid');

            // Optionally update field with formatted number
            if (validation.formatted !== phoneValue) {
                phoneField.value = validation.formatted;
            }
        } else {
            // Invalid phone number - show error
            phoneField.classList.add('invalid-phone');
            phoneField.classList.add('invalid');

            // Show error message
            errorContainer.textContent = validation.errors.join(', ');
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
    phoneField.addEventListener('input', debouncedValidation);
    phoneField.addEventListener('blur', performValidation); // Immediate validation on blur
    phoneField.addEventListener('paste', function () {
        // Validate after paste with short delay
        setTimeout(performValidation, 100);
    });
}

/**
 * Real-time email validation for form fields
 * Shows error messages only for invalid input
 * 
 * @param {HTMLElement} emailField - The email input field
 * @param {HTMLElement} errorContainer - Container for error messages (optional)
 */
function setupEmailValidation(emailField, errorContainer) {
    if (!emailField) return;

    // Create error container if not provided
    if (!errorContainer) {
        errorContainer = document.createElement('div');
        errorContainer.className = 'email-validation-error';
        errorContainer.style.color = '#d9534f';
        errorContainer.style.fontSize = '0.875em';
        errorContainer.style.marginTop = '5px';
        emailField.parentNode.appendChild(errorContainer);
    }

    // Validation timeout for debouncing
    var validationTimeout;

    /**
     * Perform validation with visual feedback
     */
    function performValidation() {
        const emailValue = emailField.value.trim();

        // Clear previous styling and errors
        emailField.classList.remove('invalid-email');
        errorContainer.textContent = '';

        if (emailValue === '') {
            // Empty field - remove validation styling
            return;
        }

        const validation = validateEmailAddress(emailValue);

        if (validation.isValid) {
            // Valid email - clear any error styling
            emailField.classList.remove('invalid');

            // Optionally update field with formatted email (lowercase)
            if (validation.formatted !== emailValue) {
                emailField.value = validation.formatted;
            }
        } else {
            // Invalid email - show error
            emailField.classList.add('invalid-email');
            emailField.classList.add('invalid');

            // Show error message
            errorContainer.textContent = validation.errors.join(', ');
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
    emailField.addEventListener('input', debouncedValidation);
    emailField.addEventListener('blur', performValidation); // Immediate validation on blur
    emailField.addEventListener('paste', function () {
        // Validate after paste with short delay
        setTimeout(performValidation, 100);
    });
}

/**
 * Real-time IBAN validation
 * Shows erorr message if IBAN is invalid
 * 
 * @param {HTMLElement} ibanField} ibanField 
 * @param {HTMLElement} errorContainer - Container for error messages (optional)
 */
function setupIbanValidation(ibanField, errorContainer) {
    if (!ibanField) return;

    // Create error container if not provided
    if (!errorContainer) {
        errorContainer = document.createElement('div');
        errorContainer.className = 'iban-validation-error';
        errorContainer.style.color = '#d9534f';
        errorContainer.style.fontSize = '0.875em';
        errorContainer.style.marginTop = '5px';
        ibanField.parentNode.appendChild(errorContainer);
    }

    // Validation timeout for debouncing
    var validationTimeout;

    /**
     * Perform validation with visual feedback
     */
    function performValidation() {
        const ibanValue = ibanField.value.trim();

        // Clear previous styling and errors
        ibanField.classList.remove('invalid-iban');
        errorContainer.textContent = '';

        if (ibanValue === '') {
            // Empty field - remove validation styling
            return;
        }

        const validation = validateIbanWithChecksum(ibanValue);

        if (validation.isValid) {
            // Valid iban - clear any error styling
            ibanField.classList.remove('invalid');

            // Optionally update field with formatted email (lowercase)
            if (validation.formatted !== ibanValue) {
                emailField.value = validation.formatted;
            }
        } else {
            // Invalid email - show error
            ibanField.classList.add('invalid-iban');
            ibanField.classList.add('invalid');

            // Show error message
            errorContainer.textContent = validation.errors.join(', ');
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
    ibanField.addEventListener('input', debouncedValidation);
    ibanField.addEventListener('blur', performValidation); // Immediate validation on blur
    ibanField.addEventListener('paste', function () {
        // Validate after paste with short delay
        setTimeout(performValidation, 100);
    });
}


/**
 * Batch setup for multiple phone and email fields in the Batenstein form
 * Automatically finds and sets up validation for all relevant fields
 */
function initializeBatensteinValidation() {
    // Phone field selectors for the Batenstein form
    const phoneFieldSelectors = [
        'input[name="jform[phone_number]"]',
        'input[name="jform[parent1_phone_number]"]',
        'input[name="jform[parent2_phone_number]"]',
        'input[name="jform[emergency_contact_phone]"]',
        'input[name="jform[gp_phone]"]',
        'input[name="jform[dentist_phone]"]'
    ];

    // Email field selectors for the Batenstein form
    const emailFieldSelectors = [
        'input[name="jform[email_address]"]',
        'input[name="jform[parent1_email_address]"]',
        'input[name="jform[parent2_email_address]"]'
    ];

    // IBAN field selectors for the Batenstein form
    const ibanFieldSelectors = [
        'input[name="jform[iban]"]'
    ];


    // Setup phone validation
    phoneFieldSelectors.forEach(function (selector) {
        const field = document.querySelector(selector);
        if (field) {
            setupPhoneValidation(field);
        }
    });

    // Setup email validation
    emailFieldSelectors.forEach(function (selector) {
        const field = document.querySelector(selector);
        if (field) {
            setupEmailValidation(field);
        }
    });

    // Setup IBAN validation
    ibanFieldSelectors.forEach(function (selector) {
        const field = document.querySelector(selector);
        if (field) {
            setupIbanValidation(field);
        }
    });
}

/**
 * Centralized function to get form data from sessionStorage with caching
 * Reduces repeated sessionStorage access and improves performance
 * @return {Object|null} Parsed form data or null if no data
 */
function getFormDataFromStorage() {
    var currentTime = Date.now();

    // Return cached data if still valid (uses global CACHE_DURATION constant)
    if (cachedFormData && typeof CACHE_DURATION !== 'undefined' && (currentTime - cacheTimestamp) < CACHE_DURATION) {
        return cachedFormData;
    }

    try {
        // Use global FORM_STORAGE_KEY constant
        var storedData = sessionStorage.getItem(FORM_STORAGE_KEY);
        if (storedData) {
            cachedFormData = JSON.parse(storedData);
            cacheTimestamp = currentTime;
            return cachedFormData;
        }
    } catch (e) {
        console.warn('Could not retrieve form data from sessionStorage:', e);
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
 * @param {boolean} isRadio - Whether it's a radio button field
 * @return {string} Field value or empty string
 */
function getFieldValue(fieldName, isRadio) {
    if (typeof isRadio === 'undefined') {
        isRadio = false;
    }

    // First try to get value from current DOM (current page)
    var form = document.getElementById('batenstein-form');
    if (form) {
        var selector = isRadio
            ? 'input[name="jform[' + fieldName + ']"]:checked'
            : '[name="jform[' + fieldName + ']"]';

        var field = form.querySelector(selector);
        if (field && field.value) {
            return field.value;
        }
    }

    // If not found in DOM, try sessionStorage
    var formData = getFormDataFromStorage();
    if (!formData) return "";

    var fieldKey = 'jform[' + fieldName + ']';
    return formData[fieldKey] || "";
}

/**
 * Get field label from centralized FIELD_LABELS object
 * @param {string} fieldName - The field name
 * @return {string} Human-readable field label
 */
function getFieldLabel(fieldName) {
    if (typeof FIELD_LABELS !== 'undefined' && FIELD_LABELS[fieldName]) {
        return FIELD_LABELS[fieldName];
    }
    return fieldName;
}

/**
 * Validates Dutch IBAN format
 * Accepts format: NL46 ASNB 0708 4337 23 (with or without spaces)
 *
 * @param {string} iban - The IBAN number to validate
 * @return {boolean} True if valid IBAN format, false otherwise
 */
function validateDutchIban(iban) {
    const cleanIban = iban.replace(/\s/g, '');
    if (!/^NL/.test(cleanIban.toUpperCase().substring(0, 2))) return false;
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
 * @return {object} Validation result with isValid boolean and formatted iban
 */
function validateIbanWithChecksum(iban) {
    // Return object structure
    const result = {
        isValid: false,
        formatted: '',
        errors: []
    };

    const cleanIban = iban.toUpperCase().replace(/\s/g, '');
    // First check the format
    if (!validateDutchIban(cleanIban)) {
        return {
            formatted: cleanIban,
            isValid: false,
            errors: [getLanguageString('COM_BATENSTEINFORM_IBAN_ERROR_INVALID_FORMAT')],
        }
    }

    const rearranged = cleanIban.substring(4) + cleanIban.substring(0, 4);

    // Replace letters with numbers (A=10, B=11, ..., Z=35)
    var numeric = '';
    for (var i = 0; i < rearranged.length; i++) {
        const char = rearranged[i];
        if (/[A-Z]/.test(char)) {
            // Convert letter to number
            numeric += (char.charCodeAt(0) - 'A'.charCodeAt(0) + 10).toString();
        } else {
            numeric += char;
        }
    }

    // Calculate mod 97 for large numbers
    var remainder = 0;
    for (var i = 0; i < numeric.length; i++) {
        remainder = (remainder * 10 + parseInt(numeric[i])) % 97;
    }
    // Check if the remainder is 1 else the IBAN is invalid
    if (remainder !== 1) {
        result.errors.push(getLanguageString('COM_BATENSTEINFORM_IBAN_ERROR_INVALID_FORMAT'));
    }
    // If all validations pass
    result.isValid = remainder === 1;
    result.formatted = cleanIban.toUpperCase(); // Normalize to uppercase

    return result;
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
                var changeEvent = new Event('change', { bubbles: true });
                scoutSections[i].dispatchEvent(changeEvent);
                break;
            }
        }
    }
}

// Auto-initialize when DOM is ready (fallback if not manually initialized)
document.addEventListener('DOMContentLoaded', function () {
    // Only auto-initialize if not already done
    if (!window.validationInitialized) {
        initializeBatensteinForm();
        window.validationInitialized = true;
    }
});