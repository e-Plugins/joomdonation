<?php
/**
 * @version        1.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         DigiWallet.nl
 * @copyright      Copyright (C) 2020 DigiWallet.nl
 * @license        GNU/GPL, see LICENSE.php
 */
/**
 * ensure this file is being included by a parent file
 */
defined('_JEXEC') or die();
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Change the db structure of the previous version
 */
class os_digiwalletInstallerScript
{

    /**
     * method to install the component
     *
     * @return void
     */
    function install($parent)
    {
        $this->updateDatabaseSchema();
    }

    function updateDatabaseSchema()
    {
        $db = JFactory::getDbo();
        $query = "CREATE TABLE IF NOT EXISTS `#__joomDonation_digiwallet` (
           `id` int(11) NOT NULL AUTO_INCREMENT,
           `cart_id` varchar(11) NOT NULL DEFAULT '0',
           `rtlo` int(11) NOT NULL,
           `paymethod` varchar(8) NOT NULL DEFAULT 'IDE',
           `transaction_id` varchar(50) NOT NULL,
           `bank_id` varchar(8) NULL,
           `country_id` varchar(8) NULL,
           `description` varchar(64) DEFAULT NULL,
            `more_information` varchar(1000) DEFAULT NULL,
           `amount` decimal(11,2) NOT NULL DEFAULT '0.00',
           `paid_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
           `message` varchar(255) DEFAULT NULL,
           PRIMARY KEY (`id`),
           KEY `cart_id` (`cart_id`),
           KEY `transaction_id` (`transaction_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
        $db->setQuery($query);
        $db->execute();
        
        // active digiwallet
        $sql = "INSERT INTO `#__jd_payment_plugins` (`name`, `title`, `author`, `creation_date`, `copyright`, `license`, `author_email`, `author_url`, `version`, `description`, `params`, `ordering`, `published`) VALUES
        ('os_digiwallet', 'Payment via Digiwallet', 'DigiWallet.nl', '25-08-2020', 'Copyright 2020 DigiWallet.nl', 'http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2', 'techsupport@targetmedia.eu', 'https://www.digiwallet.nl', '1.0', 'Digiwallet Payment Plugin For Joom Donation Extension', NULL, 0, 1);";
        $db->setQuery($sql);
        $db->execute();
    }

    function uninstall($parent)
    {
        $db = JFactory::getDbo();
        // deactive digiwallet
        $sql = "DELETE FROM `#__jd_payment_plugins` WHERE `name` = 'os_digiwallet';";
        $db->setQuery($sql);
        $db->execute();
        //delete view file
        $viewFolder = JPATH_ROOT . '/components/com_jdonation/view/digiwallet';
        if (JFolder::exists($viewFolder)) {
            JFolder::delete($viewFolder);
        }
    }
}
