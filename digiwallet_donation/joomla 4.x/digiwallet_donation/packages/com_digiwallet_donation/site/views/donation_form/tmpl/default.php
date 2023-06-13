<?php
/**
 * @version     1.0.0
 * @package     com_digiwallet_donation
 * @copyright   Copyright (C) 2020 e-plugins.nl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      DigiWallet.nl <techsupport@targetmedia.nl> - https://www.digiwallet.nl
 */

// No direct access
defined('_JEXEC') or die();
?>
<?php if($this->item) :?>
    <form name="digiwallet_donation_form" id="digiwallet_donation_form"
    	class="digiwallet-donation-form" method="post" action="">
    	<?php if (! empty($this->params->get('error'))) :?>
    		<p class="alert alert-danger"><?= $this->params->get('error');?></p>
    	<?php endif;?>
    	<p class="form-title"><?= JText::_('DIGIWALLET_DONATION_FORM_TITLE'); ?></p>
    	<?php if (! empty($this->params->get('bankArr'))) :?>
    	    <?php 
    	       $bankArrByPaymentOption = $this->params->get('bankArr');
    	       $selectdMethod = $this->params->get('selectdMethod');
    	       $payOption = $this->params->get('payOption');
    	    ?>
        	<?php foreach ($bankArrByPaymentOption as $paymentOption => $bankCodesArr):?>
        	<?php 
                $checked_method = '';
                $childStyle = 'display:none;';
                $bankListCount = count($bankCodesArr);
                if ($paymentOption == $selectdMethod) {
                    $checked_method = 'checked="checked"';
                    $childStyle = '';
                }
                $radClass = in_array($paymentOption, ['IDE', 'DEB']) ? 'have-listing' : '';
            ?>
        	<div class="control-group">
        		<label class="control-label"><input id="pay_method_<?= $paymentOption; ?>"
        			name="pay_method" value="<?= $paymentOption; ?>" <?= $checked_method;?> type="radio" class="<?= $radClass; ?>"><img
        			src="<?= JURI::root() .'components/com_digiwallet_donation/assets/images/' . $paymentOption .'.png'; ?>"></label>
        		<div class="controls" style="<?= $childStyle ?>">
        		<?php if ($bankListCount == 0) :?>
        		<?= JText::_('No banks found for this payment option'); ?>
        		<?php elseif ($bankListCount == 1):?>
        			<input value="<?= $paymentOption ?>" name="payment_option_select['<?= $paymentOption ?>']" type="hidden">
        		<?php else:?>
        			<?php foreach ($bankCodesArr as $key => $value):?>
        			<?php 
        			if ($key  == $payOption) {
        			    $checked_option = 'checked="checked"';
        			}
        			?>
        				<label class="control-label"><input id="pay_method_<?= $paymentOption; ?>"
        			name="payment_option_select[<?= $paymentOption ?>]" value="<?= $key ?>" type="radio" <?= @$checked_option; ?> class="rad-payment-data"><?= $value ?></label>
    				<?php endforeach;?>
        			
        		<?php endif;?>
        		</div>
        	</div>
        	<?php endforeach;?>
        	<div class="form-group">
        		<input type="submit" name="Submit" class="btn btn-primary"
        			value="<?= JText::_('DIGIWALLET_DONATION_PAY_BTN'); ?>">
        	</div>
        <?php endif;?>
    </form>
<?php endif;?>