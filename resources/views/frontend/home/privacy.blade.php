@extends('frontend.layouts.app')
@section('content')
    <!-- Hero section -->
    <div class="standards-hero-section privacy-template scrol-pt">
        <div class="px-240">
            <div class="d-flex flex-column align-items-start">
                <nav class="customize-bcrm">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Privacy Policy') }}</li>
                    </ol>
                </nav>
                <div class="btn-section">
                    <button class="btn btn-dark btn-lg">{{ __('Privacy Policy') }}</button>
                </div>
                <div class="merchant-text">
                    <p>{{ __('This Privacy Policy explains how we collect, use, disclose personal information when you use our online payment services') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="px-240 code-snippet-section">
        <div class="privacy-scrol">
            <div class="d-flex gap-60 justify-content-center">
                <div class="left-title-box">
                    <div class="privacy-left-box">
                        <nav id="navbar-privacy" class="scrolspy navbar flex-column align-items-stretch">
                            <nav class="nav flex-column">
                                <a class="privacy-nav privacy-nav-active" href="#item-1">{{ __('What dose this Privacy Policy Cover') }}</a>
                                <a class="privacy-nav" href="#item-2">{{ __('Information We Collect') }}</a>
                                <a class="privacy-nav" href="#item-3">{{ __('How We Use Your Information') }}</a>
                                <a class="privacy-nav" href="#item-4">{{ __('How We Share Your Information') }}</a>
                                <a class="privacy-nav" href="#item-5">{{ __('Data Security') }}</a>
                                <a class="privacy-nav" href="#item-6">{{ __('Updates to this Privacy Policy') }}</a>
                                <a class="privacy-nav mb-0" href="#item-7">{{ __('Contact Us') }}</a>
                            </nav>
                        </nav>
                    </div>
                </div>
                <div class="right-content-box">
                    <div class="privacy-nav-content" data-bs-target="#navbar-privacy">
                        <div class="effective-description">
                            <h4>{{ __('Effective Date: Decembar 16, 2023') }}</h4>
                            <p>{{ __('At :x, we understand the importance of privacy and are committed to protecting the personal information of our users. This Privacy Policy explains how we collect, use, disclose, and safeguard your personal information when you use our online payment services of :y.', ['x' => settings('name'), 'y' => settings('name')]) }}</p>
                        </div>
                        <div class="policy-cover" id="item-1">
                            <h2>{{ __('What dose this Privacy Policy Cover') }}</h2>
                            <p>{{ __('Please read this Privacy Policy carefully to understand our practices regarding your personal information. By accessing or using our Services, you acknowledge that you have read, understood, and agree to be bound by the terms of this Privacy Policy. If you do not agree to the terms of this Privacy Policy, please do not access or use our Services.') }}</p>
                        </div>
                        <div class="privacy-mx">
                            <div class="information">
                                <h2 id="item-2">{{ __('Information We Collect') }}</h2>
                                <h3>{{ __('Personal Information') }}</h3>
                                <p>{{ __('We may collect certain personal information from you when you use our Services. The types of personal information we collect may include, but are not limited to:') }}</p>
                                <ul>
                                    <li>{{ __('Your name') }}</li>
                                    <li>{{ __('Contact information, such as email address, phone number, and mailing address') }}</li>
                                    <li>{{ __('Payment information, including credit card details, bank account information, and billing address Payment information, including credit card details, bank account information, and billing address Payment information, including credit card details, bank account information, and billing address') }}</li>
                                    <li>{{ __('Information necessary for Know Your Customer (KYC) and anti-money laundering (AML) compliance, such as identification documents') }}</li>
                                    <li>{{ __('Transaction details, including the amount, recipient, and date of the payment') }}</li>
                                    <li>{{ __('Communication and support interactions with us') }}</li>
                                </ul>
                            </div>
                            <div class="usage-data">
                                <h3>{{ __('Usage Data') }}</h3>
                                <p>{!! __('We collect server logs that may include details such as your IP address, browser type, device information, operating system, and referring/exit pages.', ['x' => '<span>'.__('Log information:').'</span>']) !!}</p>
                                <p>{!! __(':x We collect information about your activity on our platform, such as the pages you visit, the features you use, and the actions you take.', ['x' => '<span>'.__('Usage information:').'</span>']) !!}</p>
                                <p>{!! __(':x Cookies and similar technologies: We use cookies and similar tracking technologies to enhance your user experience and analyze usage patterns. For more information about the cookies we use and your choices regarding , please refer to our Cookie Policy.', ['x' => '<span>'.__('Usage information:').'</span>']) !!}</p>
                            </div>
                            <div class="information information-2">
                                <h2 id="item-3">{{ __('How We Use Your Information') }}</h2>
                                <p>{{ __('We use the information we collect from you for various purposes, including but not limited to') }}: </p>
                                <ul>
                                    <li>{{ __('Providing and maintaining our Services') }}</li>
                                    <li>{{ __('Processing and facilitating secure transactions') }}</li>
                                    <li>{{ __('Verifying your identity and com plying with legal obligations, including KYC and AML requirements') }}</li>
                                    <li>{{ __('Responding to your inquiries, requests, or customer support needs') }}</li>
                                    <li>{{ __('Sending you important notifications, updates, and administrative messages') }}</li>
                                    <li>{{ __('Improving and personalizing your experience on our platform') }}</li>
                                    <li>{{ __('Conducting research and analysis to enhance and develop our Services') }}</li>
                                    <li>{{ __('Protecting against fraud, unauthorized transactions, and other potential risks') }}</li>
                                    <li>{{ __('Enforcing our terms and policies') }}</li>
                                </ul>
                            </div>
                            <div class="shareinfo">
                                <h2 id="item-4">{{ __('How We Share Your Information') }}</h2>
                                <p>{!! __(':x We engage trusted third-party service providers who assist us in delivering our Services, such as payment processors, fraud prevention services, customer support, and data analytics. These service providers are authorized to use your personal information only as necessary to provide services to us.', ['x' => '<span>'.__('Service Providers:').'</span>']) !!}</p>
                                <p>{!! __('We may disclose your information if required by law or if we believe that such disclosure is necessary to protect our rights, the safety of our users, or to comply with a judicial proceeding, court order, or legal process.', ['x' => '<span>'.__('Legal Compliance and Safety:').'</span>']) !!}</p>
                                <p>{!! __('In the event of a merger, acquisition, or sale of all or a portion of our assets, your personal information may be transferred as part of the transaction. We will notify you via email and/or prominent notice on our website of any change in ownership or the use of your personal information.', ['x' => '<span>'.__('Business Transfers:').'</span>']) !!}</p>
                            </div>
                            <div class="data-security">
                                <h2 id="item-5">{{ __('Data Security') }}</h2>
                                <p>{{ __('We employ industry-standard security measures to protect your personal information from unauthorized access, use, or disclosure. However, please be aware that no data transmission over the internet or storage system can be guaranteed to be 100% secure. Therefore, while we strive to protect your information, we cannot guarantee its absolute security.') }}</p>
                            </div>
                            <div class="data-policy">
                                <h2 id="item-6">{{ __('Updates to this Privacy Policy') }}</h2>
                                <p>{{ __('We reserve the right to update or modify this Privacy Policy at any time without prior notice. Any changes will be effective immediately upon posting the revised Privacy Policy on our website. We encourage you to review this Privacy Policy periodically for any updates. Your continued use of our Services after any changes will constitute your acceptance of the revised Privacy Policy.') }}</p>
                            </div>
                            <div class="data-contact">
                                <h2 id="item-7">{{ __('Contact Us') }}</h2>
                                <p>{{ __('If you have any questions, concerns, or requests regarding this Privacy Policy or our privacy practices, please contact us at') }} <a href="{{ url('/') }}"> {{ __('here') }}.</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
