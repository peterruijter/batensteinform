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
        "payment_acknowledgment": "' . Text::_('COM_BATENSTEINFORM_PAYMENT_ACKNOWLEDGMENT_LABEL') . '",
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
        "COM_BATENSTEINFORM_ERROR_PHONE_REQUIRED": `' . Text::_('COM_BATENSTEINFORM_ERROR_PHONE_REQUIRED') . '`,
        "COM_BATENSTEINFORM_ERROR_PHONE_INVALID_CHARS": `' . Text::_('COM_BATENSTEINFORM_ERROR_PHONE_INVALID_CHARS') . '`,
        "COM_BATENSTEINFORM_ERROR_PHONE_INVALID_FORMAT": `' . Text::_('COM_BATENSTEINFORM_ERROR_PHONE_INVALID_FORMAT') . '`,
        "COM_BATENSTEINFORM_PHONE_NUMBER_LABEL": `' . Text::_('COM_BATENSTEINFORM_PHONE_NUMBER_LABEL') . '`,
        "COM_BATENSTEINFORM_ERROR_EMAIL_REQUIRED": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_REQUIRED') . '`,
        "COM_BATENSTEINFORM_ERROR_EMAIL_SINGLE_AT": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_SINGLE_AT') . '`,
        "COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_EMPTY": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_EMPTY') . '`,
        "COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_TOO_LONG": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_TOO_LONG') . '`,
        "COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_INVALID_CHARS": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_INVALID_CHARS') . '`,
        "COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_DOT_POSITION": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_DOT_POSITION') . '`,
        "COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_CONSECUTIVE_DOTS": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_LOCAL_CONSECUTIVE_DOTS') . '`,
        "COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_EMPTY": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_EMPTY') . '`,
        "COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_TOO_LONG": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_TOO_LONG') . '`,
        "COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_NO_DOT": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_NO_DOT') . '`,
        "COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_INVALID_POSITION": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_INVALID_POSITION') . '`,
        "COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_EMPTY_LABEL": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_EMPTY_LABEL') . '`,
        "COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_LABEL_TOO_LONG": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_LABEL_TOO_LONG') . '`,
        "COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_INVALID_CHARS": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_INVALID_CHARS') . '`,
        "COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_LABEL_HYPHEN": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_DOMAIN_LABEL_HYPHEN') . '`,
        "COM_BATENSTEINFORM_ERROR_EMAIL_TLD_INVALID": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_TLD_INVALID') . '`,
        "COM_BATENSTEINFORM_ERROR_EMAIL_TYPO_SUGGESTION": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMAIL_TYPO_SUGGESTION') . '`,
        "COM_BATENSTEINFORM_EMAIL_ADDRESS_LABEL": `' . Text::_('COM_BATENSTEINFORM_EMAIL_ADDRESS_LABEL') . '`,
        "COM_BATENSTEINFORM_FORM_REQUIRED_FIELDS": `' . Text::_('COM_BATENSTEINFORM_FORM_REQUIRED_FIELDS') . '`,
        "COM_BATENSTEINFORM_ERROR_EMERGENCY_CONSENT": `' . Text::_('COM_BATENSTEINFORM_ERROR_EMERGENCY_CONSENT') . '`,
        "COM_BATENSTEINFORM_IBAN_LABEL": `' . Text::_('COM_BATENSTEINFORM_IBAN_LABEL') . '`,
        "COM_BATENSTEINFORM_IBAN_ERROR_INVALID_FORMAT": `' . Text::_('COM_BATENSTEINFORM_IBAN_ERROR_INVALID_FORMAT') . '`,
        "COM_BATENSTEINFORM_SECTION_ALERT_DOES_NOT_MATCH": `' . Text::_('COM_BATENSTEINFORM_SECTION_ALERT_DOES_NOT_MATCH') . '`,
        "COM_BATENSTEINFORM_SECTION_ALERT_PREFIX": `' . Text::_('COM_BATENSTEINFORM_SECTION_ALERT_PREFIX') . '`,
        "COM_BATENSTEINFORM_SECTION_ALERT_NO_COMBINATION": `' . Text::_('COM_BATENSTEINFORM_SECTION_ALERT_NO_COMBINATION') . '`,
        "COM_BATENSTEINFORM_SECTION_WELPEN_ALERT": `' . Text::_('COM_BATENSTEINFORM_SECTION_WELPEN_ALERT') . '`,
        "COM_BATENSTEINFORM_SECTION_SCOUTS_ALERT": `' . Text::_('COM_BATENSTEINFORM_SECTION_SCOUTS_ALERT') . '`,
        "COM_BATENSTEINFORM_SECTION_EXPLORERS_ALERT": `' . Text::_('COM_BATENSTEINFORM_SECTION_EXPLORERS_ALERT') . '`,
        "COM_BATENSTEINFORM_SECTION_STAM_ALERT": `' . Text::_('COM_BATENSTEINFORM_SECTION_STAM_ALERT') . '`,
        "COM_BATENSTEINFORM_ERROR_PAYMENT_ACKNOWLEDGMENT": `' . Text::_('COM_BATENSTEINFORM_ERROR_PAYMENT_ACKNOWLEDGMENT_REQUIRED') . '`,
    };

    // Make translations globally available
    window.VALIDATION_TRANSLATIONS = VALIDATION_TRANSLATIONS;

    // Navigation URLs for page switching
    var NEXT_PAGE_URL = "' . Route::_('index.php?option=com_batensteinform&view=form') . '";
    var CANCEL_URL = "' . Route::_('index.php?option=com_batensteinform&task=form.cancel') . '";
');
?>

<div class="<?php echo $formId; ?><?php echo $this->pageclass_sfx; ?>">
    <div class="page-header">
        <h1>
            <?php echo Text::_('COM_BATENSTEINFORM_FORM_TITLE'); ?>
        </h1>
        <?php if ($currentPage == 1): ?>
        <p class="lead"><?php echo Text::_('COM_BATENSTEINFORM_FORM_INTRO'); ?></p>
        <?php endif; ?>

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