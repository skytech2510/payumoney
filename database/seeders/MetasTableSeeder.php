<?php

namespace Database\Seeders;

use App\Models\Meta;
use Illuminate\Database\Seeder;

class MetasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Meta::truncate();

        $metas = [
            ['url' => 'help', 'title' => 'Help', 'description' => 'Help', 'keywords' => ''],

            ['url' => 'about-us', 'title' => 'About Us', 'description' => 'About Us', 'keywords' => ''],

            ['url' => 'contact-us', 'title' => 'Contact Us', 'description' => 'Contact Us', 'keywords' => ''],

            ['url' => 'header', 'title' => 'Header', 'description' => 'Header', 'keywords' => ''],

            ['url' => 'login', 'title' => 'Login', 'description' => 'Login', 'keywords' => ''],

            ['url' => 'register', 'title' => 'Register', 'description' => 'Register', 'keywords' => ''],

            ['url' => '/', 'title' => 'Home', 'description' => 'Home', 'keywords' => ''],

            ['url' => 'dashboard', 'title' => 'Dashboard', 'description' => 'Dashboard', 'keywords' => ''],

            ['url' => 'moneytransfer', 'title' => 'Money Transfer', 'description' => 'Money Transfer', 'keywords' => ''],

            ['url' => 'transactions', 'title' => 'Transactions', 'description' => 'Transactions', 'keywords' => ''],

            ['url' => 'exchanges', 'title' => 'Money Exchange', 'description' => 'Money Exchange', 'keywords' => ''],

            ['url' => 'exchange', 'title' => 'Money Exchange', 'description' => 'Money Exchange', 'keywords' => ''],

            ['url' => 'merchants', 'title' => 'Merchant', 'description' => 'Merchant', 'keywords' => ''],

            ['url' => 'merchant/add', 'title' => 'Add Merchant', 'description' => 'Add Merchant', 'keywords' => ''],

            ['url' => 'request_payments', 'title' => 'Request Payments', 'description' => 'Request Payments', 'keywords' => ''],

            ['url' => 'request_payment/add', 'title' => 'Request Payment', 'description' => 'Request Payment', 'keywords' => ''],

            ['url' => 'request_payment/edit/{id}', 'title' => 'Request Payment', 'description' => 'Request Payment', 'keywords' => ''],

            ['url' => 'request_payment/detail/{id}', 'title' => 'Request Payment', 'description' => 'Request Payment', 'keywords' => ''],

            ['url' => 'request_payment/completes', 'title' => 'Request Payment', 'description' => 'Request Payment', 'keywords' => ''],

            ['url' => 'exchange/view/{id}', 'title' => 'Money Exchange', 'description' => 'Money Exchange', 'keywords' => ''],

            ['url' => 'merchant/edit/{id}', 'title' => 'Edit Merchant', 'description' => 'Edit Merchant', 'keywords' => ''],

            ['url' => 'merchant/payments', 'title' => 'Merchant payments', 'description' => 'Merchant payments', 'keywords' => ''],

            ['url' => 'deposit', 'title' => 'Deposit', 'description' => 'Deposit', 'keywords' => ''],

            ['url' => 'deposit/method/{id}', 'title' => 'Deposit Amount', 'description' => 'Deposit Amount', 'keywords' => ''],

            ['url' => 'deposit/stripe_payment', 'title' => 'Deposit With Stripe', 'description' => 'Deposit With Stripe', 'keywords' => ''],

            ['url' => 'payout', 'title' => 'Withdraw', 'description' => 'Withdraw', 'keywords' => ''],

            ['url' => 'withdrawal/method/{id}', 'title' => 'Withdraw', 'description' => 'Withdraw', 'keywords' => ''],

            ['url' => 'payouts', 'title' => 'Withdrawals', 'description' => 'Withdrawals', 'keywords' => ''],

            ['url' => 'payout/store', 'title' => 'Withdraw', 'description' => 'Withdraw', 'keywords' => ''],

            ['url' => 'transactions/{id}', 'title' => 'Transactions', 'description' => 'Transactions', 'keywords' => ''],

            ['url' => 'request_payment/accept/{id}', 'title' => 'Request Payment', 'description' => 'Request Payment', 'keywords' => ''],

            ['url' => 'request_payment/accept/{id}', 'title' => 'Request Payment', 'description' => 'Request Payment', 'keywords' => ''],

            ['url' => 'disputes', 'title' => 'Disputes', 'description' => 'Disputes', 'keywords' => ''],

            ['url' => 'merchant/detail/{id}', 'title' => 'View Merchant', 'description' => 'View Merchant', 'keywords' => ''],

            ['url' => 'dispute/discussion/{id}', 'title' => 'Dispute Details', 'description' => 'Dispute Details', 'keywords' => ''],

            ['url' => 'dispute/add/{id}', 'title' => 'Dispute Add', 'description' => 'Dispute Add', 'keywords' => ''],

            ['url' => 'send-money', 'title' => 'Send Money', 'description' => 'Send Money', 'keywords' => ''],

            ['url' => 'request-money', 'title' => 'Request Money', 'description' => 'Request Money', 'keywords' => ''],

            ['url' => 'news', 'title' => 'News', 'description' => 'News', 'keywords' => ''],

            ['url' => 'profile', 'title' => 'User Profile', 'description' => 'User Profile', 'keywords' => ''],

            ['url' => 'tickets', 'title' => 'Tickets', 'description' => 'Tickets', 'keywords' => ''],

            ['url' => 'ticket/add', 'title' => 'Add Ticket', 'description' => 'Add Ticket', 'keywords' => ''],

            ['url' => 'ticket/reply/{id}', 'title' => 'Ticket Reply', 'description' => 'Ticket Reply', 'keywords' => ''],

            ['url' => 'exchange_of_base_currency', 'title' => 'Money Exchange', 'description' => 'Money Exchange', 'keywords' => ''],

            ['url' => 'exchange/exchange-of-base-currency-confirm', 'title' => 'Money Exchange', 'description' => 'Money Exchange', 'keywords' => ''],

            ['url' => 'deposit/stripe_payment_store', 'title' => 'Deposit With Stripe', 'description' => 'Deposit With Stripe', 'keywords' => ''],

            ['url' => 'payout/setting', 'title' => 'Withdraw', 'description' => 'Withdraw', 'keywords' => ''],

            ['url' => 'send-money-confirm', 'title' => 'Money Transfer', 'description' => 'Money Transfer', 'keywords' => ''],

            ['url' => 'exchange_to_base_currency', 'title' => 'Money Exchange', 'description' => 'Money Exchange', 'keywords' => ''],

            ['url' => 'exchange/exchange-to-base-currency-confirm', 'title' => 'Money Exchange', 'description' => 'Money Exchange', 'keywords' => ''],

            ['url' => 'portfolio', 'title' => 'Portfolio', 'description' => 'Portfolio', 'keywords' => ''],

            ['url' => 'request_payment/store', 'title' => 'Request Payment', 'description' => 'Request Payment', 'keywords' => ''],

            ['url' => 'forget-password', 'title' => 'Forgot Password', 'description' => 'Forgot Password', 'keywords' => ''],

            ['url' => 'password/resets/{token}', 'title' => 'Reset Password', 'description' => 'Reset Password', 'keywords' => ''],

            ['url' => 'request-money-confirm', 'title' => 'Request Money', 'description' => 'Request Money', 'keywords' => ''],

            ['url' => 'request_payment/accepted', 'title' => 'Request Payment', 'description' => 'Request Payment', 'keywords' => ''],

            ['url' => 'request_payment/accept-money-confirm', 'title' => 'Request Payment', 'description' => 'Request Payment', 'keywords' => ''],

            ['url' => 'deposit/stripe_payment_store', 'title' => 'Deposit With Stripe', 'description' => 'Deposit With Stripe', 'keywords' => ''],

            ['url' => 'policies', 'title' => 'Policies', 'description' => 'Policies', 'keywords' => ''],

            ['url' => 'transfer', 'title' => 'Money Transfer', 'description' => 'Money Transfer', 'keywords' => ''],

            ['url' => 'withdrawal/confirm-transaction', 'title' => 'Withdraw', 'description' => 'Withdraw', 'keywords' => ''],

            ['url' => 'request', 'title' => 'Request Payment', 'description' => 'Request Payment', 'keywords' => ''],

            ['url' => 'deposit/payumoney_success', 'title' => 'Deposit With PayUMoney', 'description' => 'Deposit With PayUMoney', 'keywords' => ''],

            ['url' => 'deposit/payment_success', 'title' => 'Deposit Success', 'description' => 'Deposit With PayMoney Success', 'keywords' => ''],

            ['url' => 'developer', 'title' => 'Developer', 'description' => 'Developer Page', 'keywords' => ''],

            ['url' => 'phone-verification', 'title' => 'Verfy Phone', 'description' => 'Verfy Phone', 'keywords' => ''],

            ['url' => 'authenticate', 'title' => '2-Factor Authentication', 'description' => '2-Factor Authentication', 'keywords' => ''],

            ['url' => 'profile/2fa', 'title' => '2-FA', 'description' => '2-FA', 'keywords' => ''],

            ['url' => '2fa', 'title' => '2-Factor Authentication', 'description' => '2-Factor Authentication', 'keywords' => ''],

            ['url' => 'deposit/bank-payment', 'title' => 'Deposit', 'description' => 'Deposit', 'keywords' => ''],

            ['url' => 'bank-transfer', 'title' => 'Bank Transfer', 'description' => 'Bank Transfer', 'keywords' => ''],

            ['url' => 'bank-transfer/confirm', 'title' => 'Bank Transfer', 'description' => 'Bank Transfer', 'keywords' => ''],

            ['url' => 'bank-transfer/success', 'title' => 'Bank Transfer', 'description' => 'Bank Transfer', 'keywords' => ''],

            ['url' => 'google2fa', 'title' => 'Google 2FA', 'description' => 'Google 2FA', 'keywords' => ''],

            ['url' => 'profile/personal-id', 'title' => 'Identity Verification', 'description' => 'Identity Verification', 'keywords' => ''],

            ['url' => 'profile/personal-address', 'title' => 'Address Verification', 'description' => 'Address Verification', 'keywords' => ''],

            ['url' => 'exchange-of-money', 'title' => 'Money Exchange', 'description' => 'Money Exchange', 'keywords' => ''],

            ['url' => 'exchange-of-money-success', 'title' => 'Money Exchange', 'description' => 'Money Exchange', 'keywords' => ''],

            ['url' => 'deposit/bank-payment', 'title' => 'Deposit', 'description' => 'Deposit', 'keywords' => ''],

            ['url' => 'deposit/payeer/payment/success', 'title' => 'Deposit With Payeer', 'description' => 'Deposit With Payeer', 'keywords' => ''],

            ['url' => 'deposit/checkout/payment/success', 'title' => 'Deposit with 2checkout', 'description' => 'Deposit with 2checkout', 'keywords' => ''],

            ['url' => 'merchant/payment', 'title' => 'Merchant Payment', 'description' => 'Merchant Payment', 'keywords' => ''],

            ['url' => 'check-user-status', 'title' => 'Suspended!', 'description' => 'Suspended', 'keywords' => ''],

            ['url' => 'check-request-creator-suspended-status', 'title' => 'Suspended', 'description' => 'Suspended', 'keywords' => ''],

            ['url' => 'check-request-creator-inactive-status', 'title' => 'Inactive', 'description' => 'Inactive', 'keywords' => ''],

            ['url' => 'deposit/stripe-payment/success', 'title' => 'Deposit With Stripe', 'description' => 'Deposit With Stripe', 'keywords' => ''],

            ['url' => 'deposit/paypal-payment/success', 'title' => 'Deposit With PayPal', 'description' => 'Deposit With PayPal', 'keywords' => ''],

            ['url' => 'deposit/bank-payment/success', 'title' => 'Deposit With Bank', 'description' => 'Deposit With Bank', 'keywords' => ''],

            ['url' => 'wallet-list', 'title' => 'Wallet List', 'description' => 'Wallet List', 'keywords' => ''],

            ['url' => 'deposit/store', 'title' => 'Deposit Fund', 'description' => 'Deposit', 'keywords' => ''],
            ['url' => 'deposit/coinpayment-transaction-info', 'title' => 'Coinpayment Summery', 'description' => 'Coinpayment Summery', 'keywords' => ''],

            ['url' => 'deposit/paypal-payment/success/{amount}', 'title' => 'Deposit With PayPal', 'description' => 'Deposit With PayPal', 'keywords' => ''],

            ['url' => 'register/store-personal-info', 'title' => 'Register User Type', 'description' => 'Register User Type', 'keywords' => ''],

            ['url' => 'privacy-policy', 'title' => 'Privacy Policy', 'description' => 'Privacy Policy', 'keywords' => ''],

            ['url' => 'deposit/confirm', 'title' => 'Deposit confirm', 'description' => 'Deposit confirm', 'keywords' => ''],

            ['url' => 'deposit/success', 'title' => 'Deposit Success', 'description' => 'Deposit Success', 'keywords' => ''],

        ];
        foreach ($metas as $key => $value)
        {
            Meta::create($value);
        }
    }
}
