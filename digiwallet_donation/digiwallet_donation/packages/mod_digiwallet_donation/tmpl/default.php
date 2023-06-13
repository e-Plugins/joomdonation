<?php
/**
 * @version     1.0.0
 * @package     mod_digiwallet_donation_1.0.0_j3x
 * @copyright   Copyright (C) 2020 e-plugins.nl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      DigiWallet.nl <techsupport@targetmedia.nl> - https://www.digiwallet.nl
 */

//No direct access
defined('_JEXEC') or die('Restricted access');
?>
<?php if ($id) :?>
<a href="<?= JRoute::_("index.php?option=com_digiwallet_donation&view=donation_form&id=$id")?>" class="btn btn-digiwallet-donation">
<?= $label ? $label : JText::_('DIGIWALLET_DONATION_BUTTON_TEXT');?>
</a>
<?php endif;?>