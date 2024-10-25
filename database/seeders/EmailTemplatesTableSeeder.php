<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EmailTemplate::truncate();
        EmailTemplate::insert([

            // Deposit  [en, ar, fr, pt, ru, es, tr, ch]
            // Notify to Aamin on user deposit
            [
                'name' => 'Notify Admin on Deposit',
                'alias' => 'notify-admin-on-deposit',
                'subject' => 'Money Deposit Notification',
                'body' => 'Hi <b>{admin}</b>,
                    <br><br>Amount <b>{amount}</b> has been deposited by <b>{user}</br>
                    <br><br><b><u><i>Here’s a brief overview of the Deposit:</i></u></b>
                    <br><br><b><u>Deposited at:</u></b> {created_at}
                    <br><br><b><u>Deposited via:</u></b> {payment_method}
                    <br><br><b><u>Transaction ID:</u></b> {uuid}
                    <br><br><b><u>Currency:</u></b> {code}
                    <br><br><b><u>Amount:</u></b> {amount}
                    <br><br><b><u>Fee:</u></b> {fee}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Deposit',
                'status' => 'Active',
            ],
            ['name' => 'Notify Admin on Deposit', 'alias' => 'notify-admin-on-deposit', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Deposit', 'status' => 'Active'],
            ['name' => 'Notify Admin on Deposit', 'alias' => 'notify-admin-on-deposit', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Deposit', 'status' => 'Active'],
            ['name' => 'Notify Admin on Deposit', 'alias' => 'notify-admin-on-deposit', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Deposit', 'status' => 'Active'],
            ['name' => 'Notify Admin on Deposit', 'alias' => 'notify-admin-on-deposit', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Deposit', 'status' => 'Active'],
            ['name' => 'Notify Admin on Deposit', 'alias' => 'notify-admin-on-deposit', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Deposit', 'status' => 'Active'],
            ['name' => 'Notify Admin on Deposit', 'alias' => 'notify-admin-on-deposit', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Deposit', 'status' => 'Active'],
            ['name' => 'Notify Admin on Deposit', 'alias' => 'notify-admin-on-deposit', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Deposit', 'status' => 'Active'],

            // Deposit
            // Notify to User on deposit via Admin
            [
                'name' => 'Notify User on Deposit via Admin',
                'alias' => 'notify-user-on-deposit-via-admin',
                'subject' => 'Deposit via System Administrator',
                'body' => 'Hi <b>{user}</b>,
                    <br><br>Amount <b>{amount}</b> has been deposited to your account by System Administrator.
                    <br><br><b><u><i>Here’s a brief overview of the Deposit:</i></u></b>
                    <br><br><b><u>Deposited at:</u></b> {created_at}
                    <br><br><b><u>Transaction ID:</u></b> {uuid}
                    <br><br><b><u>Currency:</u></b> {code}
                    <br><br><b><u>Amount:</u></b> {amount}
                    <br><br><b><u>Fee:</u></b> {fee}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Deposit',
                'status' => 'Active',
            ],
            ['name' => 'Notify User on Deposit via Admin', 'alias' => 'notify-user-on-deposit-via-admin', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Deposit', 'status' => 'Active'],
            ['name' => 'Notify User on Deposit via Admin', 'alias' => 'notify-user-on-deposit-via-admin', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Deposit', 'status' => 'Active'],
            ['name' => 'Notify User on Deposit via Admin', 'alias' => 'notify-user-on-deposit-via-admin', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Deposit', 'status' => 'Active'],
            ['name' => 'Notify User on Deposit via Admin', 'alias' => 'notify-user-on-deposit-via-admin', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Deposit', 'status' => 'Active'],
            ['name' => 'Notify User on Deposit via Admin', 'alias' => 'notify-user-on-deposit-via-admin', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Deposit', 'status' => 'Active'],
            ['name' => 'Notify User on Deposit via Admin', 'alias' => 'notify-user-on-deposit-via-admin', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Deposit', 'status' => 'Active'],
            ['name' => 'Notify User on Deposit via Admin', 'alias' => 'notify-user-on-deposit-via-admin', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Deposit', 'status' => 'Active'],

            # Send Money  [en, ar, fr, pt, ru, es, tr, ch]
            // Notify money receiver on Send Money
            [
                'name' => 'Notify Money Receiver',
                'alias' => 'notify-money-receiver',
                'subject' => 'Money Received Notification',
                'body' => 'Hi <b>{receiver_id}</b>,
                    <br><br>You have received {amount} on your account.
                    <br><br>
                    <b><u><i>Here’s a brief overview of your Received:</i></u></b>
                    <br><br><b><u>Transferred at:</u></b> {created_at}
                    <br><br><b><u>Transaction ID:</u></b> {uuid}
                    <br><br><b><u>Sent By:</u></b> {sender_id}
                    <br><br><b><u>Amount:</u></b> {amount}
                    <br><br><b><u>Note:</u></b> {note}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Send Money',
                'status' => 'Active',
            ],
            ['name' => 'Notify Money Receiver', 'alias' => 'notify-money-receiver', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Money Receiver', 'alias' => 'notify-money-receiver', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Money Receiver', 'alias' => 'notify-money-receiver', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Money Receiver', 'alias' => 'notify-money-receiver', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Money Receiver', 'alias' => 'notify-money-receiver', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Money Receiver', 'alias' => 'notify-money-receiver', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Money Receiver', 'alias' => 'notify-money-receiver', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Send Money', 'status' => 'Active'],

            # Send Money
            // Notify Admin on Send Money
            [
                'name' => 'Notify Admin on Transfer',
                'alias' => 'notify-admin-on-transfer',
                'subject' => 'Money Transfer Notification',
                'body' => 'Hi <b>{admin}</b>,
                    <br><br><b>{sender}</b> has been transferred <b>{amount}</b> to <b>{receiver}</b>.
                    <br><br><b><u><i>Here’s a brief overview of the Transfer:</i></u></b>
                    <br><br><b><u>Transferred at:</u></b> {created_at}
                    <br><br><b><u>Transaction ID:</u></b> {uuid}
                    <br><br><b><u>Sent By:</u></b> {sender}
                    <br><br><b><u>Received By:</u></b> {receiver}
                    <br><br><b><u>Amount:</u></b> {amount}
                    <br><br><b><u>Fee (deducted from {sender}):</u></b> {fee}
                    <br><br><b><u>Note:</u></b> {note}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Send Money',
                'status' => 'Active',
            ],
            ['name' => 'Notify Admin on Transfer', 'alias' => 'notify-admin-on-transfer', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Admin on Transfer', 'alias' => 'notify-admin-on-transfer', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Admin on Transfer', 'alias' => 'notify-admin-on-transfer', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Admin on Transfer', 'alias' => 'notify-admin-on-transfer', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Admin on Transfer', 'alias' => 'notify-admin-on-transfer', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Admin on Transfer', 'alias' => 'notify-admin-on-transfer', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Admin on Transfer', 'alias' => 'notify-admin-on-transfer', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Send Money', 'status' => 'Active'],


            # Request Money [en, ar, fr, pt, ru, es, tr, ch]
            // Notify request receiver
            [
                'name' => 'Notify Request Receiver',
                'alias' => 'notify-request-receiver',
                'subject' => 'Request Money Notification',
                'body' => 'Hi {request_receiver},
                    <br><br>{request_sender} has been requested {amount} from you.
                    <br><br><b><u><i>Here’s a brief overview of the Request:</i></u></b>
                    <br><br>
                    <b><u>Request:</u></b> #{uuid}
                    <br><br>
                    <b><u>Requested At:</u></b> {created_at}
                    <br><br>
                    <b><u>Requested Amount:</u></b> {amount}
                    <br><br>
                    <b><u>Note: </u></b> {note}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Request Money',
                'status' => 'Active',
            ],
            ['name' => 'Notify Request Receiver', 'alias' => 'notify-request-receiver', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Receiver', 'alias' => 'notify-request-receiver', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Receiver', 'alias' => 'notify-request-receiver', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Receiver', 'alias' => 'notify-request-receiver', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Receiver', 'alias' => 'notify-request-receiver', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Receiver', 'alias' => 'notify-request-receiver', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Receiver', 'alias' => 'notify-request-receiver', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            
            # Request Money
            // Notify request sender on request received
            [
                'name' => 'Notify Request Sender on Money Received',
                'alias' => 'notify-request-sender-on-money-received',
                'subject' => 'Requested Money Received Notification',
                'body' => 'Hi {request_sender},
                    <br><br>Your request of #{uuid} has been received by {request_receiver}.
                    <br><br><b><u><i>Here’s a brief overview of the Request:</i></u></b>
                    <br><br>
                    <b><u>Received Date:</u></b> {created_at}.
                    <br><br>
                    <b><u>Requested Amount:</u></b> {amount}
                    <br><br>
                    <b><u>Received Amount:</u></b> {accept_amount}
                    <br><br>
                    <b><u>Currency:</u></b> {currency}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Request Money',
                'status' => 'Active',
            ],
            ['name' => 'Notify Request Sender on Money Received', 'alias' => 'notify-request-sender-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Sender on Money Received', 'alias' => 'notify-request-sender-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Sender on Money Received', 'alias' => 'notify-request-sender-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Sender on Money Received', 'alias' => 'notify-request-sender-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Sender on Money Received', 'alias' => 'notify-request-sender-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Sender on Money Received', 'alias' => 'notify-request-sender-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Sender on Money Received', 'alias' => 'notify-request-sender-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],

            # Request Money
            // Notify Admin on money request received
            [
                'name' => 'Notify Admin on Money Received',
                'alias' => 'notify-admin-on-money-received',
                'subject' => 'Request Money Received Notification',
                'body' => 'Hi {admin},
                    <br><br>Money request of #{uuid} from {request_sender} has been received by {request_receiver}.
                    <br><br><b><u><i>Here’s a brief overview of the Request Acceptance:</i></u></b>
                    <br><br><b><u>Received at:</u></b> {created_at}
                    <br><br><b><u>Transaction ID:</u></b> {uuid}
                    <br><br><b><u>Currency:</u></b> {code}
                    <br><br><b><u>Requested By:</u></b> {request_sender}
                    <br><br><b><u>Received By:</u></b> {request_receiver}
                    <br><br><b><u>Requested Amount:</u></b> {request_amount}
                    <br><br><b><u>Given Amount:</u></b> {given_amount}
                    <br><br><b><u>Fee (deducted from {request_receiver}):</u></b> {fee}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Request Money', 
                'status' => 'Active'
            ],
            ['name' => 'Notify Admin on Money Received', 'alias' => 'notify-admin-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Admin on Money Received', 'alias' => 'notify-admin-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Admin on Money Received', 'alias' => 'notify-admin-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Admin on Money Received', 'alias' => 'notify-admin-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Admin on Money Received', 'alias' => 'notify-admin-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Admin on Money Received', 'alias' => 'notify-admin-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Admin on Money Received', 'alias' => 'notify-admin-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Request Money', 'status' => 'Active'],

            # Exchange [en, ar, fr, pt, ru, es, tr, ch]
            // Notify to admin on money exchange
            [
                'name' => 'Notify Admin on Exchange',
                'alias' => 'notify-admin-on-exchange',
                'subject' => 'Money Exchange Notification',
                'body' => 'Hi <b>{admin}</b>,
                    <br><br>Amount <b>{amount}</b> has been exchanged by <b>{user}</b>
                    <br><br><b><u><i>Here’s a brief overview of the Exchange:</i></u></b>
                    <br><br><b><u>Exchanged at:</u></b> {created_at}
                    <br><br><b><u>Transaction ID:</u></b> {uuid}
                    <br><br><b><u>From wallet:</u></b> {from_wallet}
                    <br><br><b><u>To wallet:</u></b> {to_wallet}
                    <br><br><b><u>Exchanged Amount:</u></b> {exchanged_amount}
                    <br><br><b><u>Exchange Rate:</u></b> 1 {from_wallet} = {exchange_rate} {to_wallet}
                    <br><br><b><u>Fee (deducted from {from_wallet}):</u></b> {fee}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Exchange',
                'status' => 'Active',
            ],
            ['name' => 'Notify Admin on Exchange', 'alias' => 'notify-admin-on-exchange', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Exchange', 'alias' => 'notify-admin-on-exchange', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Exchange', 'alias' => 'notify-admin-on-exchange', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Exchange', 'alias' => 'notify-admin-on-exchange', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Exchange', 'alias' => 'notify-admin-on-exchange', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Exchange', 'alias' => 'notify-admin-on-exchange', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Exchange', 'status' => 'Active'],
            ['name' => 'Notify Admin on Exchange', 'alias' => 'notify-admin-on-exchange', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Exchange', 'status' => 'Active'],

            # Withdraw [en, ar, fr, pt, ru, es, tr, ch]
            // Notify to admin on user withdraw money
            [
                'name' => 'Notify Admin on Withdrawal',
                'alias' => 'notify-admin-on-withdrawal',
                'subject' => 'Money Withdrawal Notification',
                'body' => 'Hi <b>{admin}</b>,
                    <br><br><b>{amount}</b> has been withdrawn by <b>{user}</b>.
                    <br><br><b><u><i>Here’s a brief overview of the Withdrawn:</i></u></b>
                    <br><br><b><u>Withdrawn at:</u></b> {created_at}
                    <br><br><b><u>Transaction ID:</u></b> {uuid}
                    <br><br><b><u>Withdraw via:</u></b> {payment_method}
                    <br><br><b><u>Currency:</u></b> {code}
                    <br><br><b><u>Amount:</u></b> {amount}
                    <br><br><b><u>Fee:</u></b> {fee}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Withdraw', 
                'status' => 'Active'
            ],
            ['name' => 'Notify Admin on Withdrawal', 'alias' => 'notify-admin-on-withdrawal', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Withdraw', 'status' => 'Active'],
            ['name' => 'Notify Admin on Withdrawal', 'alias' => 'notify-admin-on-withdrawal', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Withdraw', 'status' => 'Active'],
            ['name' => 'Notify Admin on Withdrawal', 'alias' => 'notify-admin-on-withdrawal', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Withdraw', 'status' => 'Active'],
            ['name' => 'Notify Admin on Withdrawal', 'alias' => 'notify-admin-on-withdrawal', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Withdraw', 'status' => 'Active'],
            ['name' => 'Notify Admin on Withdrawal', 'alias' => 'notify-admin-on-withdrawal', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Withdraw', 'status' => 'Active'],
            ['name' => 'Notify Admin on Withdrawal', 'alias' => 'notify-admin-on-withdrawal', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Withdraw', 'status' => 'Active'],
            ['name' => 'Notify Admin on Withdrawal', 'alias' => 'notify-admin-on-withdrawal', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Withdraw', 'status' => 'Active'],

            # Withdraw 
            // Notify user on withdraw money via admin
            [
                'name' => 'Notify User on Withdrawal via Admin',
                'alias' => 'notify-user-on-withdrawal-via-admin',
                'subject' => 'Money Withdrawal Notification',
                'body' => 'Hi <b>{user}</b>,
                    <br><br><b>{amount}</b> has been withdrawn from your account by System Administrator.
                    <br><br><b><u><i>Here’s a brief overview of the withdrawn:</i></u></b>
                    <br><br><b><u>Withdrawn at:</u></b> {created_at}
                    <br><br><b><u>Transaction ID:</u></b> {uuid}
                    <br><br><b><u>Currency:</u></b> {code}
                    <br><br><b><u>Amount:</u></b> {amount}
                    <br><br><b><u>Fee:</u></b> {fee}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Withdraw', 
                'status' => 'Active'
            ],
            ['name' => 'Notify User on Withdrawal via Admin', 'alias' => 'notify-user-on-withdrawal-via-admin', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Withdraw', 'status' => 'Active'],
            ['name' => 'Notify User on Withdrawal via Admin', 'alias' => 'notify-user-on-withdrawal-via-admin', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Withdraw', 'status' => 'Active'],
            ['name' => 'Notify User on Withdrawal via Admin', 'alias' => 'notify-user-on-withdrawal-via-admin', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Withdraw', 'status' => 'Active'],
            ['name' => 'Notify User on Withdrawal via Admin', 'alias' => 'notify-user-on-withdrawal-via-admin', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Withdraw', 'status' => 'Active'],
            ['name' => 'Notify User on Withdrawal via Admin', 'alias' => 'notify-user-on-withdrawal-via-admin', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Withdraw', 'status' => 'Active'],
            ['name' => 'Notify User on Withdrawal via Admin', 'alias' => 'notify-user-on-withdrawal-via-admin', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Withdraw', 'status' => 'Active'],
            ['name' => 'Notify User on Withdrawal via Admin', 'alias' => 'notify-user-on-withdrawal-via-admin', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Withdraw', 'status' => 'Active'],

            # Merchant Payment [en, ar, fr, pt, ru, es, tr, ch]
            // Notify Merchant on payment
            [
                'name' => 'Notify Merchant',
                'alias' => 'notify-merchant',
                'subject' => 'Merchant Payment Notification',
                'body' => 'Hi {merchant},
                    <br><br>Payment has been completed. Amount {amount} has been paid to {merchant}.
                    <br><br><b><u><i>Here’s a brief overview of the Payment:</i></u></b>
                    <br><br><b><u>Paid at:</u></b> {created_at}
                    <br><br><b><u>Transaction ID:</u></b> {uuid}
                    <br><br><b><u>Currency:</u></b> {code}
                    <br><br><b><u>Paid By:</u></b> {user}
                    <br><br><b><u>Paid To:</u></b> {merchant}
                    <br><br><b><u>Amount:</u></b> {amount}
                    <br><br><b><u>Fee (deducted from {fee_bearer}):</u></b> {fee}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Merchant Payment', 
                'status' => 'Active'
            ],
            ['name' => 'Notify Merchant', 'alias' => 'notify-merchant', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Merchant', 'alias' => 'notify-merchant', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Merchant', 'alias' => 'notify-merchant', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Merchant', 'alias' => 'notify-merchant', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Merchant', 'alias' => 'notify-merchant', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Merchant', 'alias' => 'notify-merchant', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Merchant', 'alias' => 'notify-merchant', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            
            # Merchant Payment 
            // Notify Admin on Payment to Merchant
            [
                'name' => 'Notify Admin on Payment',
                'alias' => 'notify-admin-on-payment',
                'subject' => 'Merchant Payment Notification',
                'body' => 'Hi <b>{admin}</b>,
                    <br><br>Merchant payment completed. Amount <b>{amount}</b> has been paid by <b>{user}</b> to <b>{merchant}</b>.
                    <br><br><b><u><i>Here’s a brief overview of the Payment:</i></u></b>
                    <br><br><b><u>Paid at:</u></b> {created_at}
                    <br><br><b><u>Transaction ID:</u></b> {uuid}
                    <br><br><b><u>Currency:</u></b> {code}
                    <br><br><b><u>Paid By:</u></b> {user}
                    <br><br><b><u>Paid To:</u></b> {merchant}
                    <br><br><b><u>Amount:</u></b> {amount}
                    <br><br><b><u>Fee (deducted from {fee_bearer}):</u></b> {fee}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Merchant Payment', 
                'status' => 'Active'
            ],
            ['name' => 'Notify Admin on Payment', 'alias' => 'notify-admin-on-payment', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Admin on Payment', 'alias' => 'notify-admin-on-payment', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Admin on Payment', 'alias' => 'notify-admin-on-payment', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Admin on Payment', 'alias' => 'notify-admin-on-payment', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Admin on Payment', 'alias' => 'notify-admin-on-payment', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Admin on Payment', 'alias' => 'notify-admin-on-payment', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Admin on Payment', 'alias' => 'notify-admin-on-payment', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],

            # Ticket [en, ar, fr, pt, ru, es, tr, ch]
            // Notify Admin/Assigne/User on ticket creation or assing
            [
                'name' => 'New Ticket',
                'alias' => 'new-ticket',
                'subject' => 'New Ticket has been {assigned/created}',
                'body' => 'Hi {admin/assignee/user},
                    <br><br>Ticket #{ticket_code} was {assigned/created} {to/for} you.
                    <br><br><b><u><i>Here’s a brief overview of the ticket:</i></u></b>
                    <br><br>Ticket #{ticket_code} was created at {created_at}.
                    <br><br><b><u>Assignee:</u></b> {assignee}
                    <br><br><b><u>User:</u></b> {user}
                    <br><br><b><u>Ticket Subject:</u></b> {subject}
                    <br><br><b><u>Ticket Message:</u></b> {message}
                    <br><br><b><u>Ticket Status:</u></b> {status}
                    <br><br><b><u>Ticket Priority Level:</u></b> {priority}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Ticket', 
                'status' => 'Active'
            ],
            ['name' => 'New Ticket', 'alias' => 'new-ticket', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Ticket', 'status' => 'Active'],
            ['name' => 'New Ticket', 'alias' => 'new-ticket', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Ticket', 'status' => 'Active'],
            ['name' => 'New Ticket', 'alias' => 'new-ticket', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Ticket', 'status' => 'Active'],
            ['name' => 'New Ticket', 'alias' => 'new-ticket', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Ticket', 'status' => 'Active'],
            ['name' => 'New Ticket', 'alias' => 'new-ticket', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Ticket', 'status' => 'Active'],
            ['name' => 'New Ticket', 'alias' => 'new-ticket', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Ticket', 'status' => 'Active'],
            ['name' => 'New Ticket', 'alias' => 'new-ticket', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Ticket', 'status' => 'Active'],

            # Ticket
            // Notify Assigne/User on ticket reply
            [
                'name' => 'Ticket Reply',
                'alias' => 'ticket-reply',
                'subject' => 'Ticket Reply Notification',
                'body' => 'Hi {assignee/user},
                            <br><br>You have received a ticket reply.
                            <br><br><b><u><i>Here’s a brief overview of the reply:</i></u></b>
                            <br><br>This reply was initiated against ticket code #{ticket_code}.
                            <br><br><b><u>Assignee:</u></b> {assignee}
                            <br><br><b><u>Reply Message:</u></b> {message}
                            <br><br><b><u>Reply Status:</u></b> {status}
                            <br><br><b><u>Reply Priority Level:</u></b> {priority}
                            <br><br>If you have any questions, please feel free to reply to this email.
                            <br><br>Regards,
                            <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Ticket', 
                'status' => 'Active'
            ],
            ['name' => 'Ticket Reply', 'alias' => 'ticket-reply', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Ticket', 'status' => 'Active'],
            ['name' => 'Ticket Reply', 'alias' => 'ticket-reply', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Ticket', 'status' => 'Active'],
            ['name' => 'Ticket Reply', 'alias' => 'ticket-reply', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Ticket', 'status' => 'Active'],
            ['name' => 'Ticket Reply', 'alias' => 'ticket-reply', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Ticket', 'status' => 'Active'],
            ['name' => 'Ticket Reply', 'alias' => 'ticket-reply', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Ticket', 'status' => 'Active'],
            ['name' => 'Ticket Reply', 'alias' => 'ticket-reply', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Ticket', 'status' => 'Active'],
            ['name' => 'Ticket Reply', 'alias' => 'ticket-reply', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Ticket', 'status' => 'Active'],

            # Dispute [en, ar, fr, pt, ru, es, tr, ch]
            // Notify Merchant for opening a dispute
            [
                'name' => 'Open Dispute',
                'alias' => 'open-dispute',
                'subject' => 'Dispute Open Notification',
                'body' => 'Hi <b>{admin/merchant}</b>,
                    <br><br>Dispute ID <b>#{dispute_id}</b> has been opened by <b>{claimant}</b>.
                    <br><br><b><u><i>Here’s a brief overview of the dispute:</i></u></b>
                    <br><br><b><u>Created at:</u></b> {created_at}
                    <br><br><b><u>Transaction ID:</u></b> {uuid}
                    <br><br><b><u>Claimant:</u></b> {claimant}
                    <br><br><b><u>Defendant:</u></b> {defendant}
                    <br><br><b><u>Dispute Subject:</u></b> {subject}
                    <br><br><b><u>Dispute description:</u></b> {description}
                    <br><br><b><u>Dispute Status:</u></b> {status}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Dispute', 
                'status' => 'Active'
            ],
            ['name' => 'Open Dispute', 'alias' => 'open-dispute', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Dispute', 'status' => 'Active'],
            ['name' => 'Open Dispute', 'alias' => 'open-dispute', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Dispute', 'status' => 'Active'],
            ['name' => 'Open Dispute', 'alias' => 'open-dispute', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Dispute', 'status' => 'Active'],
            ['name' => 'Open Dispute', 'alias' => 'open-dispute', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Dispute', 'status' => 'Active'],
            ['name' => 'Open Dispute', 'alias' => 'open-dispute', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Dispute', 'status' => 'Active'],
            ['name' => 'Open Dispute', 'alias' => 'open-dispute', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Dispute', 'status' => 'Active'],
            ['name' => 'Open Dispute', 'alias' => 'open-dispute', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Dispute', 'status' => 'Active'],

            # Dispute
            // Notify Merchant or User for Dispute Reply
            [
                'name' => 'Dispute Reply',
                'alias' => 'dispute-reply',
                'subject' => 'New Dispute Reply',
                'body' => 'Hi <b>{admin/merchant/user}</b>,
                    <br><br>You have received a new dispute reply from <b>{user}</b>.
                    <br><br><b><u><i>Here’s a brief overview of the reply:</i></u></b>
                    <br><br><b><u>Replied at</u></b> {created_at}
                    <br><br><b><u>Dispute ID:</u></b> {dispute_id}
                    <br><br><b><u>Transaction ID:</u></b> {transaction_id}
                    <br><br><b><u>Claimant:</u></b> {claimant}
                    <br><br><b><u>Defendant</u></b> {defendant}
                    <br><br><b><u>Subject:</u></b> {subject}
                    <br><br><b><u>Replied Message:</u></b> {message}
                    <br><br><b><u>Status:</u></b> {status}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Dispute',
                'status' => 'Active',
            ],
            ['name' => 'Dispute Reply', 'alias' => 'dispute-reply', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Dispute', 'status' => 'Active'],
            ['name' => 'Dispute Reply', 'alias' => 'dispute-reply', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Dispute', 'status' => 'Active'],
            ['name' => 'Dispute Reply', 'alias' => 'dispute-reply', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Dispute', 'status' => 'Active'],
            ['name' => 'Dispute Reply', 'alias' => 'dispute-reply', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Dispute', 'status' => 'Active'],
            ['name' => 'Dispute Reply', 'alias' => 'dispute-reply', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Dispute', 'status' => 'Active'],
            ['name' => 'Dispute Reply', 'alias' => 'dispute-reply', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Dispute', 'status' => 'Active'],
            ['name' => 'Dispute Reply', 'alias' => 'dispute-reply', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Dispute', 'status' => 'Active'],
            
            # TwoFactor [en, ar, fr, pt, ru, es, tr, ch]
            // Send Two Factor OTP code to user for login
            [
                'name' => 'Two-fa Authentication',
                'alias' => 'two-fa-authentication',
                'subject' => '2fa OTP code',
                'body' => 'Hi {user},
                    <br><br>
                    Your 2-Factor Authentication code is: {code}
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'General', 
                'status' => 'Active'
            ],
            ['name' => 'Two-fa Authentication', 'alias' => 'two-fa-authentication', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Two-fa Authentication', 'alias' => 'two-fa-authentication', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Two-fa Authentication', 'alias' => 'two-fa-authentication', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Two-fa Authentication', 'alias' => 'two-fa-authentication', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Two-fa Authentication', 'alias' => 'two-fa-authentication', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Two-fa Authentication', 'alias' => 'two-fa-authentication', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Two-fa Authentication', 'alias' => 'two-fa-authentication', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],

            # Address/Identity Verification [en, ar, fr, pt, ru, es, tr, ch]
            // Notify user for address/identity verification status change
            [
                'name' => 'Address or Identity Verification',
                'alias' => 'address-or-identity-verification',
                'subject' => '{Identity/Address} Verification',
                'body' => 'Hi {user},
                    <br><br>Your {Identity/Address} verification is <b>{approved/pending/rejected}</b>.
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'General', 
                'status' => 'Active'
            ],
            ['name' => 'Address or Identity Verification', 'alias' => 'address-or-identity-verification', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Address or Identity Verification', 'alias' => 'address-or-identity-verification', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Address or Identity Verification', 'alias' => 'address-or-identity-verification', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Address or Identity Verification', 'alias' => 'address-or-identity-verification', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Address or Identity Verification', 'alias' => 'address-or-identity-verification', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Address or Identity Verification', 'alias' => 'address-or-identity-verification', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Address or Identity Verification', 'alias' => 'address-or-identity-verification', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],

            # Email Verification [en, ar, fr, pt, ru, es, tr, ch]
            // Notify a new registered user with e verifcation link
            [
                'name' => 'Email Verification',
                'alias' => 'email-verification',
                'subject' => 'Email Verification',
                'body' => 'Hi {user},
                    <br><br>
                    Your registered email id: {email}. Please click on the below link to verify your account,<br><br>
                    {verification_url}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'General', 
                'status' => 'Active'
            ],
            ['name' => 'Email Verification', 'alias' => 'email-verification', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Email Verification', 'alias' => 'email-verification', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Email Verification', 'alias' => 'email-verification', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Email Verification', 'alias' => 'email-verification', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Email Verification', 'alias' => 'email-verification', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Email Verification', 'alias' => 'email-verification', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Email Verification', 'alias' => 'email-verification', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
           
            # Email Verification [en, ar, fr, pt, ru, es, tr, ch]
            // Password Reset notification url
            [
                'name' => 'Password Reset',
                'alias' => 'password-reset',
                'subject' => 'Password Reset Notification',
                'body' => 'Hi {user},
                    <br><br>
                    Your registered email id: {email}. Please click on the below link to reset your password,<br><br>
                    {password_reset_url}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'General', 
                'status' => 'Active'
            ],
            ['name' => 'Password Reset', 'alias' => 'password-reset', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Password Reset', 'alias' => 'password-reset', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Password Reset', 'alias' => 'password-reset', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Password Reset', 'alias' => 'password-reset', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Password Reset', 'alias' => 'password-reset', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Password Reset', 'alias' => 'password-reset', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Password Reset', 'alias' => 'password-reset', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],

            # Profile Status [en, ar, fr, pt, ru, es, tr, ch]
            // Notify user on his/her profile status change
            [
                'name' => 'Profile Status Change',
                'alias' => 'profile-status-change',
                'subject' => 'Profile Status Change',
                'body' => 'Hi {user},
                    <br><br>Your profile status has been changed to <b>{status}</b> by the System Administrator
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>.',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'General', 
                'status' => 'Active'
            ],
            ['name' => 'Profile Status Change', 'alias' => 'profile-status-change', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Profile Status Change', 'alias' => 'profile-status-change', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Profile Status Change', 'alias' => 'profile-status-change', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Profile Status Change', 'alias' => 'profile-status-change', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Profile Status Change', 'alias' => 'profile-status-change', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Profile Status Change', 'alias' => 'profile-status-change', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Profile Status Change', 'alias' => 'profile-status-change', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],


            # Transaction Status [en, ar, fr, pt, ru, es, tr, ch]
            // Notify user on transaction status change 
            [
                'name' => 'Transaction Status Update',
                'alias' => 'transaction-status-update',
                'subject' => 'Transaction Status Update Notification',
                'body' => 'Hi {user},
                    <br><br>We would like to inform you that the transaction for <b>{transaction_type}</b> with the ID #<b>{uuid}</b> has been updated to <b>{status}</b> by the system administrator.
                    <br><br>Amount <b>{amount}</b> has been <b>{added/subtracted}</b> {from/to} your account.
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>.',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'General',
                'status' => 'Active',
            ],
            ['name' => 'Transaction Status Update', 'alias' => 'transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Transaction Status Update', 'alias' => 'transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Transaction Status Update', 'alias' => 'transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Transaction Status Update', 'alias' => 'transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Transaction Status Update', 'alias' => 'transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Transaction Status Update', 'alias' => 'transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Transaction Status Update', 'alias' => 'transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'General', 'status' => 'Active'],

            # Address/Identity Verification [en, ar, fr, pt, ru, es, tr, ch]
            // Notify user for address/identity verification status change
            [
                'name' => 'Address or Identity Verification',
                'alias' => 'address-or-identity-verification',
                'subject' => '{Identity/Address} Verification',
                'body' => 'Hi {user}, Your {Identity/Address} verification is <b>{approved/pending/rejected}</b>.Regards, {soft_name}.',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'sms',
                'group' => 'General', 
                'status' => 'Active'
            ],
            ['name' => 'Address or Identity Verification', 'alias' => 'address-or-identity-verification', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'sms', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Address or Identity Verification', 'alias' => 'address-or-identity-verification', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'sms', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Address or Identity Verification', 'alias' => 'address-or-identity-verification', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'sms', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Address or Identity Verification', 'alias' => 'address-or-identity-verification', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'sms', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Address or Identity Verification', 'alias' => 'address-or-identity-verification', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'sms', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Address or Identity Verification', 'alias' => 'address-or-identity-verification', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'sms', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Address or Identity Verification', 'alias' => 'address-or-identity-verification', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'sms', 'group' => 'General', 'status' => 'Active'],

            # Send Money  [en, ar, fr, pt, ru, es, tr, ch]
            // Notify money receiver on Send Money
            [
                'name' => 'Notify Money Receiver',
                'alias' => 'notify-money-receiver',
                'subject' => 'Money Received Notification',
                'body' => 'Hi {receiver_id}, You have received amount {amount} tnxId #{uuid} at {created_at} on your account. Regards, {soft_name}.',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'sms',
                'group' => 'Send Money',
                'status' => 'Active',
            ],
            ['name' => 'Notify Money Receiver', 'alias' => 'notify-money-receiver', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'sms', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Money Receiver', 'alias' => 'notify-money-receiver', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'sms', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Money Receiver', 'alias' => 'notify-money-receiver', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'sms', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Money Receiver', 'alias' => 'notify-money-receiver', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'sms', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Money Receiver', 'alias' => 'notify-money-receiver', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'sms', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Money Receiver', 'alias' => 'notify-money-receiver', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'sms', 'group' => 'Send Money', 'status' => 'Active'],
            ['name' => 'Notify Money Receiver', 'alias' => 'notify-money-receiver', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'sms', 'group' => 'Send Money', 'status' => 'Active'],

            # Request Money [en, ar, fr, pt, ru, es, tr, ch]
            // Notify request receiver
            [
                'name' => 'Notify Request Receiver',
                'alias' => 'notify-request-receiver',
                'subject' => 'Request Money Notification',
                'body' => 'Hi {request_receiver}, {request_sender} has been requested amount {amount} with tnxId: #{uuid} at {created_at} from you. Regards, {soft_name}.',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'sms',
                'group' => 'Request Money',
                'status' => 'Active',
            ],
            ['name' => 'Notify Request Receiver', 'alias' => 'notify-request-receiver', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'sms', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Receiver', 'alias' => 'notify-request-receiver', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'sms', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Receiver', 'alias' => 'notify-request-receiver', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'sms', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Receiver', 'alias' => 'notify-request-receiver', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'sms', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Receiver', 'alias' => 'notify-request-receiver', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'sms', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Receiver', 'alias' => 'notify-request-receiver', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'sms', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Receiver', 'alias' => 'notify-request-receiver', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'sms', 'group' => 'Request Money', 'status' => 'Active'],

            # Request Money
            // Notify request sender on request received
            [
                'name' => 'Notify Request Sender on Money Received',
                'alias' => 'notify-request-sender-on-money-received',
                'subject' => 'Requested Money Received Notification',
                'body' => 'Dear {request_sender}, You have received amount {amount} tnxId #{uuid} from {request_receiver}. Regards, {soft_name}.',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'sms',
                'group' => 'Request Money',
                'status' => 'Active',
            ],
            ['name' => 'Notify Request Sender on Money Received', 'alias' => 'notify-request-sender-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'sms', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Sender on Money Received', 'alias' => 'notify-request-sender-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'sms', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Sender on Money Received', 'alias' => 'notify-request-sender-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'sms', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Sender on Money Received', 'alias' => 'notify-request-sender-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'sms', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Sender on Money Received', 'alias' => 'notify-request-sender-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'sms', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Sender on Money Received', 'alias' => 'notify-request-sender-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'sms', 'group' => 'Request Money', 'status' => 'Active'],
            ['name' => 'Notify Request Sender on Money Received', 'alias' => 'notify-request-sender-on-money-received', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'sms', 'group' => 'Request Money', 'status' => 'Active'],

            # Transaction Status [en, ar, fr, pt, ru, es, tr, ch]
            // Notify user on transaction status change 
            [
                'name' => 'Transaction Status Update',
                'alias' => 'transaction-status-update',
                'subject' => 'Transaction Status Update Notification',
                'body' => 'Hi {user}, The transaction {transaction_type} with ID #{uuid} has been updated to {status} by the administrator. Amount {amount} has been {added/subtracted} {from/to} your account. Regards, {soft_name}.',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'sms',
                'group' => 'General',
                'status' => 'Active',
            ],
            ['name' => 'Transaction Status Update', 'alias' => 'transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'sms', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Transaction Status Update', 'alias' => 'transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'sms', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Transaction Status Update', 'alias' => 'transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'sms', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Transaction Status Update', 'alias' => 'transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'sms', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Transaction Status Update', 'alias' => 'transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'sms', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Transaction Status Update', 'alias' => 'transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'sms', 'group' => 'General', 'status' => 'Active'],
            ['name' => 'Transaction Status Update', 'alias' => 'transaction-status-update', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'sms', 'group' => 'General', 'status' => 'Active'],
            // notify admin on new merchant creation
            [
                'name' => 'Notify Admin on Merchant Creation',
                'alias' => 'notify-admin-on-merchant-creation',
                'subject' => 'New merchant Creation Notification',
                'body' => 'Hi <b>{admin}</b>,
                    <br><br>A new merchant has been created by <b>{user}</b></br>
                    <br><br><b><u><i>Here’s a brief overview of the merchant:</i></u></b>
                    <br><br><b><u>Created at:</u></b> {created_at}
                    <br><br><b><u>Merchant:</u></b> {business_name}
                    <br><br><b><u>Site URL:</u></b> {site_url}
                    <br><br><b><u>Currency:</u></b> {code}
                    <br><br><b><u>Type:</u></b> {merchant_type}
                    <br><br><b><u>Message:</u></b> {message}
                    <br><br>If you have any questions, please feel free to reply to this email.
                    <br><br>Regards,
                    <br><b>{soft_name}</b>',
                'language_id' => 1,
                'lang' => 'en',
                'type' => 'email',
                'group' => 'Merchant Payment',
                'status' => 'Active',
            ],
            ['name' => 'Notify Admin on Merchant Creation', 'alias' => 'notify-admin-on-merchant-creation', 'subject' => '', 'body' => '', 'language_id' => 2, 'lang' => 'ar', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Admin on Merchant Creation', 'alias' => 'notify-admin-on-merchant-creation', 'subject' => '', 'body' => '', 'language_id' => 3, 'lang' => 'fr', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Admin on Merchant Creation', 'alias' => 'notify-admin-on-merchant-creation', 'subject' => '', 'body' => '', 'language_id' => 4, 'lang' => 'pt', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Admin on Merchant Creation', 'alias' => 'notify-admin-on-merchant-creation', 'subject' => '', 'body' => '', 'language_id' => 5, 'lang' => 'ru', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Admin on Merchant Creation', 'alias' => 'notify-admin-on-merchant-creation', 'subject' => '', 'body' => '', 'language_id' => 6, 'lang' => 'es', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Admin on Merchant Creation', 'alias' => 'notify-admin-on-merchant-creation', 'subject' => '', 'body' => '', 'language_id' => 7, 'lang' => 'tr', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
            ['name' => 'Notify Admin on Merchant Creation', 'alias' => 'notify-admin-on-merchant-creation', 'subject' => '', 'body' => '', 'language_id' => 8, 'lang' => 'ch', 'type' => 'email', 'group' => 'Merchant Payment', 'status' => 'Active'],
        ]);

    }
}
