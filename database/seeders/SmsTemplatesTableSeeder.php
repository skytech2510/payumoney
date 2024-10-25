<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class SmsTemplatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EmailTemplate::insert(
        [

            // User Verification
                // [
                //     //User Verification - en
                //     'temp_id'     => '17',
                //     'subject'     => 'Notice for User Verification!',
                //     'body'        => 'Hi {user},
                //                         <br><br>Please click on the below link to verify your account,
                //                         <br><br>{verification_url}',
                //     'lang'        => 'en',
                //     'type'        => 'sms',
                //     'language_id' => 1,
                // ],

                // [
                //     //User Verification - ar
                //     'temp_id'     => '17',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'ar',
                //     'type'        => 'sms',
                //     'language_id' => 2,
                // ],

                // [
                //     //User Verification - fr
                //     'temp_id'     => '17',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'fr',
                //     'type'        => 'sms',
                //     'language_id' => 3,
                // ],

                // [
                //     //User Verification - pt
                //     'temp_id'     => '17',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'pt',
                //     'type'        => 'sms',
                //     'language_id' => 4,
                // ],

                // [
                //     //User Verification - ru
                //     'temp_id'     => '17',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'ru',
                //     'type'        => 'sms',
                //     'language_id' => 5,
                // ],

                // [
                //     //User Verification - es
                //     'temp_id'     => '17',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'es',
                //     'type'        => 'sms',
                //     'language_id' => 6,
                // ],

                // [
                //     //User Verification - tr
                //     'temp_id'     => '17',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'tr',
                //     'type'        => 'sms',
                //     'language_id' => 7,
                // ],

                // [
                //     //User Verification - ch
                //     'temp_id'     => '17',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'ch',
                //     'type'        => 'sms',
                //     'language_id' => 8,
                // ],

            // Password Reset
                // [
                //     //Password Reset - en
                //     'temp_id'     => '18',
                //     'subject'     => 'Notice for Password Reset!',
                //     'body'        => 'Hi {user},
                //                         <br><br>Please click on the below link to reset your password,
                //                         <br><br>{password_reset_url}',
                //     'lang'        => 'en',
                //     'type'        => 'sms',
                //     'language_id' => 1,
                // ],

                // [
                //     //Password Reset - ar
                //     'temp_id'     => '18',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'ar',
                //     'type'        => 'sms',
                //     'language_id' => 2,
                // ],

                // [
                //     //Password Reset - fr
                //     'temp_id'     => '18',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'fr',
                //     'type'        => 'sms',
                //     'language_id' => 3,
                // ],

                // [
                //     //Password Reset - pt
                //     'temp_id'     => '18',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'pt',
                //     'type'        => 'sms',
                //     'language_id' => 4,
                // ],

                // [
                //     //Password Reset - ru
                //     'temp_id'     => '18',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'ru',
                //     'type'        => 'sms',
                //     'language_id' => 5,
                // ],

                // [
                //     //Password Reset - es
                //     'temp_id'     => '18',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'es',
                //     'type'        => 'sms',
                //     'language_id' => 6,
                // ],

                // [
                //     //Password Reset - tr
                //     'temp_id'     => '18',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'tr',
                //     'type'        => 'sms',
                //     'language_id' => 7,
                // ],

                // [
                //     //Password Reset - ch
                //     'temp_id'     => '18',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'ch',
                //     'type'        => 'sms',
                //     'language_id' => 8,
                // ],

            //Transferred
                [
                    //Transferred - en
                    'temp_id'     => '1',
                    'subject'     => 'Notice of Transfer!',
                    'body'        => 'Hi {sender_id},
                    <br><br>You have transferred {amount} from your account.
                    ',
                    'lang'        => 'en',
                    'type'        => 'sms',
                    'language_id' => 1,
                ],
                [
                    //Transferred - ar
                    'temp_id'     => '1',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ar',
                    'type'        => 'sms',
                    'language_id' => 2,
                ],
                [
                    //Transferred - fr
                    'temp_id'     => '1',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'fr',
                    'type'        => 'sms',
                    'language_id' => 3,
                ],
                [
                    //Transferred - pt
                    'temp_id'     => '1',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'pt',
                    'type'        => 'sms',
                    'language_id' => 4,
                ],
                [
                    //Transferred - ru
                    'temp_id'     => '1',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ru',
                    'type'        => 'sms',
                    'language_id' => 5,
                ],
                [
                    //Transferred - es
                    'temp_id'     => '1',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'es',
                    'type'        => 'sms',
                    'language_id' => 6,
                ],
                [
                    //Transferred - tr
                    'temp_id'     => '1',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'tr',
                    'type'        => 'sms',
                    'language_id' => 7,
                ],
                [
                    //Transferred - ch
                    'temp_id'     => '1',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ch',
                    'type'        => 'sms',
                    'language_id' => 8,
                ],

            //Received
                [
                    //Received - en
                    'temp_id'     => '2',
                    'subject'     => 'Notice to Receive!',
                    'body'        => 'Hi {receiver_id},
                                <br><br>You have received {amount} from {sender_id}.
                                ',
                    'lang'        => 'en',
                    'type'        => 'sms',
                    'language_id' => 1,
                ],
                [
                    //Received - ar
                    'temp_id'     => '2',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ar',
                    'type'        => 'sms',
                    'language_id' => 2,
                ],
                [
                    //Received - fr
                    'temp_id'     => '2',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'fr',
                    'type'        => 'sms',
                    'language_id' => 3,
                ],
                [
                    //Received - pt
                    'temp_id'     => '2',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'pt',
                    'type'        => 'sms',
                    'language_id' => 4,
                ],
                [
                    //Received - ru
                    'temp_id'     => '2',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ru',
                    'type'        => 'sms',
                    'language_id' => 5,
                ],
                [
                    //Received - es
                    'temp_id'     => '2',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'es',
                    'type'        => 'sms',
                    'language_id' => 6,
                ],
                [
                    //Received - tr
                    'temp_id'     => '2',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'tr',
                    'type'        => 'sms',
                    'language_id' => 7,
                ],
                [
                    //Received - ch
                    'temp_id'     => '2',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ch',
                    'type'        => 'sms',
                    'language_id' => 8,
                ],

            //Request Payment Creation
                [
                    //Request Payment Creation - en
                    'temp_id'     => '4',
                    'subject'     => 'Notice of Request Creation!',
                    'body'        => 'Hi {acceptor},
                                <br><br>Amount {amount} has been requested by {creator} to your account.
                                ',
                    'lang'        => 'en',
                    'type'        => 'sms',
                    'language_id' => 1,
                ],
                [
                    //Request Payment Creation - ar
                    'temp_id'     => '4',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ar',
                    'type'        => 'sms',
                    'language_id' => 2,
                ],
                [
                    //Request Payment Creation - fr
                    'temp_id'     => '4',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'fr',
                    'type'        => 'sms',
                    'language_id' => 3,
                ],
                [
                    //Request Payment Creation - pt
                    'temp_id'     => '4',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'pt',
                    'type'        => 'sms',
                    'language_id' => 4,
                ],
                [
                    //Request Payment Creation - ru
                    'temp_id'     => '4',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ru',
                    'type'        => 'sms',
                    'language_id' => 5,
                ],
                [
                    //Request Payment Creation - es
                    'temp_id'     => '4',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'es',
                    'type'        => 'sms',
                    'language_id' => 6,
                ],
                [
                    //Request Payment Creation - tr
                    'temp_id'     => '4',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'tr',
                    'type'        => 'sms',
                    'language_id' => 7,
                ],
                [
                    //Request Payment Creation - ch
                    'temp_id'     => '4',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ch',
                    'type'        => 'sms',
                    'language_id' => 8,
                ],

            //Request Payment Acceptance
                [
                    //Request Payment Acceptance - ne
                    'temp_id'     => '5',
                    'subject'     => 'Notice of Request Acceptance!',
                    'body'        => 'Hi {creator},
                                    <br><br>Your request of #{uuid} of amount {amount} has been accepted by {acceptor}.
                                ',
                    'lang'        => 'en',
                    'type'        => 'sms',
                    'language_id' => 1,
                ],
                [
                    //Request Payment Acceptance - ar
                    'temp_id'     => '5',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ar',
                    'type'        => 'sms',
                    'language_id' => 2,
                ],
                [
                    //Request Payment Acceptance - fr
                    'temp_id'     => '5',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'fr',
                    'type'        => 'sms',
                    'language_id' => 3,
                ],
                [
                    //Request Payment Acceptance - pt
                    'temp_id'     => '5',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'pt',
                    'type'        => 'sms',
                    'language_id' => 4,
                ],
                [
                    //Request Payment Acceptance - ru
                    'temp_id'     => '5',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ru',
                    'type'        => 'sms',
                    'language_id' => 5,
                ],
                [
                    //Request Payment Acceptance - es
                    'temp_id'     => '5',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'es',
                    'type'        => 'sms',
                    'language_id' => 6,
                ],
                [
                    //Request Payment Acceptance - tr
                    'temp_id'     => '5',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'tr',
                    'type'        => 'sms',
                    'language_id' => 7,
                ],
                [
                    //Request Payment Acceptance - ch
                    'temp_id'     => '5',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ch',
                    'type'        => 'sms',
                    'language_id' => 8,
                ],

            // ---------------------------------------------------------

            //Status Change - Transfer
                [
                    //Status Change - Transfer - en
                    'temp_id'     => '6',
                    'subject'     => 'Status of Transaction #{uuid} has been updated!',
                    'body'        => 'Hi {sender_id/receiver_id},
                                    <br><br><b>
                                    Transfer #{uuid} has been updated to {status} by system administrator!</b>
                                    <br><br>
                                    {amount} is {added/subtracted} {from/to} your account.
                                ',
                    'lang'        => 'en',
                    'type'        => 'sms',
                    'language_id' => 1,
                ],
                [
                    //Status Change - Transfer - ar
                    'temp_id'     => '6',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ar',
                    'type'        => 'sms',
                    'language_id' => 2,
                ],
                [
                    //Status Change - Transfer - fr
                    'temp_id'     => '6',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'fr',
                    'type'        => 'sms',
                    'language_id' => 3,
                ],
                [
                    //Status Change - Transfer - pt
                    'temp_id'     => '6',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'pt',
                    'type'        => 'sms',
                    'language_id' => 4,
                ],
                [
                    //Status Change - Transfer - ru
                    'temp_id'     => '6',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ru',
                    'type'        => 'sms',
                    'language_id' => 5,
                ],
                [
                    //Status Change - Transfer - es
                    'temp_id'     => '6',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'es',
                    'type'        => 'sms',
                    'language_id' => 6,
                ],
                [
                    //Status Change - Transfer - tr
                    'temp_id'     => '6',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'tr',
                    'type'        => 'sms',
                    'language_id' => 7,
                ],
                [
                    //Status Change - Transfer - ch
                    'temp_id'     => '6',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ch',
                    'type'        => 'sms',
                    'language_id' => 8,
                ],

            //Status Change - Request Payment
                [
                    //Status Change - Request Payment - en
                    'temp_id'     => '8',
                    'subject'     => 'Status of Transaction #{uuid} has been updated!',
                    'body'        => 'Hi {user_id/receiver_id},
                                <br><br><b>
                                Transaction of Request Payment #{uuid} has been updated to {status} by system administrator!</b>
                                <br><br>
                                {amount} is {added/subtracted} {from/to} your account.
                                ',
                    'lang'        => 'en',
                    'type'        => 'sms',
                    'language_id' => 1,
                ],

                [
                    //Status Change - Request Payment - ar
                    'temp_id'     => '8',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ar',
                    'type'        => 'sms',
                    'language_id' => 2,
                ],

                [
                    //Status Change - Request Payment - fr
                    'temp_id'     => '8',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'fr',
                    'type'        => 'sms',
                    'language_id' => 3,
                ],

                [
                    //Status Change - Request Payment - pt
                    'temp_id'     => '8',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'pt',
                    'type'        => 'sms',
                    'language_id' => 4,
                ],

                [
                    //Status Change - Request Payment - ru
                    'temp_id'     => '8',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ru',
                    'type'        => 'sms',
                    'language_id' => 5,
                ],

                [
                    //Status Change - Request Payment - es
                    'temp_id'     => '8',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'es',
                    'type'        => 'sms',
                    'language_id' => 6,
                ],

                [
                    //Status Change - Request Payment - tr
                    'temp_id'     => '8',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'tr',
                    'type'        => 'sms',
                    'language_id' => 7,
                ],
                [
                    //Status Change - Request Payment - ch
                    'temp_id'     => '8',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ch',
                    'type'        => 'sms',
                    'language_id' => 8,
                ],

            //Status Change - Payout
                [
                    //Status Change - Payout - en
                    'temp_id'     => '10',
                    'subject'     => 'Status of Transaction #{uuid} has been updated!',
                    'body'        => 'Hi {user_id},
                                <br><br><b>
                                Transaction of Payout #{uuid} has been updated to {status} by system administrator!</b>
                                <br><br>
                                {amount} is {added/subtracted} {from/to} your account.
                                ',
                    'lang'        => 'en',
                    'type'        => 'sms',
                    'language_id' => 1,
                ],
                [
                    //Status Change - Payout - ar
                    'temp_id'     => '10',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ar',
                    'type'        => 'sms',
                    'language_id' => 2,
                ],
                [
                    //Status Change - Payout - fr
                    'temp_id'     => '10',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'fr',
                    'type'        => 'sms',
                    'language_id' => 3,
                ],
                [
                    //Status Change - Payout - pt
                    'temp_id'     => '10',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'pt',
                    'type'        => 'sms',
                    'language_id' => 4,
                ],
                [
                    //Status Change - Payout - ru
                    'temp_id'     => '10',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ru',
                    'type'        => 'sms',
                    'language_id' => 5,
                ],
                [
                    //Status Change - Payout - es
                    'temp_id'     => '10',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'es',
                    'type'        => 'sms',
                    'language_id' => 6,
                ],
                [
                    //Status Change - Payout - tr
                    'temp_id'     => '10',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'tr',
                    'type'        => 'sms',
                    'language_id' => 7,
                ],
                [
                    //Status Change - Payout - ch
                    'temp_id'     => '10',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ch',
                    'type'        => 'sms',
                    'language_id' => 8,
                ],

            //Status Change - Merchant Payment
                [
                    //Status Change - Merchant Payment - en
                    'temp_id'     => '14',
                    'subject'     => 'Status of Transaction #{uuid} has been updated!',
                    'body'        => 'Hi {paidByUser/merchantUser},
                                <br><br><b>
                                Transaction of Merchant Payment #{uuid} has been updated to {status} by system administrator!</b>
                                <br><br>
                                {amount} is {added/subtracted} {from/to} your account.
                                ',
                    'lang'        => 'en',
                    'type'        => 'sms',
                    'language_id' => 1,
                ],

                [
                    //Status Change - Merchant Payment - ar
                    'temp_id'     => '14',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ar',
                    'type'        => 'sms',
                    'language_id' => 2,
                ],

                [
                    //Status Change - Merchant Payment - fr
                    'temp_id'     => '14',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'fr',
                    'type'        => 'sms',
                    'language_id' => 3,
                ],

                [
                    //Status Change - Merchant Payment - pt
                    'temp_id'     => '14',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'pt',
                    'type'        => 'sms',
                    'language_id' => 4,
                ],

                [
                    //Status Change - Merchant Payment - ru
                    'temp_id'     => '14',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ru',
                    'type'        => 'sms',
                    'language_id' => 5,
                ],

                [
                    //Status Change - Merchant Payment - es
                    'temp_id'     => '14',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'es',
                    'type'        => 'sms',
                    'language_id' => 6,
                ],

                [
                    //Status Change - Merchant Payment - tr
                    'temp_id'     => '14',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'tr',
                    'type'        => 'sms',
                    'language_id' => 7,
                ],

                [
                    //Status Change - Merchant Payment - ch
                    'temp_id'     => '14',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ch',
                    'type'        => 'sms',
                    'language_id' => 8,
                ],

            // Merchant Payment
                // [
                //     //Merchant Payment - en
                //     'subject'     => 'Notice of Merchant Payment!',
                //     'body'        => 'Hi {paidByUser/merchantUser},

                //                 <br><br><b>
                //                 The funds amount equal to {symbol}{amount} has been {sent/received} {from/to} your account</b>

                //                 <br><br><b><u><i>Here’s a brief overview of the payment:</i></u></b>

                //                 <br><br>Payment # {uuid} was created at {created_at}.

                //                 <br><br><b><u>{Merchant Id/Paid By}:</u></b> {merchant_id/user_id}

                //                 <br><br><b><u>Currency:</u></b> {currency_id}

                //                 <br><br><b><u>Payment Method:</u></b> {receiver_id}

                //                 <br><br><b><u>Fees:</u></b> {symbol}{fees}

                //                 <br><br><b><u>Amount:</u></b> {symbol}{amount}

                //                 <br><br>If you have any questions, please feel free to reply to this email.

                //                 <br><br>Regards,
                //                 <br><b>{soft_name}</b>',

                //     'lang'        => 'en',
                //     'type'        => 'sms',
                //     'language_id' => 1,
                // ],

                // [
                //     //Merchant Payment - ar
                //     'temp_id'     => '15',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'ar',
                //     'type'        => 'sms',
                //     'language_id' => 2,
                // ],

                // [
                //     //Merchant Payment - fr
                //     'temp_id'     => '15',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'fr',
                //     'type'        => 'sms',
                //     'language_id' => 3,
                // ],

                // [
                //     //Merchant Payment - pt
                //     'temp_id'     => '15',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'pt',
                //     'type'        => 'sms',
                //     'language_id' => 4,
                // ],

                // [
                //     //Merchant Payment - ru
                //     'temp_id'     => '15',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'ru',
                //     'type'        => 'sms',
                //     'language_id' => 5,
                // ],

                // [
                //     //Merchant Payment - es
                //     'temp_id'     => '15',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'es',
                //     'type'        => 'sms',
                //     'language_id' => 6,
                // ],

                // [
                //     //Merchant Payment - tr
                //     'temp_id'     => '15',
                //     'subject'     => '',
                //     'body'        => '',
                //     'lang'        => 'tr',
                //     'type'        => 'sms',
                //     'language_id' => 7,
                // ],

            // Status Change - Request Payment (for Pending/Cancel)
                [
                    //Request Payment - en
                    'temp_id'     => '16',
                    'subject'     => 'Status of Transaction #{uuid} has been updated!',
                    'body'        => 'Hi {user_id/receiver_id},
                                <br><br><b>
                                Transaction of Request Payment #{uuid} has been updated to {status} by system administrator!</b>
                                ',
                    'lang'        => 'en',
                    'type'        => 'sms',
                    'language_id' => 1,
                ],

                [
                    //Request Payment - ar
                    'temp_id'     => '16',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ar',
                    'type'        => 'sms',
                    'language_id' => 2,
                ],

                [
                    //Request Payment - fr
                    'temp_id'     => '16',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'fr',
                    'type'        => 'sms',
                    'language_id' => 3,
                ],

                [
                    //Request Payment - pt
                    'temp_id'     => '16',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'pt',
                    'type'        => 'sms',
                    'language_id' => 4,
                ],

                [
                    //Request Payment - ru
                    'temp_id'     => '16',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ru',
                    'type'        => 'sms',
                    'language_id' => 5,
                ],

                [
                    //Request Payment - es
                    'temp_id'     => '16',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'es',
                    'type'        => 'sms',
                    'language_id' => 6,
                ],

                [
                    //Request Payment - tr
                    'temp_id'     => '16',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'tr',
                    'type'        => 'sms',
                    'language_id' => 7,
                ],

                [
                    //Request Payment - ch
                    'temp_id'     => '16',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ch',
                    'type'        => 'sms',
                    'language_id' => 8,
                ],


            /**
             * Not needeed - told by boss on - 5/9/2018
             */
            //Ticket
                [
                    //Ticket - en
                    'temp_id'     => '11',
                    'subject'     => 'Notice of Ticket!',
                    'body'        => 'Hi {assignee/user},
                                <br><br>Ticket #{ticket_code} was {assigned/created} {to/for} you by the system administrator.
                                ',
                    'lang'        => 'en',
                    'type'        => 'sms',
                    'language_id' => 1,
                ],
                [
                    //Ticket - ar
                    'temp_id'     => '11',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ar',
                    'type'        => 'sms',
                    'language_id' => 2,
                ],
                [
                    //Ticket - fr
                    'temp_id'     => '11',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'fr',
                    'type'        => 'sms',
                    'language_id' => 3,
                ],
                [
                    //Ticket - pt
                    'temp_id'     => '11',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'pt',
                    'type'        => 'sms',
                    'language_id' => 4,
                ],
                [
                    //Ticket - ru
                    'temp_id'     => '11',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ru',
                    'type'        => 'sms',
                    'language_id' => 5,
                ],
                [
                    //Ticket - es
                    'temp_id'     => '11',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'es',
                    'type'        => 'sms',
                    'language_id' => 6,
                ],
                [
                    //Ticket - tr
                    'temp_id'     => '11',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'tr',
                    'type'        => 'sms',
                    'language_id' => 7,
                ],
                [
                    //Ticket - ch
                    'temp_id'     => '11',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ch',
                    'type'        => 'sms',
                    'language_id' => 8,
                ],

            //Ticket Reply
                [
                    //Ticket Reply - en
                    'temp_id'     => '12',
                    'subject'     => 'Notice of Ticket Reply!',
                    'body'        => 'Hi {user},
                                <br><br>The system administrator has replied to your assigned ticket # {ticket_code).
                                ',
                    'lang'        => 'en',
                    'type'        => 'sms',
                    'language_id' => 1,
                ],
                [
                    //Ticket Reply - ar
                    'temp_id'     => '12',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ar',
                    'type'        => 'sms',
                    'language_id' => 2,
                ],
                [
                    //Ticket Reply - fr
                    'temp_id'     => '12',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'fr',
                    'type'        => 'sms',
                    'language_id' => 3,
                ],
                [
                    //Ticket Reply - pt
                    'temp_id'     => '12',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'pt',
                    'type'        => 'sms',
                    'language_id' => 4,
                ],
                [
                    //Ticket Reply - ru
                    'temp_id'     => '12',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ru',
                    'type'        => 'sms',
                    'language_id' => 5,
                ],
                [
                    //Ticket Reply - es
                    'temp_id'     => '12',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'es',
                    'type'        => 'sms',
                    'language_id' => 6,
                ],
                [
                    //Ticket Reply - tr
                    'temp_id'     => '12',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'tr',
                    'type'        => 'sms',
                    'language_id' => 7,
                ],
                [
                    //Ticket Reply - ch
                    'temp_id'     => '12',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ch',
                    'type'        => 'sms',
                    'language_id' => 8,
                ],

            //Dispute Reply
                [
                    //Dispute Reply - en
                    'temp_id'     => '13',
                    'subject'     => 'Notice of Dispute Reply!',
                    'body'        => 'Hi {user},
                                <br><br>The system administrator has replied to your dispute for transaction # {transaction_id).
                                ',
                    'lang'        => 'en',
                    'type'        => 'sms',
                    'language_id' => 1,
                ],
                [
                    //Dispute Reply - ar
                    'temp_id'     => '13',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ar',
                    'type'        => 'sms',
                    'language_id' => 2,
                ],
                [
                    //Dispute Reply - fr
                    'temp_id'     => '13',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'fr',
                    'type'        => 'sms',
                    'language_id' => 3,
                ],
                [
                    //Dispute Reply - pt
                    'temp_id'     => '13',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'pt',
                    'type'        => 'sms',
                    'language_id' => 4,
                ],
                [
                    //Dispute Reply - ru
                    'temp_id'     => '13',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ru',
                    'type'        => 'sms',
                    'language_id' => 5,
                ],
                [
                    //Dispute Reply - es
                    'temp_id'     => '13',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'es',
                    'type'        => 'sms',
                    'language_id' => 6,
                ],
                [
                    //Dispute Reply - tr
                    'temp_id'     => '13',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'tr',
                    'type'        => 'sms',
                    'language_id' => 7,
                ],
                [
                    //Dispute Reply - ch
                    'temp_id'     => '13',
                    'subject'     => '',
                    'body'        => '',
                    'lang'        => 'ch',
                    'type'        => 'sms',
                    'language_id' => 8,
                ],
        ]);

    }
}
