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
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;

/**
 * Script file of BatensteinForm component
 */
class Com_BatensteinformInstallerScript
{
    /**
     * Constructor
     *
     * @param   InstallerAdapter  $adapter  The object responsible for running this script
     */
    public function __construct(InstallerAdapter $adapter) {}

    /**
     * Method to install the component
     *
     * @param   InstallerAdapter  $adapter  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function install(InstallerAdapter $adapter)
    {
        $this->createFolder();
        
        echo '<p>' . Text::_('COM_BATENSTEINFORM_POSTFLIGHT_INSTALL_TEXT') . '</p>';
        
        return true;
    }

    /**
     * Method to uninstall the component
     *
     * @param   InstallerAdapter  $adapter  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function uninstall(InstallerAdapter $adapter) 
    {
        echo '<p>' . Text::_('COM_BATENSTEINFORM_UNINSTALL_TEXT') . '</p>';
        
        return true;
    }

    /**
     * Method to update the component
     *
     * @param   InstallerAdapter  $adapter  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function update(InstallerAdapter $adapter) 
    {
        $this->createFolder();
        
        echo '<p>' . Text::_('COM_BATENSTEINFORM_POSTFLIGHT_UPDATE_TEXT') . '</p>';
        
        return true;
    }

    /**
     * Method to run before an install/update/uninstall method
     *
     * @param   string            $type     The type of change (install, update or discover_install)
     * @param   InstallerAdapter  $adapter  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function preflight($type, InstallerAdapter $adapter) 
    {
        // Check minimum Joomla version
        $jversion = new JVersion();
        
        if (version_compare($jversion->getShortVersion(), '3.9.0', 'lt')) {
            $adapter->getParent()->abort(
                Text::_('COM_BATENSTEINFORM_INSTALLER_MINIMUM_JOOMLA_VERSION')
            );
            
            return false;
        }
        
        return true;
    }

    /**
     * Method to run after an install/update/uninstall method
     *
     * @param   string            $type     The type of change (install, update or discover_install)
     * @param   InstallerAdapter  $adapter  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function postflight($type, InstallerAdapter $adapter) 
    {
        // Display post-installation message
        if ($type == 'install') {
            echo '<h2>Scouting Batenstein Inschrijfformulier geïnstalleerd!</h2>';
            echo '<p>Het formulier is nu beschikbaar op uw website. U kunt inschrijvingen beheren via Componenten > Batenstein Inschrijvingen in het beheergedeelte.</p>';
        } elseif ($type == 'update') {
            echo '<h2>Scouting Batenstein Inschrijfformulier bijgewerkt!</h2>';
            echo '<p>Het component is succesvol bijgewerkt naar de nieuwste versie.</p>';
        }
        
        return true;
    }
    
    /**
     * Method to create necessary folders
     *
     * @return  void
     */
    private function createFolder()
    {
        // Create media folder if it doesn't exist
        $mediaFolder = JPATH_ROOT . '/media/com_batensteinform';
        
        if (!Folder::exists($mediaFolder)) {
            Folder::create($mediaFolder);
        }
        
        // Create subfolders
        $folders = array(
            $mediaFolder . '/css',
            $mediaFolder . '/js',
            $mediaFolder . '/images'
        );
        
        foreach ($folders as $folder) {
            if (!Folder::exists($folder)) {
                Folder::create($folder);
            }
        }
        
        // Copy CSS and JS files
        $this->copyFiles();
    }
    
    /**
     * Method to copy CSS and JS files to media folder
     *
     * @return  void
     */
    private function copyFiles()
    {
        // The files should already be copied by the installer
        // This method can be used for any additional file operations if needed
        
        // Check if scouting logo exists in root images folder, if not copy it
        $logoSource = JPATH_ROOT . '/media/com_batensteinform/images/scouting_logo.png';
        $logoDestination = JPATH_ROOT . '/images/scouting_logo.png';
        
        if (File::exists($logoSource) && !File::exists($logoDestination)) {
            File::copy($logoSource, $logoDestination);
        }
    }
}