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
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;

/**
 * BatensteinForm Model
 */
class BatensteinFormModelForm extends FormModel
{
    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed    Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        // Return empty object since this is a new form
        return new stdClass();
    }

    /**
     * Method to get the form
     *
     * @param   array    $data      Data for the form
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not
     *
     * @return  mixed  A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        Log::add('getForm called with data: ' . print_r($data, true), Log::INFO, 'com_batensteinform');

        // Get the form
        $form = $this->loadForm(
            'com_batensteinform.form',
            'batenstein-form',
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
     * Method to get the data that should be injected in the form
     *
     * @return  mixed  The data for the form
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data
        $data = Factory::getApplication()->getUserState(
            'com_batensteinform.edit.form.data',
            array()
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        // Convert array to object if needed for consistent handling
        if (is_array($data)) {
            $data = (object) $data;
        }

        // Set today's date as default for sign_date
        if (empty($data->sign_date)) {
            $data->sign_date = date('d-m-Y');
        }

        return $data;
    }

    /**
     * Method to save the form data
     *
     * @param   array  $data  The form data
     *
     * @return  boolean  True on success
     */
    public function save($data)
    {
        $app = Factory::getApplication();

        // Debug: Check data structure
        Log::add('Save method received data: ' . print_r($data, true), Log::DEBUG, 'com_batensteinform');

        // Ensure we have valid data
        if (empty($data)) {
            Log::add('No data received in save method', Log::ERROR, 'com_batensteinform');
            return false;
        }

        // Clean and validate form data
        $filter = new InputFilter();
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = $filter->clean($value, 'string');
            }
        }

        // Store data in database
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Create columns array with all new fields
        $columns = array(
            // Personal Information
            'first_name',
            'calling_name',
            'name_prefix',
            'last_name',
            'address',
            'postal_code_city',
            'birth_date',
            'birth_place',
            'scout_section',

            // Personal Contact (for Scouts/Explorers)
            'phone_number',
            'email_address',

            // Parent/Guardian Contact Details
            'parent1_name',
            'parent1_phone_number',
            'parent1_email_address',
            'parent2_name',
            'parent2_phone_number',
            'parent2_email_address',

            // Payment Information
            'iban',
            'account_name',
            'sign_date',
            'payment_acknowledgment',

            // Image/Video Permissions
            'images_website',
            'images_social',
            'images_newspaper',

            // Health Information
            'can_swim',
            'swim_diplomas',
            'emergency_contact_name',
            'emergency_contact_relation',
            'emergency_contact_phone',
            'special_health_care',
            'special_health_care_details',
            'medication',
            'medication_details',
            'allergies',
            'allergies_details',
            'diet',
            'diet_details',
            'health_insurance',
            'policy_number',
            'gp_name',
            'gp_address',
            'gp_phone',
            'dentist_name',
            'dentist_address',
            'dentist_phone',
            'emergency_treatment_consent',

            // Additional Information
            'comments',

            // System field
            'created_at'
        );

        // Create values array - handle missing keys gracefully
        $values = array();
        foreach ($columns as $column) {
            if ($column === 'created_at') {
                $values[] = 'NOW()';
            } elseif ($column === 'sign_date' && !empty($data[$column])) {
                // Convert d-m-Y to Y-m-d for database
                $date = DateTime::createFromFormat('d-m-Y', $data[$column]);
                if ($date) {
                    $values[] = $db->quote($date->format('Y-m-d'));
                } else {
                    $values[] = $db->quote(date('Y-m-d'));
                }
            } elseif ($column === 'birth_date' && !empty($data[$column])) {
                // Convert d-m-Y to Y-m-d for database
                $date = DateTime::createFromFormat('d-m-Y', $data[$column]);
                if ($date) {
                    $values[] = $db->quote($date->format('Y-m-d'));
                } else {
                    $values[] = 'NULL';
                }
            } else {
                // Check if the field exists in data
                $fieldValue = isset($data[$column]) ? $data[$column] : '';
                $values[] = $db->quote($fieldValue);

                // Debug missing fields
                if (!isset($data[$column])) {
                    Log::add("Field missing from data: $column", Log::WARNING, 'com_batensteinform');
                } else {
                    Log::add("Field $column has value: " . $data[$column], Log::INFO, 'com_batensteinform');
                }
            }
        }

        // Prepare the insert query
        $query
            ->insert($db->quoteName('#__batenstein_registrations'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));


        $db->setQuery($query);
        try {
            $result = $db->execute();
            Log::add('Database insert result: ' . ($result ? 'SUCCESS' : 'FAILED'), Log::INFO, 'com_batensteinform');

            if ($result) {
                $this->sendEmails($data);
                return true;
            } else {
                Log::add('Database execute returned false', Log::ERROR, 'com_batensteinform');
                return false;
            }
        } catch (Exception $e) {
            Log::add('Database error: ' . $e->getMessage(), Log::ERROR, 'com_batensteinform');
            $app->enqueueMessage($e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Method to send multilingual emails
     *
     * @param   array  $data  The form data
     *
     * @return  boolean  True on success
     */
    protected function sendEmails($data)
    {
        $app = Factory::getApplication();
        $mailer = Factory::getMailer();

        // Get component parameters for email language configuration
        $params = ComponentHelper::getParams('com_batensteinform');
        $emailLanguage = $params->get('email_language', 'en-GB'); // Default to English

        // Load the correct language for emails
        $language = Factory::getLanguage();
        $currentTag = $language->getTag();

        // Load the component language file for the specified email language
        $language->load('com_batensteinform', JPATH_SITE, $emailLanguage, true);

        // Set sender
        $config = Factory::getConfig();
        $sender = array(
            $config->get('mailfrom'),
            $config->get('fromname')
        );
        $mailer->setSender($sender);

        // Set subject using language constants
        $subject = Text::sprintf(
            'COM_BATENSTEINFORM_EMAIL_SUBJECT',
            ucfirst($data['scout_section']),
            $data['calling_name'],
            ($data['name_prefix'] ? $data['name_prefix'] . ' ' : ''),
            $data['last_name']
        );
        $mailer->setSubject($subject);

        // Set recipients from component configuration
        $recipients = array();
        $recipientList = $params->get('recipient_emails', 'secretaris@batenstein.nl,penningmeester@batenstein.nl');

        // Split on comma or new line and trim spaces
        $recipients = preg_split('/[\s,]+/', $recipientList, -1, PREG_SPLIT_NO_EMPTY);
        $recipients = array_map('trim', $recipients);

        // Add scout section-specific email if configured
        $sectionEmails = $params->get('section_emails', 1); // Enable/disable section emails
        if ($sectionEmails && !empty($data['scout_section'])) {
            $sectionEmail = strtolower($data['scout_section']) . '@batenstein.nl';
            $recipients[] = $sectionEmail;
        }

        // Add appropriate email addresses based on scout section
        if (!empty($data['scout_section'])) {
            $scoutSection = strtolower($data['scout_section']);
            
            // For younger sections (welpen and scouts), add parent email addresses
            if ($scoutSection === 'welpen' || $scoutSection === 'scouts') {
                // Add parent1 email if provided
                if (!empty($data['parent1_email_address'])) {
                    $recipients[] = $data['parent1_email_address'];
                }
                // Add parent2 email if provided
                if (!empty($data['parent2_email_address'])) {
                    $recipients[] = $data['parent2_email_address'];
                }
            } else {
                // For older sections (explorers, stam, plus, sikas), add scout's own email
                if (!empty($data['email_address'])) {
                    $recipients[] = $data['email_address'];
                }
            }
        }

        $mailer->addRecipient($recipients);

        // Create email body
        $body = $this->createEmailBody($data); 
        $mailer->setBody($body);
        $mailer->isHTML(true);

        try {
            $mailer->Send();

            // Restore original language
            $language->load('com_batensteinform', JPATH_SITE, $currentTag, true);

            return true;
        } catch (Exception $e) {
            // Restore original language
            $language->load('com_batensteinform', JPATH_SITE, $currentTag, true);
            $app->enqueueMessage(Text::sprintf('COM_BATENSTEINFORM_EMAIL_ERROR', $e->getMessage()), 'error');
            return false;
        }
    }

    function getMailerRecipients($mailer)
    {
        try {
            $recipients = array();

            // Try different methods depending on Joomla version
            if (method_exists($mailer, 'getRecipients')) {
                $recipients = $mailer->getRecipients();
            } elseif (method_exists($mailer, 'getAllRecipients')) {
                $recipients = $mailer->getAllRecipients();
            } else {
                // Try to access the underlying PHPMailer object
                if (property_exists($mailer, 'phpmailer')) {
                    $phpmailer = $mailer->phpmailer;

                    if (method_exists($phpmailer, 'getToAddresses')) {
                        $recipients = array_merge(
                            $phpmailer->getToAddresses(),
                            $phpmailer->getCCAddresses(),
                            $phpmailer->getBCCAddresses()
                        );
                    }
                }
            }

            return $recipients;
        } catch (Exception $e) {
            error_log('Could not get recipients via reflection: ' . $e->getMessage());
        }

        return array();
    }


    /**
     * Method to create multilingual email body from form data
     *
     * @param   array  $data  The form data
     *
     * @return  string  The email body
     */
    protected function createEmailBody($data)
    {
        $html = '<h1>' . Text::sprintf('COM_BATENSTEINFORM_EMAIL_TITLE', ucfirst($data['scout_section'])) . '</h1>';

        // Personal Information Section
        $html .= '<h2>' . Text::_('COM_BATENSTEINFORM_EMAIL_PERSONAL_INFO') . '</h2>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_FIRST_NAMES_LABEL') . ':</strong> ' . $data['first_name'] . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_CALLING_NAME_LABEL') . ':</strong> ' . $data['calling_name'] . '</p>';
        if (!empty($data['name_prefix'])) {
            $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_NAME_PREFIX_LABEL') . ':</strong> ' . $data['name_prefix'] . '</p>';
        }
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_LAST_NAME_LABEL') . ':</strong> ' . $data['last_name'] . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_ADDRESS_LABEL') . ':</strong> ' . $data['address'] . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_POSTAL_CODE_CITY_LABEL') . ':</strong> ' . $data['postal_code_city'] . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_BIRTH_DATE_LABEL') . ':</strong> ' . $data['birth_date'] . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_BIRTH_PLACE_LABEL') . ':</strong> ' . $data['birth_place'] . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_SCOUT_SECTION_LABEL') . ':</strong> ' . Text::_('COM_BATENSTEINFORM_' . strtoupper($data['scout_section'])) . '</p>';

        // Personal Contact Details (for Scouts and Explorers)
        if (!empty($data['phone_number']) || !empty($data['email_address'])) {
            $html .= '<h2>' . Text::_('COM_BATENSTEINFORM_PERSONAL_CONTACT_LABEL') . '</h2>';
            if (!empty($data['phone_number'])) {
                $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_PHONE_NUMBER_LABEL') . ':</strong> ' . $data['phone_number'] . '</p>';
            }
            if (!empty($data['email_address'])) {
                $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_EMAIL_ADDRESS_LABEL') . ':</strong> ' . $data['email_address'] . '</p>';
            }
        }

        // Parent/Guardian 1 Contact Details
        $html .= '<h2>' . Text::_('COM_BATENSTEINFORM_PARENT1_LABEL') . '</h2>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_PARENT1_NAME_LABEL') . ':</strong> ' . $data['parent1_name'] . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_PARENT1_PHONE_NUMBER_LABEL') . ':</strong> ' . $data['parent1_phone_number'] . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_PARENT1_EMAIL_ADDRESS_LABEL') . ':</strong> ' . $data['parent1_email_address'] . '</p>';

        // Parent/Guardian 2 Contact Details (if provided)
        if (!empty($data['parent2_name'])) {
            $html .= '<h2>' . Text::_('COM_BATENSTEINFORM_PARENT2_LABEL') . '</h2>';
            $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_PARENT2_NAME_LABEL') . ':</strong> ' . $data['parent2_name'] . '</p>';
            if (!empty($data['parent2_phone_number'])) {
                $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_PARENT2_PHONE_NUMBER_LABEL') . ':</strong> ' . $data['parent2_phone_number'] . '</p>';
            }
            if (!empty($data['parent2_email_address'])) {
                $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_PARENT2_EMAIL_ADDRESS_LABEL') . ':</strong> ' . $data['parent2_email_address'] . '</p>';
            }
        }

        // Payment Details
        $html .= '<h2>' . Text::_('COM_BATENSTEINFORM_PAYMENT_LABEL') . '</h2>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_IBAN_LABEL') . ':</strong> ' . $data['iban'] . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_ACCOUNT_NAME_LABEL') . ':</strong> ' . $data['account_name'] . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_SIGN_DATE_LABEL') . ':</strong> ' . $data['sign_date'] . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_PAYMENT_ACKNOWLEDGMENT_LABEL') . ':</strong> ' . Text::_('COM_BATENSTEINFORM_' . strtoupper($data['payment_acknowledgment'])) . '</p>';

        // Image/Video Permissions
        $html .= '<h2>' . Text::_('COM_BATENSTEINFORM_IMAGES_LABEL') . '</h2>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_IMAGES_WEBSITE_LABEL') . ':</strong> ' . Text::_('COM_BATENSTEINFORM_' . strtoupper($data['images_website'])) . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_IMAGES_SOCIAL_LABEL') . ':</strong> ' . Text::_('COM_BATENSTEINFORM_' . strtoupper($data['images_social'])) . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_IMAGES_NEWSPAPER_LABEL') . ':</strong> ' . Text::_('COM_BATENSTEINFORM_' . strtoupper($data['images_newspaper'])) . '</p>';

        // Health Information
        $html .= '<h2>' . Text::_('COM_BATENSTEINFORM_HEALTH_LABEL') . '</h2>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_CAN_SWIM_LABEL') . ':</strong> ' . Text::_('COM_BATENSTEINFORM_' . strtoupper($data['can_swim'])) . '</p>';
        if (!empty($data['swim_diplomas']) && $data['can_swim'] === 'Yes') {
            $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_SWIM_DIPLOMAS_LABEL') . ':</strong> ' . $data['swim_diplomas'] . '</p>';
        }

        // Emergency Contact
        $html .= '<h3>' . Text::_('COM_BATENSTEINFORM_EMAIL_EMERGENCY_CONTACT') . '</h3>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_EMERGENCY_CONTACT_NAME_LABEL') . ':</strong> ' . $data['emergency_contact_name'] . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_EMERGENCY_CONTACT_RELATION_LABEL') . ':</strong> ' . $data['emergency_contact_relation'] . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_EMERGENCY_CONTACT_PHONE_LABEL') . ':</strong> ' . $data['emergency_contact_phone'] . '</p>';

        // Medical Information
        $html .= '<h3>' . Text::_('COM_BATENSTEINFORM_EMAIL_MEDICAL_INFO') . '</h3>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_SPECIAL_HEALTH_CARE_LABEL') . ':</strong> ' . Text::_('COM_BATENSTEINFORM_' . strtoupper($data['special_health_care'])) . '</p>';
        if (!empty($data['special_health_care_details']) && $data['special_health_care'] === 'Yes') {
            $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_SPECIAL_HEALTH_CARE_DETAILS_LABEL') . ':</strong> ' . nl2br($data['special_health_care_details']) . '</p>';
        }

        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_MEDICATION_LABEL') . ':</strong> ' . Text::_('COM_BATENSTEINFORM_' . strtoupper($data['medication'])) . '</p>';
        if (!empty($data['medication_details']) && $data['medication'] === 'Yes') {
            $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_MEDICATION_DETAILS_LABEL') . ':</strong> ' . nl2br($data['medication_details']) . '</p>';
        }

        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_ALLERGIES_LABEL') . ':</strong> ' . Text::_('COM_BATENSTEINFORM_' . strtoupper($data['allergies'])) . '</p>';
        if (!empty($data['allergies_details']) && $data['allergies'] === 'Yes') {
            $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_ALLERGIES_DETAILS_LABEL') . ':</strong> ' . nl2br($data['allergies_details']) . '</p>';
        }

        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_DIET_LABEL') . ':</strong> ' . Text::_('COM_BATENSTEINFORM_' . strtoupper($data['diet'])) . '</p>';
        if (!empty($data['diet_details']) && $data['diet'] === 'Yes') {
            $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_DIET_DETAILS_LABEL') . ':</strong> ' . nl2br($data['diet_details']) . '</p>';
        }

        // Insurance and Medical Contacts
        $html .= '<h3>' . Text::_('COM_BATENSTEINFORM_EMAIL_INSURANCE_DOCTORS') . '</h3>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_HEALTH_INSURANCE_LABEL') . ':</strong> ' . $data['health_insurance'] . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_POLICY_NUMBER_LABEL') . ':</strong> ' . $data['policy_number'] . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_GP_NAME_LABEL') . ':</strong> ' . $data['gp_name'] . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_GP_ADDRESS_LABEL') . ':</strong> ' . $data['gp_address'] . '</p>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_GP_PHONE_LABEL') . ':</strong> ' . $data['gp_phone'] . '</p>';

        if (!empty($data['dentist_name'])) {
            $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_DENTIST_NAME_LABEL') . ':</strong> ' . $data['dentist_name'] . '</p>';
            if (!empty($data['dentist_address'])) {
                $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_DENTIST_ADDRESS_LABEL') . ':</strong> ' . $data['dentist_address'] . '</p>';
            }
            if (!empty($data['dentist_phone'])) {
                $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_DENTIST_PHONE_LABEL') . ':</strong> ' . $data['dentist_phone'] . '</p>';
            }
        }

        // Emergency Treatment Consent
        $html .= '<h3>' . Text::_('COM_BATENSTEINFORM_EMAIL_EMERGENCY_CONSENT') . '</h3>';
        $html .= '<p><strong>' . Text::_('COM_BATENSTEINFORM_EMERGENCY_TREATMENT_CONSENT_LABEL') . ':</strong> ' . Text::_('COM_BATENSTEINFORM_' . strtoupper($data['emergency_treatment_consent'])) . '</p>';

        // Additional Comments
        if (!empty($data['comments'])) {
            $html .= '<h2>' . Text::_('COM_BATENSTEINFORM_COMMENTS_LABEL') . '</h2>';
            $html .= '<p>' . nl2br($data['comments']) . '</p>';
        }

        return $html;
    }
}
