<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {

        $permissions = [

            /* FOR BACKEND (Admin Panel)*/
            // User
            ['id' => '1', 'group' => 'User', 'name' => 'view_user', 'display_name' => 'View User', 'description' => 'View User', 'user_type' => 'Admin'],
            ['id' => '2', 'group' => 'User', 'name' => 'add_user', 'display_name' => 'Add User', 'description' => 'Add User', 'user_type' => 'Admin'],
            ['id' => '3', 'group' => 'User', 'name' => 'edit_user', 'display_name' => 'Edit User', 'description' => 'Edit User', 'user_type' => 'Admin'],
            ['id' => '4', 'group' => 'User', 'name' => 'delete_user', 'display_name' => 'Delete User', 'description' => 'Delete User', 'user_type' => 'Admin'],

            // Transaction
            ['id' => '5', 'group' => 'Transaction', 'name' => 'view_transaction', 'display_name' => 'View Transaction', 'description' => 'View Transaction', 'user_type' => 'Admin'],
            ['id' => '6', 'group' => 'Transaction', 'name' => 'add_transaction', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '7', 'group' => 'Transaction', 'name' => 'edit_transaction', 'display_name' => 'Edit Transaction', 'description' => 'Edit Transaction', 'user_type' => 'Admin'],
            ['id' => '8', 'group' => 'Transaction', 'name' => 'delete_transaction', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Deposit
            ['id' => '9', 'group' => 'Deposit', 'name' => 'view_deposit', 'display_name' => 'View Deposit', 'description' => 'View Deposit', 'user_type' => 'Admin'],
            ['id' => '10', 'group' => 'Deposit', 'name' => 'add_deposit', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '11', 'group' => 'Deposit', 'name' => 'edit_deposit', 'display_name' => 'Edit Deposit', 'description' => 'Edit Deposit', 'user_type' => 'Admin'],
            ['id' => '12', 'group' => 'Deposit', 'name' => 'delete_deposit', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Withdrawal
            ['id' => '13', 'group' => 'Withdrawal', 'name' => 'view_withdrawal', 'display_name' => 'View Withdrawal', 'description' => 'View Withdrawal', 'user_type' => 'Admin'],
            ['id' => '14', 'group' => 'Withdrawal', 'name' => 'add_withdrawal', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '15', 'group' => 'Withdrawal', 'name' => 'edit_withdrawal', 'display_name' => 'Edit Withdrawal', 'description' => 'Edit Withdrawal', 'user_type' => 'Admin'],
            ['id' => '16', 'group' => 'Withdrawal', 'name' => 'delete_withdrawal', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Transfer
            ['id' => '17', 'group' => 'Transfer', 'name' => 'view_transfer', 'display_name' => 'View Transfer', 'description' => 'View Transfer', 'user_type' => 'Admin'],
            ['id' => '18', 'group' => 'Transfer', 'name' => 'add_transfer', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '19', 'group' => 'Transfer', 'name' => 'edit_transfer', 'display_name' => 'Edit Transfer', 'description' => 'Edit Transfer', 'user_type' => 'Admin'],
            ['id' => '20', 'group' => 'Transfer', 'name' => 'delete_transfer', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Exchange
            ['id' => '21', 'group' => 'Exchange', 'name' => 'view_exchange', 'display_name' => 'View Exchange', 'description' => 'View Exchange', 'user_type' => 'Admin'],
            ['id' => '22', 'group' => 'Exchange', 'name' => 'add_exchange', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '23', 'group' => 'Exchange', 'name' => 'edit_exchange', 'display_name' => 'Edit Exchange', 'description' => 'Edit Exchange', 'user_type' => 'Admin'],
            ['id' => '24', 'group' => 'Exchange', 'name' => 'delete_exchange', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Request Payment
            ['id' => '29', 'group' => 'Request Payment', 'name' => 'view_request_payment', 'display_name' => 'View Request Payment', 'description' => 'View Request Payment', 'user_type' => 'Admin'],
            ['id' => '30', 'group' => 'Request Payment', 'name' => 'add_request_payment', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '31', 'group' => 'Request Payment', 'name' => 'edit_request_payment', 'display_name' => 'Edit Request Payment', 'description' => 'Edit Request Payment', 'user_type' => 'Admin'],
            ['id' => '32', 'group' => 'Request Payment', 'name' => 'delete_request_payment', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Revenue
            ['id' => '33', 'group' => 'Revenue', 'name' => 'view_revenue', 'display_name' => 'View Revenue', 'description' => 'View Revenue', 'user_type' => 'Admin'],
            ['id' => '34', 'group' => 'Revenue', 'name' => 'add_revenue', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '35', 'group' => 'Revenue', 'name' => 'edit_revenue', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '36', 'group' => 'Revenue', 'name' => 'delete_revenue', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Email Template
            ['id' => '37', 'group' => 'Email Template', 'name' => 'view_email_template', 'display_name' => 'View Email Template', 'description' => 'View Email Template', 'user_type' => 'Admin'],
            ['id' => '38', 'group' => 'Email Template', 'name' => 'add_email_template', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '39', 'group' => 'Email Template', 'name' => 'edit_email_template', 'display_name' => 'Edit Email Template', 'description' => 'Edit Email Template', 'user_type' => 'Admin'],
            ['id' => '40', 'group' => 'Email Template', 'name' => 'delete_email_template', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Activity Log
            ['id' => '41', 'group' => 'Activity Log', 'name' => 'view_activity_log', 'display_name' => 'View Activity Log', 'description' => 'View Activity Log', 'user_type' => 'Admin'],
            ['id' => '42', 'group' => 'Activity Log', 'name' => 'add_activity_log', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '43', 'group' => 'Activity Log', 'name' => 'edit_activity_log', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '44', 'group' => 'Activity Log', 'name' => 'delete_activity_log', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // General Setting
            ['id' => '45', 'group' => 'General Setting', 'name' => 'view_general_setting', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '46', 'group' => 'General Setting', 'name' => 'add_general_setting', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '47', 'group' => 'General Setting', 'name' => 'edit_general_setting', 'display_name' => 'Edit General Setting', 'description' => 'Edit General Setting', 'user_type' => 'Admin'],
            ['id' => '48', 'group' => 'General Setting', 'name' => 'delete_general_setting', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Social Links
            ['id' => '49', 'group' => 'Social Links', 'name' => 'view_social_links', 'display_name' => 'View Social Links', 'description' => 'View Social Links', 'user_type' => 'Admin'],
            ['id' => '50', 'group' => 'Social Links', 'name' => 'add_social_links', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '51', 'group' => 'Social Links', 'name' => 'edit_social_links', 'display_name' => 'Edit Social Links', 'description' => 'Edit Social Links', 'user_type' => 'Admin'],
            ['id' => '52', 'group' => 'Social Links', 'name' => 'delete_social_links', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // API Credentials
            ['id' => '53', 'group' => 'API Credentials', 'name' => 'view_api_credentials', 'display_name' => 'View API Credentials', 'description' => 'View API Credentials', 'user_type' => 'Admin'],
            ['id' => '54', 'group' => 'API Credentials', 'name' => 'add_api_credentials', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '55', 'group' => 'API Credentials', 'name' => 'edit_api_credentials', 'display_name' => 'Edit API Credentials', 'description' => 'Edit API Credentials', 'user_type' => 'Admin'],
            ['id' => '56', 'group' => 'API Credentials', 'name' => 'delete_api_credentials', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Payment Methods
            ['id' => '57', 'group' => 'Payment Methods', 'name' => 'view_payment_methods', 'display_name' => 'View Payment Methods', 'description' => 'View Payment Methods', 'user_type' => 'Admin'],
            ['id' => '58', 'group' => 'Payment Methods', 'name' => 'add_payment_methods', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '59', 'group' => 'Payment Methods', 'name' => 'edit_payment_methods', 'display_name' => 'Edit Payment Methods', 'description' => 'Edit Payment Methods', 'user_type' => 'Admin'],
            ['id' => '60', 'group' => 'Payment Methods', 'name' => 'delete_payment_methods', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Email Setting
            ['id' => '61', 'group' => 'Email Setting', 'name' => 'view_email_setting', 'display_name' => 'View Email Setting', 'description' => 'View Email Setting', 'user_type' => 'Admin'],
            ['id' => '62', 'group' => 'Email Setting', 'name' => 'add_email_setting', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '63', 'group' => 'Email Setting', 'name' => 'edit_email_setting', 'display_name' => 'Edit Email Setting', 'description' => 'Edit Email Setting', 'user_type' => 'Admin'],
            ['id' => '64', 'group' => 'Email Setting', 'name' => 'delete_email_setting', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],


            // Currency
            ['id' => '65', 'group' => 'Currency', 'name' => 'view_currency', 'display_name' => 'View Currency', 'description' => 'View Currency', 'user_type' => 'Admin'],
            ['id' => '66', 'group' => 'Currency', 'name' => 'add_currency', 'display_name' => 'Add Currency', 'description' => 'Add Currency', 'user_type' => 'Admin'],
            ['id' => '67', 'group' => 'Currency', 'name' => 'edit_currency', 'display_name' => 'Edit Currency', 'description' => 'Edit Currency', 'user_type' => 'Admin'],
            ['id' => '68', 'group' => 'Currency', 'name' => 'delete_currency', 'display_name' => 'Delete Currency', 'description' => 'Delete Currency', 'user_type' => 'Admin'],

            // Country
            ['id' => '69', 'group' => 'Country', 'name' => 'view_country', 'display_name' => 'View Country', 'description' => 'View Country', 'user_type' => 'Admin'],
            ['id' => '70', 'group' => 'Country', 'name' => 'add_country', 'display_name' => 'Add Country', 'description' => 'Add Country', 'user_type' => 'Admin'],
            ['id' => '71', 'group' => 'Country', 'name' => 'edit_country', 'display_name' => 'Edit Country', 'description' => 'Edit Country', 'user_type' => 'Admin'],
            ['id' => '72', 'group' => 'Country', 'name' => 'delete_country', 'display_name' => 'Delete Country', 'description' => 'Delete Country', 'user_type' => 'Admin'],

            // Language
            ['id' => '73', 'group' => 'Language', 'name' => 'view_language', 'display_name' => 'View Language', 'description' => 'View Language', 'user_type' => 'Admin'],
            ['id' => '74', 'group' => 'Language', 'name' => 'add_language', 'display_name' => 'Add Language', 'description' => 'Add Language', 'user_type' => 'Admin'],
            ['id' => '75', 'group' => 'Language', 'name' => 'edit_language', 'display_name' => 'Edit Language', 'description' => 'Edit Language', 'user_type' => 'Admin'],
            ['id' => '76', 'group' => 'Language', 'name' => 'delete_language', 'display_name' => 'Delete Language', 'description' => 'Delete Language', 'user_type' => 'Admin'],

            // Role
            ['id' => '77', 'group' => 'Role', 'name' => 'view_role', 'display_name' => 'View Role', 'description' => 'View Role', 'user_type' => 'Admin'],
            ['id' => '78', 'group' => 'Role', 'name' => 'add_role', 'display_name' => 'Add Role', 'description' => 'Add Role', 'user_type' => 'Admin'],
            ['id' => '79', 'group' => 'Role', 'name' => 'edit_role', 'display_name' => 'Edit Role', 'description' => 'Edit Role', 'user_type' => 'Admin'],
            ['id' => '80', 'group' => 'Role', 'name' => 'delete_role', 'display_name' => 'Delete Role', 'description' => 'Delete Role', 'user_type' => 'Admin'],

            // Fees
            // ['id' => '81', 'group' => 'Fees', 'name' => 'view_fees', 'display_name' => 'View Fees', 'description' => 'View Fees', 'user_type' => 'Admin'],
            // ['id' => '82', 'group' => 'Fees', 'name' => 'add_fees', 'display_name' => 'Add Fees', 'description' => 'Add Fees', 'user_type' => 'Admin'],
            // ['id' => '83', 'group' => 'Fees', 'name' => 'edit_fees', 'display_name' => 'Edit Fees', 'description' => 'Edit Fees', 'user_type' => 'Admin'],
            // ['id' => '84', 'group' => 'Fees', 'name' => 'delete_fees', 'display_name' => 'Delete Fees', 'description' => 'Delete Fees', 'user_type' => 'Admin'],

            // Database Backup
            ['id' => '85', 'group' => 'Database Backup', 'name' => 'view_database_backup', 'display_name' => 'View Database Backup', 'description' => 'View Database Backup', 'user_type' => 'Admin'],
            ['id' => '86', 'group' => 'Database Backup', 'name' => 'add_database_backup', 'display_name' => 'Add Database Backup', 'description' => 'Add Database Backup', 'user_type' => 'Admin'],
            ['id' => '87', 'group' => 'Database Backup', 'name' => 'edit_database_backup', 'display_name' => 'Edit Database Backup', 'description' => 'Edit Database Backup', 'user_type' => 'Admin'],
            ['id' => '88', 'group' => 'Database Backup', 'name' => 'delete_database_backup', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Meta
            ['id' => '89', 'group' => 'Meta', 'name' => 'view_meta', 'display_name' => 'View Meta', 'description' => 'View Meta', 'user_type' => 'Admin'],
            ['id' => '90', 'group' => 'Meta', 'name' => 'add_meta', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '91', 'group' => 'Meta', 'name' => 'edit_meta', 'display_name' => 'Edit Meta', 'description' => 'Edit Meta', 'user_type' => 'Admin'],
            ['id' => '92', 'group' => 'Meta', 'name' => 'delete_meta', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Page
            ['id' => '93', 'group' => 'Page', 'name' => 'view_page', 'display_name' => 'View Page', 'description' => 'View Page', 'user_type' => 'Admin'],
            ['id' => '94', 'group' => 'Page', 'name' => 'add_page', 'display_name' => 'Add Page', 'description' => 'Add Page', 'user_type' => 'Admin'],
            ['id' => '95', 'group' => 'Page', 'name' => 'edit_page', 'display_name' => 'Edit Page', 'description' => 'Edit Page', 'user_type' => 'Admin'],
            ['id' => '96', 'group' => 'Page', 'name' => 'delete_page', 'display_name' => 'Delete Page', 'description' => 'Delete Page', 'user_type' => 'Admin'],

            // Preference
            ['id' => '97', 'group' => 'Preference', 'name' => 'view_preference', 'display_name' => 'View Preference', 'description' => 'View Preference', 'user_type' => 'Admin'],
            ['id' => '98', 'group' => 'Preference', 'name' => 'add_preference', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '99', 'group' => 'Preference', 'name' => 'edit_preference', 'display_name' => 'Edit Preference', 'description' => 'Edit Preference', 'user_type' => 'Admin'],
            ['id' => '100', 'group' => 'Preference', 'name' => 'delete_preference', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Merchant
            ['id' => '101', 'group' => 'Merchant', 'name' => 'view_merchant', 'display_name' => 'View Merchant', 'description' => 'View Merchant', 'user_type' => 'Admin'],
            ['id' => '102', 'group' => 'Merchant', 'name' => 'add_merchant', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '103', 'group' => 'Merchant', 'name' => 'edit_merchant', 'display_name' => 'Edit Merchant', 'description' => 'Edit Merchant', 'user_type' => 'Admin'],
            ['id' => '104', 'group' => 'Merchant', 'name' => 'delete_merchant', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Merchant Payment
            ['id' => '105', 'group' => 'Merchant Payment', 'name' => 'view_merchant_payment', 'display_name' => 'View Merchant Payment', 'description' => 'View Merchant Payment', 'user_type' => 'Admin'],
            ['id' => '106', 'group' => 'Merchant Payment', 'name' => 'add_merchant_payment', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '107', 'group' => 'Merchant Payment', 'name' => 'edit_merchant_payment', 'display_name' => 'Edit Merchant Payment', 'description' => 'Edit Merchant Payment', 'user_type' => 'Admin'],
            ['id' => '108', 'group' => 'Merchant Payment', 'name' => 'delete_merchant_payment', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            /* FOR FRONTEND (User Panel)*/
            ['id' => '109', 'group' => 'Transaction', 'name' => 'manage_transaction', 'display_name' => 'Manage Transaction', 'description' => 'Manage Transaction', 'user_type' => 'User'],
            ['id' => '110', 'group' => 'Deposit', 'name' => 'manage_deposit', 'display_name' => 'Manage Deposit', 'description' => 'Manage Deposit', 'user_type' => 'User'],
            ['id' => '111', 'group' => 'Withdrawal', 'name' => 'manage_withdrawal', 'display_name' => 'Manage Withdrawal', 'description' => 'Manage Withdrawal', 'user_type' => 'User'],
            ['id' => '112', 'group' => 'Transfer', 'name' => 'manage_transfer', 'display_name' => 'Manage Transfer', 'description' => 'Manage Transfer', 'user_type' => 'User'],
            ['id' => '113', 'group' => 'Exchange', 'name' => 'manage_exchange', 'display_name' => 'Manage Exchange', 'description' => 'Manage Exchange', 'user_type' => 'User'],
            ['id' => '115', 'group' => 'Request Payment', 'name' => 'manage_request_payment', 'display_name' => 'Manage Request Payment', 'description' => 'Manage Request Payment', 'user_type' => 'User'],

            ['id' => '116', 'group' => 'Merchant', 'name' => 'manage_merchant', 'display_name' => 'Manage Merchant', 'description' => 'Manage Merchant', 'user_type' => 'User'],
            ['id' => '117', 'group' => 'Merchant Payment', 'name' => 'manage_merchant_payment', 'display_name' => 'Manage Merchant Payment', 'description' => 'Manage Merchant Payment', 'user_type' => 'User'],

            /* FOR BACKEND (Admin Panel)*/

            // User Group
            ['id' => '118', 'group' => 'User Group', 'name' => 'view_group', 'display_name' => 'View User Group', 'description' => 'View User Group', 'user_type' => 'Admin'],
            ['id' => '119', 'group' => 'User Group', 'name' => 'add_group', 'display_name' => 'Add User Group', 'description' => 'Add User Group', 'user_type' => 'Admin'],
            ['id' => '120', 'group' => 'User Group', 'name' => 'edit_group', 'display_name' => 'Edit User Group', 'description' => 'Edit User Group', 'user_type' => 'Admin'],
            ['id' => '121', 'group' => 'User Group', 'name' => 'delete_group', 'display_name' => 'Delete User Group', 'description' => 'Delete User Group', 'user_type' => 'Admin'],

            // Admins
            ['id' => '122', 'group' => 'Admins', 'name' => 'view_admins', 'display_name' => 'View Admins', 'description' => 'View Admins', 'user_type' => 'Admin'],
            ['id' => '123', 'group' => 'Admins', 'name' => 'add_admin', 'display_name' => 'Add Admin', 'description' => 'Add Admin', 'user_type' => 'Admin'],
            ['id' => '124', 'group' => 'Admins', 'name' => 'edit_admin', 'display_name' => 'Edit Admin', 'description' => 'Edit Admin', 'user_type' => 'Admin'],
            ['id' => '125', 'group' => 'Admins', 'name' => 'delete_admin', 'display_name' => 'Delete Admin', 'description' => 'Delete Admin', 'user_type' => 'Admin'],

            // Disputes
            ['id' => '126', 'group' => 'Disputes', 'name' => 'view_disputes', 'display_name' => 'View Disputes', 'description' => 'View Disputes', 'user_type' => 'Admin'],
            ['id' => '127', 'group' => 'Disputes', 'name' => 'add_dispute', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '128', 'group' => 'Disputes', 'name' => 'edit_dispute', 'display_name' => 'Edit Dispute', 'description' => 'Edit Dispute', 'user_type' => 'Admin'],
            ['id' => '129', 'group' => 'Disputes', 'name' => 'delete_dispute', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Tickets
            ['id' => '130', 'group' => 'Tickets', 'name' => 'view_tickets', 'display_name' => 'View Tickets', 'description' => 'View Tickets', 'user_type' => 'Admin'],
            ['id' => '131', 'group' => 'Tickets', 'name' => 'add_ticket', 'display_name' => 'Add Ticket', 'description' => 'Add Ticket', 'user_type' => 'Admin'],
            ['id' => '132', 'group' => 'Tickets', 'name' => 'edit_ticket', 'display_name' => 'Edit Ticket', 'description' => 'Edit Ticket', 'user_type' => 'Admin'],
            ['id' => '133', 'group' => 'Tickets', 'name' => 'delete_ticket', 'display_name' => 'Delete Ticket', 'description' => 'Delete Ticket', 'user_type' => 'Admin'],

            /* FOR FRONTEND (User Panel)*/
            ['id' => '134', 'group' => 'Dispute', 'name' => 'manage_dispute', 'display_name' => 'Manage Dispute', 'description' => 'Manage Dispute', 'user_type' => 'User'],
            ['id' => '135', 'group' => 'Ticket', 'name' => 'manage_ticket', 'display_name' => 'Manage Ticket', 'description' => 'Manage Ticket', 'user_type' => 'User'],
            ['id' => '136', 'group' => 'Settings', 'name' => 'manage_setting', 'display_name' => 'Manage Settings', 'description' => 'Manage Settings', 'user_type' => 'User'],

            /* FOR BACKEND (Admin Panel)*/
            ['id' => '137', 'group' => 'AppStore Credentials', 'name' => 'view_appstore_credentials', 'display_name' => 'View AppStore Credentials', 'description' => 'View AppStore Credentials', 'user_type' => 'Admin'],
            ['id' => '138', 'group' => 'AppStore Credentials', 'name' => 'add_appstore_credentials', 'display_name' => Null, 'description' => Null, 'user_type' => 'Admin'],
            ['id' => '139', 'group' => 'AppStore Credentials', 'name' => 'edit_appstore_credentials', 'display_name' => 'Edit AppStore Credentials', 'description' => 'Edit AppStore Credentials', 'user_type' => 'Admin'],
            ['id' => '140', 'group' => 'AppStore Credentials', 'name' => 'delete_appstore_credentials', 'display_name' => Null, 'description' => Null, 'user_type' => 'Admin'],

            ['id' => '145', 'group' => 'Merchant Groups', 'name' => 'view_merchant_group', 'display_name' => 'View Merchant Group', 'description' => 'View Merchant Group', 'user_type' => 'Admin'],
            ['id' => '146', 'group' => 'Merchant Groups', 'name' => 'add_merchant_group', 'display_name' => 'Add Merchant Group', 'description' => 'Add Merchant Group', 'user_type' => 'Admin'],
            ['id' => '147', 'group' => 'Merchant Groups', 'name' => 'edit_merchant_group', 'display_name' => 'Edit Merchant Group', 'description' => 'Edit Merchant Group', 'user_type' => 'Admin'],
            ['id' => '148', 'group' => 'Merchant Groups', 'name' => 'delete_merchant_group', 'display_name' => 'Delete Merchant Group', 'description' => 'Delete Merchant Group', 'user_type' => 'Admin'],

            // Sms Setting
            ['id' => '149', 'group' => 'SMS Setting', 'name' => 'view_sms_setting', 'display_name' => 'View SMS Setting', 'description' => 'View SMS Setting', 'user_type' => 'Admin'],
            ['id' => '150', 'group' => 'SMS Setting', 'name' => 'add_sms_setting', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '151', 'group' => 'SMS Setting', 'name' => 'edit_sms_setting', 'display_name' => 'Edit SMS Setting', 'description' => 'Edit SMS Setting', 'user_type' => 'Admin'],
            ['id' => '152', 'group' => 'SMS Setting', 'name' => 'delete_sms_setting', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // sms Templates
            ['id' => '153', 'group' => 'Sms Template', 'name' => 'view_sms_template', 'display_name' => 'View Sms Template', 'description' => 'View Sms Template', 'user_type' => 'Admin'],
            ['id' => '154', 'group' => 'Sms Template', 'name' => 'add_sms_template', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '155', 'group' => 'Sms Template', 'name' => 'edit_sms_template', 'display_name' => 'Edit Sms Template', 'description' => 'Edit Sms Template', 'user_type' => 'Admin'],
            ['id' => '156', 'group' => 'Sms Template', 'name' => 'delete_sms_template', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Identity
            ['id' => '157', 'group' => 'Identity Verificattion', 'name' => 'view_identity_verfication', 'display_name' => 'View Identity Verificattion', 'description' => 'View Identity Verificattion', 'user_type' => 'Admin'],
            ['id' => '158', 'group' => 'Identity Verificattion', 'name' => 'add_identity_verfication', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '159', 'group' => 'Identity Verificattion', 'name' => 'edit_identity_verfication', 'display_name' => 'Edit Identity Verificattion', 'description' => 'Edit Identity Verificattion', 'user_type' => 'Admin'],
            ['id' => '160', 'group' => 'Identity Verificattion', 'name' => 'delete_identity_verfication', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Address
            ['id' => '161', 'group' => 'Address Verificattion', 'name' => 'view_address_verfication', 'display_name' => 'View Address Verificattion', 'description' => 'View Address Verificattion', 'user_type' => 'Admin'],
            ['id' => '162', 'group' => 'Address Verificattion', 'name' => 'add_address_verfication', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '163', 'group' => 'Address Verificattion', 'name' => 'edit_address_verfication', 'display_name' => 'Edit Address Verificattion', 'description' => 'Edit Address Verificattion', 'user_type' => 'Admin'],
            ['id' => '164', 'group' => 'Address Verificattion', 'name' => 'delete_address_verfication', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // woocommerce [165-168, but recently removed]
            
            // Admin Security
            ['id' => '165', 'group' => 'Admin Security', 'name' => 'view_admin_security', 'display_name' => 'View Admin Security', 'description' => 'View Admin Security', 'user_type' => 'Admin'],
            ['id' => '166', 'group' => 'Admin Security', 'name' => 'add_admin_security', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '167', 'group' => 'Admin Security', 'name' => 'edit_admin_security', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '168', 'group' => 'Admin Security', 'name' => 'delete_admin_security', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Notification Type
            ['id' => '169', 'group' => 'Notification Type', 'name' => 'view_notification_type', 'display_name' => 'View Notification Type', 'description' => 'View Notification Type', 'user_type' => 'Admin'],
            ['id' => '170', 'group' => 'Notification Type', 'name' => 'add_notification_type', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '171', 'group' => 'Notification Type', 'name' => 'edit_notification_type', 'display_name' => 'Edit Notification Type', 'description' => 'Edit Notification Type', 'user_type' => 'Admin'],
            ['id' => '172', 'group' => 'Notification Type', 'name' => 'delete_notification_type', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Notification Settings
            ['id' => '173', 'group' => 'Notification Setting', 'name' => 'view_notification_setting', 'display_name' => 'View Notification Setting', 'description' => 'View Notification Setting', 'user_type' => 'Admin'],
            ['id' => '174', 'group' => 'Notification Setting', 'name' => 'add_notification_setting', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '175', 'group' => 'Notification Setting', 'name' => 'edit_notification_setting', 'display_name' => 'Edit Notification Setting', 'description' => 'Edit Notification Setting', 'user_type' => 'Admin'],
            ['id' => '176', 'group' => 'Notification Setting', 'name' => 'delete_notification_setting', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Converion rate api
            ['id' => '177', 'group' => 'Conversion Rate Api', 'name' => 'view_conversion_rate_api', 'display_name' => 'View Conversion Rate Api', 'description' => 'View Conversion Rate Api', 'user_type' => 'Admin'],
            ['id' => '178', 'group' => 'Conversion Rate Api', 'name' => 'add_conversion_rate_api', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '179', 'group' => 'Conversion Rate Api', 'name' => 'edit_conversion_rate_api', 'display_name' => 'Edit Conversion Rate Api', 'description' => 'Edit Conversion Rate Api', 'user_type' => 'Admin'],
            ['id' => '180', 'group' => 'Conversion Rate Api', 'name' => 'delete_conversion_rate_api', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            
            // Addon Manager
            ['id' => '181', 'group' => 'Addon Manager', 'name' => 'view_addon_manager', 'display_name' => 'View Addon Manager', 'description' => 'View Addon Manager', 'user_type' => 'Admin'],
            ['id' => '182', 'group' => 'Addon Manager', 'name' => 'add_addon_manager', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '183', 'group' => 'Addon Manager', 'name' => 'edit_addon_manager', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '184', 'group' => 'Addon Manager', 'name' => 'delete_addon_manager', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            
            // Crypto Providers
            ['id' => '185', 'group' => 'Crypto Provider', 'name' => 'view_crypto_provider', 'display_name' => 'View Crypto Provider', 'description' => 'View Addon Manager', 'user_type' => 'Admin'],
            ['id' => '186', 'group' => 'Crypto Provider', 'name' => 'add_crypto_provider', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '187', 'group' => 'Crypto Provider', 'name' => 'edit_crypto_provider', 'display_name' => 'Edit Crypto Provider', 'description' => null, 'user_type' => 'Admin'],
            ['id' => '188', 'group' => 'Crypto Provider', 'name' => 'delete_crypto_provider', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            
            // Crypto Asset
            ['id' => '189', 'group' => 'Crypto Asset', 'name' => 'view_crypto_asset', 'display_name' => 'View Crypto Asset', 'description' => 'View Crypto Asset', 'user_type' => 'Admin'],
            ['id' => '190', 'group' => 'Crypto Asset', 'name' => 'add_crypto_asset', 'display_name' => 'Add Crypto Asset', 'description' => 'Add Crypto Asset', 'user_type' => 'Admin'],
            ['id' => '191', 'group' => 'Crypto Asset', 'name' => 'edit_crypto_asset', 'display_name' => 'Edit Crypto Asset', 'description' => 'Edit Crypto Asset', 'user_type' => 'Admin'],
            ['id' => '192', 'group' => 'Crypto Asset', 'name' => 'delete_crypto_asset', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            
            // Crypto Transactions
            ['id' => '193', 'group' => 'Crypto Transactions', 'name' => 'view_crypto_transactions', 'display_name' => 'View Crypto Transactions', 'description' => 'View Crypto Transactions', 'user_type' => 'Admin'],
            ['id' => '194', 'group' => 'Crypto Transactions', 'name' => 'add_crypto_transactions', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '195', 'group' => 'Crypto Transactions', 'name' => 'edit_crypto_transactions', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '196', 'group' => 'Crypto Transactions', 'name' => 'delete_crypto_transactions', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],

            // Crypto Send Receive User Panel
            ['id' => '197', 'group' => 'Crypto Send/Receive', 'name' => 'manage_crypto_send_receive', 'display_name' => 'Manage Crypto Send/Receive', 'description' => 'Manage Crypto Send/Receive', 'user_type' => 'User'],

            // Cache Clear
            ['id' => '198', 'group' => 'Cache Clear', 'name' => 'view_cache_clear', 'display_name' => 'View Cache Clear', 'description' => 'View Cache Clear', 'user_type' => 'Admin'],
            ['id' => '199', 'group' => 'Cache Clear', 'name' => 'add_cache_clear', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '200', 'group' => 'Cache Clear', 'name' => 'edit_cache_clear', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['id' => '201', 'group' => 'Cache Clear', 'name' => 'delete_cache_clear', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
        ];

        Permission::truncate();
        foreach ($permissions as $value) {
            Permission::create($value);
        }
    }
}
