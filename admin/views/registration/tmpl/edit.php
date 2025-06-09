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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// Load jQuery for backward compatibility (optional - see vanilla JS solution below)
HTMLHelper::_('jquery.framework');

// Load behaviors with Joomla 5 compatibility
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

// Load Bootstrap if needed
HTMLHelper::_('bootstrap.framework');

$app = Factory::getApplication();
$input = $app->input;
?>

<script type="text/javascript">
    // Joomla 5 compatible form validation setup
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Joomla 5 form validation if available
        if (typeof document.formvalidator !== 'undefined' && document.formvalidator.attachToForm) {
            document.formvalidator.attachToForm(document.getElementById('registration-form'));
        }
    });
    
    // Enhanced form submission function compatible with Joomla 5
    Joomla.submitbutton = function(task) {
        var form = document.getElementById('registration-form');
        
        if (task === 'registration.cancel') {
            Joomla.submitform(task, form);
        } else {
            // Use Joomla's built-in validation if available, otherwise fallback to custom
            var isValid = false;
            
            if (typeof document.formvalidator !== 'undefined' && document.formvalidator.isValid) {
                isValid = document.formvalidator.isValid(form);
            } else {
                isValid = validateForm(form);
            }
            
            if (isValid) {
                Joomla.submitform(task, form);
            } else {
                alert('<?php echo Text::_('COM_BATENSTEINFORM_FORM_INVALID_FIELDS', true); ?>');
            }
        }
    };
    
    // Custom form validation function
    function validateForm(form) {
        var isValid = true;
        var requiredFields = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        requiredFields.forEach(function(field) {
            if (!field.value || field.value.trim() === '') {
                var label = field.getAttribute('data-label') || field.getAttribute('name') || 'Field';
                alert('<?php echo Text::_('COM_BATENSTEINFORM_FORM_REQUIRED_FIELDS', true); ?>' + ' ' + label);
                field.focus();
                isValid = false;
                return false;
            }
        });
        
        return isValid;
    }
    
    // Modern JavaScript solution (replaces jQuery)
    document.addEventListener('DOMContentLoaded', function() {
        
        // Function to toggle conditional fields based on Yes/No selections
        function toggleConditionalField(triggerField, targetField, showOnValue) {
            var trigger = document.querySelector('[name="jform[' + triggerField + ']"]');
            var target = document.querySelector('[name="jform[' + targetField + ']"]');
            
            if (!trigger || !target) return;
            
            // Find the closest control-group container
            var targetContainer = target.closest('.control-group');
            if (!targetContainer) {
                // Fallback - create a container if it doesn't exist
                targetContainer = target.parentElement;
            }
            
            function checkVisibility() {
                var selectedValue = trigger.value;
                if (selectedValue === showOnValue) {
                    targetContainer.style.display = '';
                } else {
                    targetContainer.style.display = 'none';
                    // Clear the field when hiding
                    target.value = '';
                }
            }
            
            // Initial check
            checkVisibility();
            
            // Listen for changes
            trigger.addEventListener('change', checkVisibility);
        }
        
        // Set up conditional field relationships
        toggleConditionalField('can_swim', 'swim_diplomas', 'Yes');
        toggleConditionalField('special_health_care', 'special_health_care_details', 'Yes');
        toggleConditionalField('medication', 'medication_details', 'Yes');
        toggleConditionalField('allergies', 'allergies_details', 'Yes');
        toggleConditionalField('diet', 'diet_details', 'Yes');
        
        // Scout section dependent fields visibility
        function toggleScoutSectionFields() {
            var scoutSectionField = document.querySelector('[name="jform[scout_section]"]');
            if (!scoutSectionField) return;
            
            var scoutSection = scoutSectionField.value;
            
            // Get field containers
            var personalContactFields = [
                document.querySelector('[name="jform[phone_number]"]'),
                document.querySelector('[name="jform[email_address]"]')
            ].filter(Boolean).map(field => field.closest('.control-group')).filter(Boolean);
            
            var parent1Fields = [
                document.querySelector('[name="jform[parent1_name]"]'),
                document.querySelector('[name="jform[parent1_phone_number]"]'),
                document.querySelector('[name="jform[parent1_email_address]"]')
            ].filter(Boolean).map(field => field.closest('.control-group')).filter(Boolean);
            
            var parent2Fields = [
                document.querySelector('[name="jform[parent2_name]"]'),
                document.querySelector('[name="jform[parent2_phone_number]"]'),
                document.querySelector('[name="jform[parent2_email_address]"]')
            ].filter(Boolean).map(field => field.closest('.control-group')).filter(Boolean);
            
            // Helper function to show/hide fields
            function showFields(fields) {
                fields.forEach(function(field) {
                    if (field) field.style.display = '';
                });
            }
            
            function hideFields(fields) {
                fields.forEach(function(field) {
                    if (field) field.style.display = 'none';
                });
            }
            
            // Show/hide based on scout section
            if (scoutSection === 'welpen') {
                // Welpen: only parent contact, no personal contact
                hideFields(personalContactFields);
                showFields(parent1Fields);
                showFields(parent2Fields);
            } else if (scoutSection === 'scouts' || scoutSection === 'explorers') {
                // Scouts/Explorers: both personal and parent contact
                showFields(personalContactFields);
                showFields(parent1Fields);
                showFields(parent2Fields);
            } else {
                // Stam/Sikas/Plus: only personal contact, no parent contact
                showFields(personalContactFields);
                hideFields(parent1Fields);
                hideFields(parent2Fields);
            }
        }
        
        // Initial check and change listener for scout section
        toggleScoutSectionFields();
        var scoutSectionField = document.querySelector('[name="jform[scout_section]"]');
        if (scoutSectionField) {
            scoutSectionField.addEventListener('change', toggleScoutSectionFields);
        }
    });
</script>

<form action="<?php echo Route::_('index.php?option=com_batensteinform&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="registration-form" class="form-validate">

    <div class="form-horizontal">
        <!-- Personal Information -->
        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_BATENSTEINFORM_FORM_PERSONAL_DETAILS'); ?></legend>
            
            <div class="row-fluid">
                <div class="span6">
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('first_name'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('first_name'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('calling_name'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('calling_name'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('name_prefix'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('name_prefix'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('last_name'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('last_name'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('address'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('address'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('postal_code_city'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('postal_code_city'); ?></div>
                    </div>
                </div>
                <div class="span6">
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('birth_date'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('birth_date'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('birth_place'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('birth_place'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('scout_section'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('scout_section'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('phone_number'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('phone_number'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('email_address'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('email_address'); ?></div>
                    </div>
                </div>
            </div>
        </fieldset>

        <!-- Parent/Guardian Contact Details -->
        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_BATENSTEINFORM_FORM_PARENT_DETAILS'); ?></legend>
            
            <div class="row-fluid">
                <div class="span6">
                    <h4><?php echo Text::_('COM_BATENSTEINFORM_FORM_PARENT_DETAILS_1'); ?></h4>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('parent1_name'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('parent1_name'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('parent1_phone_number'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('parent1_phone_number'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('parent1_email_address'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('parent1_email_address'); ?></div>
                    </div>
                </div>
                <div class="span6">
                    <h4><?php echo Text::_('COM_BATENSTEINFORM_FORM_PARENT_DETAILS_2'); ?></h4>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('parent2_name'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('parent2_name'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('parent2_phone_number'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('parent2_phone_number'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('parent2_email_address'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('parent2_email_address'); ?></div>
                    </div>
                </div>
            </div>
        </fieldset>

        <!-- Payment Information -->
        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_BATENSTEINFORM_FORM_PAYMENT_DETAILS'); ?></legend>
            
            <div class="row-fluid">
                <div class="span6">
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('iban'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('iban'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('account_name'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('account_name'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('sign_date'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('sign_date'); ?></div>
                    </div>
                </div>
                <div class="span6">
                    <h4><?php echo Text::_('COM_BATENSTEINFORM_IMAGES_LABEL'); ?></h4>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('images_website'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('images_website'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('images_social'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('images_social'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('images_newspaper'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('images_newspaper'); ?></div>
                    </div>
                </div>
            </div>
        </fieldset>

        <!-- Health Information - Swimming -->
        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_BATENSTEINFORM_HEALTH_SWIMMING_LABEL'); ?></legend>
            
            <div class="row-fluid">
                <div class="span6">
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('can_swim'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('can_swim'); ?></div>
                    </div>
                </div>
                <div class="span6">
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('swim_diplomas'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('swim_diplomas'); ?></div>
                    </div>
                </div>
            </div>
        </fieldset>

        <!-- Emergency Contact -->
        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_BATENSTEINFORM_HEALTH_EMERGENCY_LABEL'); ?></legend>
            
            <div class="row-fluid">
                <div class="span4">
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('emergency_contact_name'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('emergency_contact_name'); ?></div>
                    </div>
                </div>
                <div class="span4">
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('emergency_contact_relation'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('emergency_contact_relation'); ?></div>
                    </div>
                </div>
                <div class="span4">
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('emergency_contact_phone'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('emergency_contact_phone'); ?></div>
                    </div>
                </div>
            </div>
        </fieldset>

        <!-- Medical Information -->
        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_BATENSTEINFORM_HEALTH_MEDICAL_LABEL'); ?></legend>
            
            <div class="row-fluid">
                <div class="span6">
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('special_health_care'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('special_health_care'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('special_health_care_details'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('special_health_care_details'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('medication'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('medication'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('medication_details'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('medication_details'); ?></div>
                    </div>
                </div>
                <div class="span6">
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('allergies'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('allergies'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('allergies_details'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('allergies_details'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('diet'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('diet'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('diet_details'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('diet_details'); ?></div>
                    </div>
                </div>
            </div>
        </fieldset>

        <!-- Insurance Information -->
        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_BATENSTEINFORM_HEALTH_INSURANCE_LABEL'); ?></legend>
            
            <div class="row-fluid">
                <div class="span6">
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('health_insurance'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('health_insurance'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('policy_number'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('policy_number'); ?></div>
                    </div>
                </div>
                <div class="span6">
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('emergency_treatment_consent'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('emergency_treatment_consent'); ?></div>
                    </div>
                </div>
            </div>
        </fieldset>

        <!-- Doctors Information -->
        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_BATENSTEINFORM_DOCTORS_LABEL'); ?></legend>
            
            <div class="row-fluid">
                <div class="span6">
                    <h4><?php echo Text::_('COM_BATENSTEINFORM_GP_TITLE'); ?></h4>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('gp_name'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('gp_name'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('gp_address'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('gp_address'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('gp_phone'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('gp_phone'); ?></div>
                    </div>
                </div>
                <div class="span6">
                    <h4><?php echo Text::_('COM_BATENSTEINFORM_DENTIST_TITLE'); ?></h4>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('dentist_name'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('dentist_name'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('dentist_address'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('dentist_address'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('dentist_phone'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('dentist_phone'); ?></div>
                    </div>
                </div>
            </div>
        </fieldset>

        <!-- Additional Comments -->
        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_BATENSTEINFORM_FORM_ADDITIONAL_DETAILS'); ?></legend>
            
            <div class="control-group">
                <div class="control-label"><?php echo $this->form->getLabel('comments'); ?></div>
                <div class="controls"><?php echo $this->form->getInput('comments'); ?></div>
            </div>
            
            <?php if ($this->item->id) : ?>
            <div class="control-group">
                <div class="control-label"><?php echo $this->form->getLabel('created_at'); ?></div>
                <div class="controls"><?php echo $this->form->getInput('created_at'); ?></div>
            </div>
            <?php endif; ?>
        </fieldset>
        
        <?php echo $this->form->getInput('id'); ?>
        <input type="hidden" name="task" value="" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>