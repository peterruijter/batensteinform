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
use Joomla\CMS\Router\Route;

/**
 * HTML View class for the BatensteinForm Confirmation
 */
class BatensteinFormViewConfirmation extends HtmlView
{
    /**
     * Display the confirmation view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Get the application
        $app = Factory::getApplication();
        
        // Get any messages
        $this->message = $app->getMessageQueue();
        
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
        $document->setTitle(Text::_('COM_BATENSTEINFORM_CONFIRMATION_TITLE'));
        
        // Add styling
        $document->addStyleSheet('/media/com_batensteinform/css/style.css');
    }
}