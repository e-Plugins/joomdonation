<?php
/**
 * @version        5.6.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2019 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
$configuration = $this->component_params->get('configuration');
?>
<?php if(!empty($this->sidebar)): ?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<?php endif;?>
<form action="index.php?option=com_digiwallet_donation&view=digiwallet_donation_configuration"
	method="post" name="adminForm" id="adminForm" class="form-horizontal">
	<div id="j-main-container">
		<div class="row-fluid">
			<table class="admintable adminform" style="width: 100%;">
				<tbody>
					<tr>
						<td colspan="3" class="config_heading">
							<h3>Digiwallet Configuration</h3>
						</td>
					</tr>
					<tr>
						<td class="key" width="25%"><?php echo JText::_('COM_DIGIWALLET_DONATION_RTLO_LABEL'); ?></td>
						<td width="15%">
							<input type="text" name="dw_rtlo" class="input-mini" value="<?= @$this->escape($configuration['dw_rtlo']) ?>" size="10">
						</td>
						<td width="60%"><?php echo JText::_('COM_DIGIWALLET_DONATION_RTLO_DESC'); ?></td>
					</tr>
					<tr>
						<td class="key" width="25%"><?php echo JText::_('COM_DIGIWALLET_DONATION_TOKEN_LABEL'); ?></td>
						<td width="15%">
							<input type="text" name="dw_token" class="input-large" value="<?= @$this->escape($configuration['dw_token']) ?>" size="10">
						</td>
						<td width="60%"><?php echo JText::_('COM_DIGIWALLET_DONATION_TOKEN_DESC'); ?></td>
					</tr>
					<tr>
						<td class="key" width="25%"><?php echo JText::_('COM_DIGIWALLET_DONATION_SUCCESS_RETURN_LABEL'); ?></td>
						<td width="15%">
							<input type="text" name="dw_return" class="input-large" value="<?= @$this->escape($configuration['dw_return']) ?>" size="10">
						</td>
						<td width="60%"><?php echo JText::_('COM_DIGIWALLET_DONATION_SUCCESS_RETURN_DESC'); ?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_IDE'); ?></td>
						<td>
						<?php 
    						if (@$configuration['digiwallet_enable_ide'] == 1) {
    						    $checked_ide_yes = 'checked="checked"';
    						    $checked_ide_no = '';
    						} else {
    						    $checked_ide_no = 'checked="checked"';
    						    $checked_ide_yes = '';
    						}
						?>
							<fieldset id="digiwallet_enable_ide" class="radio btn-group btn-group-yesno radio">
								<input type="radio" id="digiwallet_enable_ide_no" name="digiwallet_enable_ide"
									value="0" <?= $checked_ide_no; ?>> <label for="digiwallet_enable_ide_no"
									class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_NO'); ?> </label> <input type="radio"
									id="digiwallet_enable_ide_yes" name="digiwallet_enable_ide" value="1" <?= $checked_ide_yes; ?>> <label
									for="digiwallet_enable_ide_yes" class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_YES'); ?> </label>
							</fieldset>
						</td>
						<td><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_IDE_DESC'); ?></td>
					</tr>
					
					<tr>
						<td class="key"><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_MRC'); ?></td>
						<td>
						<?php 
    						if (@$configuration['digiwallet_enable_mrc'] == 1) {
    						    $checked_mrc_yes = 'checked="checked"';
    						    $checked_mrc_no = '';
    						} else {
    						    $checked_mrc_no = 'checked="checked"';
    						    $checked_mrc_yes = '';
    						}
						?>
							<fieldset id="digiwallet_enable_mrc" class="radio btn-group btn-group-yesno radio">
								<input type="radio" id="digiwallet_enable_mrc_no" name="digiwallet_enable_mrc"
									value="0" <?= $checked_mrc_no; ?>> <label for="digiwallet_enable_mrc_no"
									class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_NO'); ?> </label> <input type="radio"
									id="digiwallet_enable_mrc_yes" name="digiwallet_enable_mrc" value="1" <?= $checked_mrc_yes; ?>> <label
									for="digiwallet_enable_mrc_yes" class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_YES'); ?> </label>
							</fieldset>
						</td>
						<td><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_MRC_DESC'); ?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_DEB'); ?></td>
						<td>
						<?php 
    						if (@$configuration['digiwallet_enable_deb'] == 1) {
    						    $checked_deb_yes = 'checked="checked"';
    						    $checked_deb_no = '';
    						} else {
    						    $checked_deb_no = 'checked="checked"';
    						    $checked_deb_yes = '';
    						}
						?>
							<fieldset id="digiwallet_enable_deb" class="radio btn-group btn-group-yesno radio">
								<input type="radio" id="digiwallet_enable_deb_no" name="digiwallet_enable_deb"
									value="0" <?= $checked_deb_no; ?>> <label for="digiwallet_enable_deb_no"
									class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_NO'); ?> </label> <input type="radio"
									id="digiwallet_enable_deb_yes" name="digiwallet_enable_deb" value="1" <?= $checked_deb_yes; ?>> <label
									for="digiwallet_enable_deb_yes" class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_YES'); ?> </label>
							</fieldset>
						</td>
						<td><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_DEB_DESC'); ?></td>
					</tr>

					<tr>
						<td class="key"><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_WAL'); ?></td>
						<td>
						<?php 
    						if (@$configuration['digiwallet_enable_wal'] == 1) {
    						    $checked_wal_yes = 'checked="checked"';
    						    $checked_wal_no = '';
    						} else {
    						    $checked_wal_no = 'checked="checked"';
    						    $checked_wal_yes = '';
    						}
						?>
							<fieldset id="digiwallet_enable_wal" class="radio btn-group btn-group-yesno radio">
								<input type="radio" id="digiwallet_enable_wal_no" name="digiwallet_enable_wal"
									value="0" <?= $checked_wal_no; ?>> <label for="digiwallet_enable_wal_no"
									class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_NO'); ?> </label> <input type="radio"
									id="digiwallet_enable_wal_yes" name="digiwallet_enable_wal" value="1" <?= $checked_wal_yes; ?>> <label
									for="digiwallet_enable_wal_yes" class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_YES'); ?> </label>
							</fieldset>
						</td>
						<td><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_WAL_DESC'); ?></td>
					</tr>
					
					<tr>
						<td class="key"><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_CC'); ?></td>
						<td>
						<?php 
    						if (@$configuration['digiwallet_enable_cc'] == 1) {
    						    $checked_cc_yes = 'checked="checked"';
    						    $checked_cc_no = '';
    						} else {
    						    $checked_cc_no = 'checked="checked"';
    						    $checked_cc_yes = '';
    						}
						?>
							<fieldset id="digiwallet_enable_cc" class="radio btn-group btn-group-yesno radio">
								<input type="radio" id="digiwallet_enable_cc_no" name="digiwallet_enable_cc"
									value="0" <?= $checked_cc_no; ?>> <label for="digiwallet_enable_cc_no"
									class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_NO'); ?> </label> <input type="radio"
									id="digiwallet_enable_cc_yes" name="digiwallet_enable_cc" value="1" <?= $checked_cc_yes; ?>> <label
									for="digiwallet_enable_cc_yes" class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_YES'); ?> </label>
							</fieldset>
						</td>
						<td><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_CC_DESC'); ?></td>
					</tr>
					
					<tr>
						<td class="key"><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_BW'); ?></td>
						<td>
						<?php 
    						if (@$configuration['digiwallet_enable_bw'] == 1) {
    						    $checked_bw_yes = 'checked="checked"';
    						    $checked_bw_no = '';
    						} else {
    						    $checked_bw_no = 'checked="checked"';
    						    $checked_bw_yes = '';
    						}
						?>
							<fieldset id="digiwallet_enable_bw" class="radio btn-group btn-group-yesno radio">
								<input type="radio" id="digiwallet_enable_bw_no" name="digiwallet_enable_bw"
									value="0" <?= $checked_bw_no; ?>> <label for="digiwallet_enable_bw_no"
									class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_NO'); ?> </label> <input type="radio"
									id="digiwallet_enable_bw_yes" name="digiwallet_enable_bw" value="1" <?= $checked_bw_yes; ?>> <label
									for="digiwallet_enable_bw_yes" class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_YES'); ?> </label>
							</fieldset>
						</td>
						<td><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_BW_DESC'); ?></td>
					</tr>
					
					<tr>
						<td class="key"><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_AFP'); ?></td>
						<td>
						<?php 
    						if (@$configuration['digiwallet_enable_afp'] == 1) {
    						    $checked_afp_yes = 'checked="checked"';
    						    $checked_afp_no = '';
    						} else {
    						    $checked_afp_no = 'checked="checked"';
    						    $checked_afp_yes = '';
    						}
						?>
							<fieldset id="digiwallet_enable_afp" class="radio btn-group btn-group-yesno radio">
								<input type="radio" id="digiwallet_enable_afp_no" name="digiwallet_enable_afp"
									value="0" <?= $checked_afp_no; ?>> <label for="digiwallet_enable_afp_no"
									class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_NO'); ?> </label> <input type="radio"
									id="digiwallet_enable_afp_yes" name="digiwallet_enable_afp" value="1" <?= $checked_afp_yes; ?>> <label
									for="digiwallet_enable_afp_yes" class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_YES'); ?> </label>
							</fieldset>
						</td>
						<td><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_AFP_DESC'); ?></td>
					</tr>
					
					<tr>
						<td class="key"><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_PYP'); ?></td>
						<td>
						<?php 
    						if (@$configuration['digiwallet_enable_pyp'] == 1) {
    						    $checked_pyp_yes = 'checked="checked"';
    						    $checked_pyp_no = '';
    						} else {
    						    $checked_pyp_no = 'checked="checked"';
    						    $checked_pyp_yes = '';
    						}
						?>
							<fieldset id="digiwallet_enable_pyp" class="radio btn-group btn-group-yesno radio">
								<input type="radio" id="digiwallet_enable_pyp_no" name="digiwallet_enable_pyp"
									value="0" <?= $checked_pyp_no; ?>> <label for="digiwallet_enable_pyp_no"
									class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_NO'); ?> </label> <input type="radio"
									id="digiwallet_enable_pyp_yes" name="digiwallet_enable_pyp" value="1" <?= $checked_pyp_yes; ?>> <label
									for="digiwallet_enable_pyp_yes" class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_YES'); ?> </label>
							</fieldset>
						</td>
						<td><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_PYP_DESC'); ?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_EPS'); ?></td>
						<td>
						<?php 
    						if (@$configuration['digiwallet_enable_eps'] == 1) {
    						    $checked_eps_yes = 'checked="checked"';
    						    $checked_eps_no = '';
    						} else {
    						    $checked_eps_no = 'checked="checked"';
    						    $checked_eps_yes = '';
    						}
						?>
							<fieldset id="digiwallet_enable_eps" class="radio btn-group btn-group-yesno radio">
								<input type="radio" id="digiwallet_enable_eps_no" name="digiwallet_enable_eps"
									value="0" <?= $checked_eps_no; ?>> <label for="digiwallet_enable_eps_no"
									class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_NO'); ?> </label> <input type="radio"
									id="digiwallet_enable_eps_yes" name="digiwallet_enable_eps" value="1" <?= $checked_eps_yes; ?>> <label
									for="digiwallet_enable_eps_yes" class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_YES'); ?> </label>
							</fieldset>
						</td>
						<td><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_EPS_DESC'); ?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_GIP'); ?></td>
						<td>
						<?php 
    						if (@$configuration['digiwallet_enable_gip'] == 1) {
    						    $checked_gip_yes = 'checked="checked"';
    						    $checked_gip_no = '';
    						} else {
    						    $checked_gip_no = 'checked="checked"';
    						    $checked_gip_yes = '';
    						}
						?>
							<fieldset id="digiwallet_enable_gip" class="radio btn-group btn-group-yesno radio">
								<input type="radio" id="digiwallet_enable_gip_no" name="digiwallet_enable_gip"
									value="0" <?= $checked_gip_no; ?>> <label for="digiwallet_enable_gip_no"
									class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_NO'); ?> </label> <input type="radio"
									id="digiwallet_enable_gip_yes" name="digiwallet_enable_gip" value="1" <?= $checked_gip_yes; ?>> <label
									for="digiwallet_enable_gip_yes" class="btn"> <?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_YES'); ?> </label>
							</fieldset>
						</td>
						<td><?php echo JText::_('COM_DIGIWALLET_DONATION_ENABLE_PAYMENT_OPTION_GIP_DESC'); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="clearfix"></div>
	<input type="hidden" name="task" value="" />
</form>
