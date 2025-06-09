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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
?>

<div class="batenstein-confirmation">
    <div class="confirmation-icon">
        <i class="icon-check"></i>
    </div>
    
    <h1><?php echo Text::_('COM_BATENSTEINFORM_CONFIRMATION_TITLE'); ?></h1>
    
    <p><?php echo Text::_('COM_BATENSTEINFORM_CONFIRMATION_DETAILS'); ?></p>
    
    <a href="/" class="back-home-btn">
        <i class="icon-home"></i> <?php echo Text::_('COM_BATENSTEINFORM_BACK_TO_HOME'); ?>
    </a>
</div>
