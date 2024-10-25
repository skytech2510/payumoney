@php
    $companyName = settings('name');
@endphp

@extends('frontend.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('public/frontend/templates/css/prism.min.css') }}">
@endsection

@section('content')
    <!-- Hero section -->
    <div class="standards-hero-section">
        <div class="px-240">
            <div class="d-flex flex-column align-items-start">
                <nav class="customize-bcrm">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Developer') }}</li>
                    </ol>
                </nav>
                <div class="btn-section">
                    <button class="btn btn-dark btn-lg">{{ __('Developer') }}</button>
                </div>
                <div class="merchant-text">
                    <p>{{ __('With :x Standard and Express, you can easily and safely receive online payments from your customer.', ['x' => $companyName]) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Merchant tab -->
    @include('frontend.pages.merchant_tab')


    <!--Paymoney code-snippet-section-->
    <div class="px-240 code-snippet-section">
        <div class="snippet-module">
            <div class="snippet-text">
                <div class="standard-title-text mb-28">
                    <h3>{{ $companyName }} {{ __('Express Payment Gateway Documentation.') }}</h3>
                </div>
            <span>{{ __('Payer') }}</span>
                <p>{{ __('If payer wants to fund payments using :x, set payer to :x.(Other payment method ex: paypal, stripe, coin payments etc not available yet).', ['x' => $companyName]) }}</p>
            </div>
            <div class="language-container">
                <div class="snippet">
                    <pre class="language-php thin-scrollbar">
                        <code>
                            //Payer Object 
                            $payer = new Payer(); 
                            $payer->setPaymentMethod('PayMoney'); //preferably, your system name, example - PayMoney
                        </code>
                    </pre>
                </div>
            </div>
        </div>
        <div class="snippet-module">
            <div class="snippet-text">
                <span>{{ __('Amount') }}</span>
                <p>{{ __('Specify a payment amount and the currency.') }}</p>
            </div>
            <div class="language-container">
                <div class="snippet line-numbers">
                    <pre class="language-php thin-scrollbar">
                        <code>
                            //Amount Object 
                            $amountIns = new Amount(); 
                            $amountIns->setTotal(20)->setCurrency('USD'); //must give a valid currency code and must exist in merchant wallet list 
                        </code>
                    </pre>
                </div>
            </div>
        </div>
		<div class="snippet-module">
            <div class="snippet-text">
                <span>{{ __("Transaction") }}</span>
                <p>{{ __("It’s a Transaction resource where amount object has to set.") }}</p>
            </div>
            <div class="language-container">
                <div class="snippet line-numbers">
                    <pre class="language-php thin-scrollbar">
                        <code>
                            //Transaction Object
                            $trans = new Transaction();
                            $trans->setAmount($amountIns);
                        </code>
                    </pre>
                </div>
            </div>
        </div>
		<div class="snippet-module">
            <div class="snippet-text">
            <span>{{ __('RedirectUrls') }}</span>
            <p>{{ __('Set the urls where buyer should redirect after transaction is completed or cancelled.') }}</p>
            </div>
            <div class="language-container">
                <div class="snippet line-numbers">
                    <pre class="language-php thin-scrollbar">
                        <code>
                            //RedirectUrls Object
                            $urls = new RedirectUrls();
                            $urls->setSuccessUrl('http://your-merchant-domain.com/example-success.php') //success url - the merchant domain page, to redirect after successful payment, see sample example-success.php file in  sdk root, example - http://techvill.net/PayMoney_sdk/example-success.php
                            ->setCancelUrl('http://your-merchant-domain.com/');//cancel url - the merchant domain page, to redirect after cancellation of payment, example - http://techvill.net/PayMoney_sdk/
                        </code>
                    </pre>
                </div>
            </div>
        </div>
        <div class="snippet-module mb-0">
            <div class="snippet-text">
                <span>{{ __("Payment") }}</span>
                <p>{{ __("It’s a payment resource where all Payer, Amount, RedirectUrls and Credentials of merchant (Client ID and Client Secret) have to set. After initialized into payment object, need to call create method. It will generate a redirect URL. Users have to redirect into this URL to complete the transaction.") }}</p>
            </div>
            <div class="language-container">
                <div class="snippet line-numbers">
                    <pre class="language-php thin-scrollbar">
                        <code>
                            //Payment Object
                            $payment = new Payment();
                            $payment->setCredentials([ //client id & client secret, see merchants->setting(gear icon)
                            'client_id' => 'place your client id here',  //must provide correct client id of an express merchant
                            'client_secret' => 'place your client secret here' //must provide correct client secret of an express merchant
                            ])->setRedirectUrls($urls)
                            ->setPayer($payer) 
                            ->setTransaction($trans);
                            
                            try {
                            $payment->create(); //create payment
                            header("Location: ".$payment->getApprovedUrl()); //checkout url
                            } catch (Exception $ex) { 
                            print $ex; 
                            exit; }
                        </code>
                    </pre>
                </div>
            </div>
        </div>
		<div class="snippet-module mb-0">
            <div class="snippet-text run-code">
                <div class="standard-title-text m-width mb-28">
                    <h3>{{ __('A few steps on how to run this code on your device') }}:</h3>
                </div>
                <span>{{ __('1st') }} :</span>
                <p>{{ __('Click download for the package') }} </p>
                <div class="download-btn mt-12">
                    <a href="{{ url('download/package') }}" class="btn btn-sm btn-primary">{{ __('Download') }}</a>
                </div>
            </div>
        </div>
		<div class="snippet-module">
            <div class="snippet-text run-code mt-1">
            <span>{{ __('2nd') }} :</span>
            <p class="download-desc">{{ __('Now, go to') }} php-sdk/src/PayMoney/Rest/Connection.php, {{ __('then change') }} BASE_URL {{ __("value to your domain name(i.e: If the domain is - 'your-domain.com' then,") }} define( 'BASE_URL' , 'http://your-domain.com/' ) )</p>
            </div>
            <div class="language-container">
                <div class="snippet">
                    <pre class="language-php thin-scrollbar pt-76">
                        <div class="example">
                            <span class="left-example-text">{{ __('Example code') }}</span>
                        </div>
                        <code>
                            require 'vendor/autoload.php';
                            //if you want to change the namespace/path from 'PayMoney' - lines[1-5] - 
                            //to your desired name, i.e. (use PayMoney\Api\Amount; 
                            //to use MyDomain\Api\Amount;), then you must change the folders name that holds the API classes 
                            //as well as change the property 'PayMoney' in (autoload->psr-0) of (php-sdk/composer.json) file to your 
                            //desired name and run "composer dump-autoload" command from sdk root
                            use PayMoney\Api\Payer; 
                            use PayMoney\Api\Amount; 
                            use PayMoney\Api\Transaction; 
                            use PayMoney\Api\RedirectUrls; 
                            use PayMoney\Api\Payment;
                            //Payer Object 
                            $payer = new Payer(); 
                            $payer->setPaymentMethod('PayMoney'); //preferably, your system name, example - PayMoney
                            //Amount Object 
                            $amountIns = new Amount(); 
                            $amountIns->setTotal(20)->setCurrency('USD'); //must give a valid currency code and must exist in merchant wallet list 
                            //Transaction Object
                            $trans = new Transaction();
                            $trans->setAmount($amountIns);
                        </code>
                    </pre>
                </div>
            </div>
        </div>
		<div class="snippet-module">
            <div class="snippet-text optional">
                <div class="standard-title-text">
                    <h3 class="mt-0">{{ __('Optional Instructions') }}</h3>
                </div>
                <p>{{ __('If you don\'t see changes after configuring and extracting SDK, go to your SDK root and run the commands below') }}:-</p>
            </div>
            <div class="option-container">
                <ul>
                    <li>{{ __('Composer clear-cache') }}</li>
                    <li>{{ __('Composer install') }}</li>
                    <li>{{ __('Composer dump-autoload') }}</li>
                </ul>
            </div>
        </div>    
   </div> 
@endsection

@section('js')
    <script src="{{ asset('public/frontend/templates/js/prism.min.js') }}"></script>
@endsection
