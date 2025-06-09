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

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * HTML View class for the BatensteinForm Component
 */
class BatensteinFormViewForm extends HtmlView
{
    /**
     * The form object
     *
     * @var    JForm
     */
    protected $form;

    /**
     * The item object
     *
     * @var    JObject
     */
    protected $item;

    /**
     * The model state
     *
     * @var    JObject
     */
    protected $state;

    /**
     * Display the Batenstein registration form view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Get the form
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');
        
        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors), 500);
        }
        
        // Add scripts and styles directly in display method
        $this->addDocumentAssets();
        
        // Display the view
        parent::display($tpl);
    }
    
    /**
     * Method to add document assets (CSS/JS)
     *
     * @return void
     */
    protected function addDocumentAssets()
    {
        $document = Factory::getDocument();
        $document->setTitle(Text::_('COM_BATENSTEINFORM_FORM_TITLE'));
        
        // Add styling and validation scripts
        $document->addStyleSheet('/media/com_batensteinform/css/style.css');
        $document->addScript('/media/com_batensteinform/js/validation.js');
    }
}