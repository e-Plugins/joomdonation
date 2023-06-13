CREATE TABLE IF NOT EXISTS `#__digiwallet_donation_buttons` (
	`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`amount` DECIMAL(10) NOT NULL,
	`label` VARCHAR(255) NOT NULL,
	`created_by` INT(11) NOT NULL,
	`state` INT(11) NOT NULL,
	`ordering` INT(11) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB COMMENT="" DEFAULT COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__digiwallet_donation_configuration` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `config_key` varchar(50) DEFAULT NULL,
  `config_value` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB COMMENT="" DEFAULT COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__digiwallet_donation` (
	`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`button_id` INT(11) NOT NULL,
	`rtlo` INT(11) NOT NULL,
	`token` varchar(255) DEFAULT NULL,
	`amount` DECIMAL(10) NOT NULL,
	`amount_paid` DECIMAL(10) DEFAULT NULL,
	`transaction_id` varchar(50) DEFAULT NULL,
	`payment_method` varchar(10) NOT NULL,
	`bw_data` varchar(500) DEFAULT NULL,
	`status` TINYINT(6) NOT NULL DEFAULT '0',
	`message` text DEFAULT NULL,
	`created_date` DATETIME NULL,
	`payment_date` DATETIME NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB COMMENT="" DEFAULT COLLATE=utf8_general_ci;