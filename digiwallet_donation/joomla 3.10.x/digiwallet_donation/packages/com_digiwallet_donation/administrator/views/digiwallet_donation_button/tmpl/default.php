<?php
/**
 * @version     1.0.0
 * @package     com_digiwallet_donation_1.0.0_j3x
 * @copyright   Copyright (C) 2020 e-plugins.nl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      DigiWallet.nl <techsupport@targetmedia.nl> - https://www.digiwallet.nl
 */

// No direct access
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_digiwallet_donation&layout=edit&id=' . $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate">
	<div class="row-fluid">
		<div class="span10 form-horizontal">
    		<p><strong>Embed shortcode below into an article to show your button</strong></p>
    		<p>{loadmodule mod_digiwallet_donation, Digiwallet Donation, <?= $this->item->id ? $this->item->id : 'buttonId' ; ?>}</p>
    		<br/>
            <fieldset class="adminform">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('id'); ?>
					</div>
				</div>
            	<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('amount'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('amount'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('label'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('label'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('created_by'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('created_by'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('state'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('state'); ?>
					</div>
				</div>
            </fieldset>
    	</div>
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
	<div id="validation-form-failed" data-backend-detail="digiwallet_donation_button" data-message="<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>">
	</div>
</form>
