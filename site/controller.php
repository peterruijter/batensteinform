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

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

/**
 * General Controller of BatensteinForm component
 */
class BatensteinFormController extends BaseController
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $default_view = 'form';

    /**
     * Method to display a view
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types
     *
     * @return  BatensteinFormController  This object to support chaining
     */
    public function display($cachable = false, $urlparams = array())
    {
        // Set the default view if not set
        $view = $this->input->get('view', $this->default_view);
        $this->input->set('view', $view);

        parent::display($cachable, $urlparams);

        return $this;
    }
}
