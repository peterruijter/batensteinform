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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Load behaviors with compatibility checks for Joomla 5.3.1
if (method_exists('JHtml', '_')) {
    // Try to load multiselect
    try {
        if (JHtml::isRegistered('behavior.multiselect')) {
            JHtml::_('behavior.multiselect');
        }
    } catch (Exception $e) {
        // Ignore if behavior not found
    }
    
    // Try to load chosen
    try {
        if (JHtml::isRegistered('formbehavior.chosen')) {
            JHtml::_('formbehavior.chosen', 'select');
        }
    } catch (Exception $e) {
        // Ignore if behavior not found
    }
}

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$canOrder  = $user->authorise('core.edit.state', 'com_batensteinform');

$sortFields = $this->getSortFields();
?>

<style>
/* Custom styles for row selection and edit button highlighting */
.table tbody tr.selected {
    background-color: #d9edf7 !important;
}

.table tbody tr:hover {
    background-color: #f5f5f5;
    cursor: pointer;
}

.btn-toolbar .btn.highlighted {
    background-color: #5bc0de;
    border-color: #46b8da;
    color: #fff;
    box-shadow: 0 0 5px rgba(91, 192, 222, 0.5);
}

.btn-toolbar .btn.highlighted:hover {
    background-color: #339bb9;
    border-color: #2d8aa6;
}

.alert-selection {
    margin: 10px 0;
    padding: 8px 15px;
    background-color: #dff0d8;
    border: 1px solid #d6e9c6;
    color: #3c763d;
    border-radius: 4px;
}

/* Disabled button styles */
.btn.disabled, .btn[disabled] {
    opacity: 0.4;
    cursor: not-allowed !important;
    pointer-events: none;
}

a.disabled {
    opacity: 0.4;
    cursor: not-allowed !important;
    pointer-events: none;
    text-decoration: none;
}

/* Toolbar button states for Joomla 5.3.1 */
#toolbar .btn.btn-danger {
    background-color: #d9534f !important;
    border-color: #d43f3a !important;
    color: #fff !important;
}

#toolbar .btn.highlighted {
    background-color: #5bc0de !important;
    border-color: #46b8da !important;
    color: #fff !important;
    box-shadow: 0 0 5px rgba(91, 192, 222, 0.5) !important;
}
</style>

<script type="text/javascript">
// Joomla 5.3.1 Compatible Button Management with Native Confirmations Only
document.addEventListener('DOMContentLoaded', function() {
    let selectedItems = [];
    
    /**
     * Update the selected items array based on checked checkboxes
     */
    function updateSelectedItems() {
        selectedItems = [];
        const checkboxes = document.querySelectorAll('input[name="cid[]"]:checked');
        checkboxes.forEach(cb => selectedItems.push(cb.value));
        
        updateButtonStates();
        updateBoxChecked();
    }
    
    /**
     * Update button states based on selection
     */
    function updateButtonStates() {
        // Joomla 5 uses custom elements and different structure
        // Try multiple approaches to find buttons
        
        // Method 1: Joomla 5 custom elements
        const editButton = document.querySelector('joomla-toolbar-button[task="registration.edit"]') ||
                          document.querySelector('[data-task="registration.edit"]') ||
                          document.querySelector('button[onclick*="registration.edit"]');
        
        const deleteButton = document.querySelector('joomla-toolbar-button[task="registrations.delete"]') ||
                            document.querySelector('[data-task="registrations.delete"]') ||
                            document.querySelector('button[onclick*="registrations.delete"]');
        
        // Handle Edit Button
        if (editButton) {
            const editBtn = editButton.tagName === 'JOOMLA-TOOLBAR-BUTTON' ? 
                           editButton.querySelector('button') : editButton;
            
            if (editBtn) {
                if (selectedItems.length === 1) {
                    editBtn.disabled = false;
                    editBtn.classList.add('btn-success');
                    editBtn.style.opacity = '1';
                    
                    // Override click handler for single selection
                    editBtn.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        window.location.href = `index.php?option=com_batensteinform&task=registration.edit&id=${selectedItems[0]}`;
                        return false;
                    };
                    
                    // Also set the task attribute if it's a Joomla custom element
                    if (editButton.tagName === 'JOOMLA-TOOLBAR-BUTTON') {
                        editButton.setAttribute('task', `registration.edit&id=${selectedItems[0]}`);
                    }
                } else {
                    editBtn.disabled = selectedItems.length === 0;
                    editBtn.classList.remove('btn-success');
                    editBtn.style.opacity = selectedItems.length === 0 ? '0.4' : '1';
                    
                    // Show alert for multiple selections
                    if (selectedItems.length > 1) {
                        editBtn.onclick = function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            alert('<?php echo JText::_("COM_BATENSTEINFORM_SELECT_SINGLE_ITEM_TO_EDIT"); ?>');
                            return false;
                        };
                    }
                }
            }
        }
        
        // Handle Delete Button
        if (deleteButton) {
            const deleteBtn = deleteButton.tagName === 'JOOMLA-TOOLBAR-BUTTON' ? 
                             deleteButton.querySelector('button') : deleteButton;
            
            if (deleteBtn) {
                deleteBtn.disabled = selectedItems.length === 0;
                deleteBtn.style.opacity = selectedItems.length === 0 ? '0.4' : '1';
                
                if (selectedItems.length > 0) {
                    deleteBtn.classList.add('btn-danger');
                    deleteBtn.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Use native confirmation only
                        const message = selectedItems.length === 1 ? 
                            '<?php echo JText::_("COM_BATENSTEINFORM_CONFIRM_DELETE_SINGLE"); ?>' :
                            '<?php echo JText::_("COM_BATENSTEINFORM_CONFIRM_DELETE_MULTIPLE"); ?>'.replace('###NUMBER###', selectedItems.length);
                        
                        if (confirm(message)) {
                            if (typeof Joomla !== 'undefined' && Joomla.submitform) {
                                Joomla.submitform('registrations.delete', document.getElementById('adminForm'));
                            } else {
                                document.adminForm.task.value = 'registrations.delete';
                                document.adminForm.submit();
                            }
                        }
                        return false;
                    };
                } else {
                    deleteBtn.classList.remove('btn-danger');
                }
            }
        }
    }
    
    /**
     * Update the boxchecked hidden field value
     */
    function updateBoxChecked() {
        const boxchecked = document.querySelector('input[name="boxchecked"]');
        if (boxchecked) {
            boxchecked.value = selectedItems.length;
        }
    }
    
    // Bind to checkbox changes
    document.addEventListener('change', function(e) {
        if (e.target.matches('input[name="cid[]"]') || e.target.matches('input[name="checkall-toggle"]')) {
            updateSelectedItems();
        }
    });
    
    // Bind to row clicks for easy selection
    document.addEventListener('click', function(e) {
        const row = e.target.closest('tr[id^="row_"]');
        if (row && !e.target.matches('input, a, button')) {
            const checkbox = row.querySelector('input[name="cid[]"]');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                updateSelectedItems();
            }
        }
    });
    
    // Override Joomla's submitbutton if it exists - Use ONLY native confirmations
    if (typeof Joomla !== 'undefined') {
        const originalSubmitbutton = Joomla.submitbutton;
        Joomla.submitbutton = function(task) {
            if (task === 'registrations.delete') {
                if (selectedItems.length === 0) {
                    alert('<?php echo JText::_("COM_BATENSTEINFORM_NO_ITEMS_SELECTED"); ?>');
                    return false;
                }
                
                // Native confirmation dialog only
                const message = selectedItems.length === 1 ? 
                    '<?php echo JText::_("COM_BATENSTEINFORM_CONFIRM_DELETE_SINGLE"); ?>' :
                    '<?php echo JText::sprintf("COM_BATENSTEINFORM_CONFIRM_DELETE_MULTIPLE", ""); ?>' + selectedItems.length + ' registrations?';
                
                if (confirm(message)) {
                    if (originalSubmitbutton) {
                        originalSubmitbutton(task);
                    } else {
                        Joomla.submitform(task, document.getElementById('adminForm'));
                    }
                }
                return false;
            }
            
            // For other tasks, use original behavior
            if (originalSubmitbutton) {
                return originalSubmitbutton(task);
            } else if (Joomla.submitform) {
                return Joomla.submitform(task, document.getElementById('adminForm'));
            }
        };
    }
    
    // Simple table ordering fallback for Joomla 5.3.1
    if (typeof Joomla !== 'undefined' && typeof Joomla.orderTable === 'undefined') {
        Joomla.orderTable = function() {
            var table = document.getElementById("sortTable");
            var direction = document.getElementById("directionTable");
            if (table && direction) {
                var order = table.options[table.selectedIndex].value;
                var dirn = 'asc';
                if (order == '<?php echo $listOrder; ?>') {
                    dirn = direction.options[direction.selectedIndex].value;
                }
                Joomla.tableOrdering(order, dirn, '');
            }
        };
    }
    
    // Simple table ordering function for Joomla 5.3.1
    if (typeof Joomla !== 'undefined' && typeof Joomla.tableOrdering === 'undefined') {
        Joomla.tableOrdering = function(order, dirn, task) {
            var form = document.getElementById('adminForm');
            if (form) {
                var input = form.querySelector('input[name="filter_order"]');
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'filter_order';
                    form.appendChild(input);
                }
                input.value = order;
                
                var dirnInput = form.querySelector('input[name="filter_order_Dir"]');
                if (!dirnInput) {
                    dirnInput = document.createElement('input');
                    dirnInput.type = 'hidden';
                    dirnInput.name = 'filter_order_Dir';
                    form.appendChild(dirnInput);
                }
                dirnInput.value = dirn;
                
                form.submit();
            }
        };
    }
    
    // Initial state setup
    setTimeout(updateButtonStates, 500);
    
    // Listen for dynamic content changes (Joomla 5.3.1 compatibility)
    const observer = new MutationObserver(function(mutations) {
        let shouldUpdate = false;
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0) {
                shouldUpdate = true;
            }
        });
        if (shouldUpdate) {
            setTimeout(updateButtonStates, 100);
        }
    });
    
    const container = document.querySelector('body');
    if (container) {
        observer.observe(container, { childList: true, subtree: true });
    }
});
</script>

<form action="<?php echo JRoute::_('index.php?option=com_batensteinform&view=registrations'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if (!empty( $this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
    <?php else : ?>
    <div id="j-main-container">
    <?php endif; ?>
        
        <?php // Search tools bar ?>
        <div id="filter-bar" class="btn-toolbar">
            <div class="filter-search btn-group pull-left">
                <label for="filter_search" class="element-invisible"><?php echo JText::_('COM_BATENSTEINFORM_FORM_SEARCH'); ?></label>
                <input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_BATENSTEINFORM_FORM_SEARCH_PLACEHOLDER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="inputbox" />
            </div>
            <div class="btn-group pull-left">
                <button type="submit" class="btn" title="<?php echo JText::_('COM_BATENSTEINFORM_FORM_SEARCH_BUTTON_TITLE'); ?>"><i class="icon-search"></i></button>
                <button type="button" class="btn" title="<?php echo JText::_('COM_BATENSTEINFORM_FORM_CLEAR_BUTTON_TITLE'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
            </div>
            <div class="btn-group pull-right hidden-phone">
                <label for="limit" class="element-invisible"><?php echo JText::_('COM_BATENSTEINFORM_FORM_LIMIT'); ?></label>
                <?php echo $this->pagination->getLimitBox(); ?>
            </div>
            <div class="btn-group pull-right hidden-phone">
                <label for="directionTable" class="element-invisible"><?php echo JText::_('COM_BATENSTEINFORM_FORM_DIRECTION'); ?></label>
                <select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
                    <option value=""><?php echo JText::_('COM_BATENSTEINFORM_FORM_SORT'); ?></option>
                    <option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('COM_BATENSTEINFORM_FORM_ASC'); ?></option>
                    <option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('COM_BATENSTEINFORM_FORM_DESC'); ?></option>
                </select>
            </div>
            <div class="btn-group pull-right">
                <label for="sortTable" class="element-invisible"><?php echo JText::_('COM_BATENSTEINFORM_FORM_SORT_BY'); ?></label>
                <select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
                    <option value=""><?php echo JText::_('COM_BATENSTEINFORM_FORM_SORT_BY'); ?></option>
                    <?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
                </select>
            </div>
        </div>
        <div class="clearfix"> </div>
        
        <?php if (empty($this->items)) : ?>
            <div class="alert alert-info">
                <h4><?php echo JText::_('COM_BATENSTEINFORM_FORM_NO_REGISTRATIONS_TITLE'); ?></h4>
                <p><?php echo JText::_('COM_BATENSTEINFORM_FORM_NO_REGISTRATIONS_TEXT'); ?></p>
            </div>
        <?php else : ?>
            <table class="table table-striped" id="registrationList">
                <thead>
                    <tr>
                        <th width="1%" class="hidden-phone">
                            <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                        </th>
                        <th style="min-width:100px" class="nowrap">
                            <?php echo JHtml::_('grid.sort', 'COM_BATENSTEINFORM_CALLING_NAME_LABEL', 'a.calling_name', $listDirn, $listOrder); ?>
                        </th>
                        <th width="15%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_BATENSTEINFORM_LAST_NAME_LABEL', 'a.last_name', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_BATENSTEINFORM_BIRTH_DATE_LABEL', 'a.birth_date', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_BATENSTEINFORM_SCOUT_SECTION_LABEL', 'a.scout_section', $listDirn, $listOrder); ?>
                        </th>
                        <th width="15%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'JGLOBAL_CREATED_DATE_LABEL', 'a.created_at', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="7">
                            <?php echo $this->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                <?php foreach ($this->items as $i => $item) :
                    $canEdit = $user->authorise('core.edit', 'com_batensteinform.registration.' . $item->id);
                    $canEditOwn = $user->authorise('core.edit.own', 'com_batensteinform.registration.' . $item->id);
                    
                    // Since we don't have checkout functionality, always allow editing if user has permission
                    $canCheckin = true;
                ?>
                    <tr class="row<?php echo $i % 2; ?>" id="row_<?php echo $item->id; ?>">
                        <td class="center hidden-phone">
                            <input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo $item->id; ?>" />
                        </td>
                        <td class="nowrap has-context">
                            <div class="pull-left">
                                <?php if ($canEdit || $canEditOwn) : ?>
                                    <a href="<?php echo JRoute::_('index.php?option=com_batensteinform&task=registration.edit&id=' . (int) $item->id); ?>" onclick="event.stopPropagation();" title="<?php echo JText::_('COM_BATENSTEINFORM_EDIT_REGISTRATION'); ?>">
                                        <?php echo $this->escape($item->calling_name); ?>
                                    </a>
                                <?php else : ?>
                                    <?php echo $this->escape($item->calling_name); ?>
                                <?php endif; ?>
                                <div class="small">
                                    <?php if (!empty($item->name_prefix)) : ?>
                                        <?php echo $this->escape($item->name_prefix) . ' '; ?>
                                    <?php endif; ?>
                                    <?php echo $this->escape($item->last_name); ?>
                                </div>
                            </div>
                        </td>
                        <td class="small hidden-phone">
                            <?php echo $this->escape($item->last_name); ?>
                        </td>
                        <td class="small hidden-phone">
                            <?php if ($item->birth_date && $item->birth_date != '0000-00-00') : ?>
                                <?php echo JHtml::_('date', $item->birth_date, 'd-m-Y'); ?>
                            <?php endif; ?>
                        </td>
                        <td class="small hidden-phone">
                            <span class="label label-info"><?php echo $this->escape($item->scout_section); ?></span>
                        </td>
                        <td class="nowrap small hidden-phone">
                            <?php echo JHtml::_('date', $item->created_at, 'd-m-Y H:i'); ?>
                        </td>
                        <td class="center hidden-phone">
                            <?php echo (int) $item->id; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Keyboard shortcuts help -->
            <div class="alert alert-info" style="margin-top: 20px;">
                <strong><?php echo JText::_('COM_BATENSTEINFORM_KEYBOARD_SHORTCUTS'); ?>:</strong>
                <span style="margin-left: 10px;">
                    <kbd>Ctrl+A</kbd> <?php echo JText::_('COM_BATENSTEINFORM_SELECT_ALL'); ?> |
                    <kbd>Enter</kbd> <?php echo JText::_('COM_BATENSTEINFORM_EDIT_SELECTED'); ?> |
                    <kbd>Delete</kbd> <?php echo JText::_('COM_BATENSTEINFORM_DELETE_SELECTED'); ?>
                </span>
            </div>
        <?php endif; ?>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>