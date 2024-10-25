
-- Tatum.io Crypto provider related changes

INSERT INTO `payment_methods` (`name`, `status`) VALUES ('TatumIo', 'Active');

INSERT INTO `crypto_providers` (`name`, `alias`, `description`, `logo`, `subscription_details`, `status`) VALUES ('TatumIo', 'TatumIo', 'Tatum offers a flexible framework to build, run, and scale blockchain apps fast. ', NULL, '', 'Active');

ALTER TABLE `currencies` DROP INDEX `currencies_code_type_unique`;

INSERT INTO `email_templates` (`language_id`, `name`, `alias`, `subject`, `body`, `lang`, `type`, `status`, `group`, `created_at`, `updated_at`) VALUES
(1, 'Notify Admin on Merchant Creation', 'notify-admin-on-merchant-creation', 'New merchant Creation Notification', 'Hi <b>{admin}</b>,\r\n                    <br><br>A new merchant has been created by <b>{user}</b></br>\r\n                    <br><br><b><u><i>Here’s a brief overview of the merchant:</i></u></b>\r\n                    <br><br><b><u>Created at:</u></b> {created_at}\r\n                    <br><br><b><u>Merchant:</u></b> {business_name}\r\n                    <br><br><b><u>Site URL:</u></b> {site_url}\r\n                    <br><br><b><u>Currency:</u></b> {code}\r\n                    <br><br><b><u>Type:</u></b> {merchant_type}\r\n <br><br><b><u>Message:</u></b> {message} \r\n                <br><br>If you have any questions, please feel free to reply to this email.\r\n                    <br><br>Regards,\r\n                    <br><b>{soft_name}</b>', 'en', 'email', 'Active', 'Merchant Payment', '2023-11-16 03:46:37', NULL),
(2, 'Notify Admin on Merchant Creation', 'notify-admin-on-merchant-creation', NULL, NULL, 'ar', 'email', 'Active', 'Merchant Payment', '2023-11-16 03:46:37', NULL),
(3, 'Notify Admin on Merchant Creation', 'notify-admin-on-merchant-creation', NULL, NULL, 'fr', 'email', 'Active', 'Merchant Payment', '2023-11-16 03:46:37', NULL),
(4, 'Notify Admin on Merchant Creation', 'notify-admin-on-merchant-creation', NULL, NULL, 'pt', 'email', 'Active', 'Merchant Payment', '2023-11-16 03:46:37', NULL),
(5, 'Notify Admin on Merchant Creation', 'notify-admin-on-merchant-creation', NULL, NULL, 'ru', 'email', 'Active', 'Merchant Payment', '2023-11-16 03:46:37', NULL),
(6, 'Notify Admin on Merchant Creation', 'notify-admin-on-merchant-creation', NULL, NULL, 'es', 'email', 'Active', 'Merchant Payment', '2023-11-16 03:46:37', NULL),
(7, 'Notify Admin on Merchant Creation', 'notify-admin-on-merchant-creation', NULL, NULL, 'tr', 'email', 'Active', 'Merchant Payment', '2023-11-16 03:46:37', NULL),
(8, 'Notify Admin on Merchant Creation', 'notify-admin-on-merchant-creation', NULL, NULL, 'ch', 'email', 'Active', 'Merchant Payment', '2023-11-16 03:46:37', NULL);

-- Payment Gateway Related changes

INSERT INTO `metas` (`url`, `title`, `description`, `keywords`) VALUES ('deposit/confirm', 'Deposit confirm', 'Deposit confirm', NULL);

INSERT INTO `metas` (`url`, `title`, `description`, `keywords`) VALUES ('deposit/success', 'Deposit Success', 'Deposit Success', NULL);

ALTER TABLE `transactions` ADD `payment_status` VARCHAR(11) NULL AFTER `note`;

INSERT INTO `payment_methods` (`name`, `status`) VALUES ('Coinbase', 'Active');

CREATE TABLE IF NOT EXISTS parameters (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    unique_code VARCHAR(255) NOT NULL,
    parameter LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO `email_templates` (`language_id`, `name`, `alias`, `subject`, `body`, `lang`, `type`, `status`, `group`, `created_at`, `updated_at`) VALUES
(1, 'Notify Admin on Merchant Creation', 'notify-admin-on-merchant-creation', 'New merchant Creation Notification', 'Hi <b>{admin}</b>,<br><br>A new merchant has been created by <b>{user}</b></br><br><br><b><u><i>Here’s a brief overview of the merchant:</i></u></b><br><br><b><u>Created at:</u></b> {created_at}<br><br><b><u>Merchant:</u></b> {business_name}<br><br><b><u>Site URL:</u></b> {site_url}<br><br><b><u>Currency:</u></b> {code}<br><br><b><u>Type:</u></b> {merchant_type}<br><br><b><u>Message:</u></b> {message}<br><br>If you have any questions, please feel free to reply to this email.<br><br>Regards,<br><b>{soft_name}</b>', 'en', 'email', 'Active', 'Merchant Payment', '2023-11-16 03:46:37', NULL),
(2, 'Notify Admin on Merchant Creation', 'notify-admin-on-merchant-creation', NULL, NULL, 'ar', 'email', 'Active', 'Merchant Payment', '2023-11-16 03:46:37', NULL),
(3, 'Notify Admin on Merchant Creation', 'notify-admin-on-merchant-creation', NULL, NULL, 'fr', 'email', 'Active', 'Merchant Payment', '2023-11-16 03:46:37', NULL),
(4, 'Notify Admin on Merchant Creation', 'notify-admin-on-merchant-creation', NULL, NULL, 'pt', 'email', 'Active', 'Merchant Payment', '2023-11-16 03:46:37', NULL),
(5, 'Notify Admin on Merchant Creation', 'notify-admin-on-merchant-creation', NULL, NULL, 'ru', 'email', 'Active', 'Merchant Payment', '2023-11-16 03:46:37', NULL),
(6, 'Notify Admin on Merchant Creation', 'notify-admin-on-merchant-creation', NULL, NULL, 'es', 'email', 'Active', 'Merchant Payment', '2023-11-16 03:46:37', NULL),
(7, 'Notify Admin on Merchant Creation', 'notify-admin-on-merchant-creation', NULL, NULL, 'tr', 'email', 'Active', 'Merchant Payment', '2023-11-16 03:46:37', NULL),
(8, 'Notify Admin on Merchant Creation', 'notify-admin-on-merchant-creation', NULL, NULL, 'ch', 'email', 'Active', 'Merchant Payment', '2023-11-16 03:46:37', NULL);



-- Clear cache permission

INSERT INTO `permissions` (`id`, `group`, `name`, `display_name`, `description`, `user_type`, `created_at`, `updated_at`) VALUES
(202, 'Cache Clear', 'view_cache_clear', 'View Cache Clear', 'View Cache Clear', 'Admin', '2023-11-14 21:56:29', '2023-11-14 21:56:29'),
(203, 'Cache Clear', 'add_cache_clear', NULL, NULL, 'Admin', '2023-11-14 21:56:29', '2023-11-14 21:56:29'),
(204, 'Cache Clear', 'edit_cache_clear', NULL, NULL, 'Admin', '2023-11-14 21:56:29', '2023-11-14 21:56:29'),
(205, 'Cache Clear', 'delete_cache_clear', NULL, NULL, 'Admin', '2023-11-14 21:56:29', '2023-11-14 21:56:29');

INSERT INTO `permission_role` (`role_id`, `permission_id`) VALUES
(1, 202),
(1, 203),
(1, 204),
(1, 205);

