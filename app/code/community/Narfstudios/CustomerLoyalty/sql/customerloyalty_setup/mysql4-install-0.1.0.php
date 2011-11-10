<?php
// Load EAV Database Connection
$read = Mage::getSingleton('core/resource')->getConnection('core_read');

// start the install
$installer 	= $this;
$installer->startSetup();

// insert template
$installer->run("
INSERT INTO {$this->getTable('core_email_template')} (`template_code`, `template_text`, `template_type`, `template_subject`, `template_sender_name`, `template_sender_email`, `added_at`, `modified_at`) VALUES
('Transaktion fehlgeschlagen Kundeninformation', 'Guten Tag {{var customer}},<br /><br /><p>wir müssen Ihnen leider mitteilen, dass Ihre Bestellung vom {{var dateAndTime}} fehlgeschlagen ist. Das tut uns sehr leid und wir möchten ihnen den Gutschein {{var couponCode}} im Wert von {{var couponValue}} {{var currency}} überreichen.</p><p>Grund der fehlgeschlagenen Transaktion war:<br />{{var reason}}</p><p>Wir von Zawali wünschen Ihnen noch einen schönen Tag!</p>', 2, 'Zahlungs-Transaktion fehlgeschlagen', NULL, NULL, NOW(), NOW());
");
$installer->endSetup();
?>
