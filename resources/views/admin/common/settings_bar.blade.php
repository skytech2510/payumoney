<div class="box box-info box_info">
    <div class="panel-body">
        <h4 class="all_settings f-18">
            {{ __('Manage Settings') }}
        </h4>
        <ul class="nav navbar-pills nav-tabs nav-stacked no-margin row settings-nav" role="tablist">

            @if(Common::has_permission(auth('admin')->user()->id, 'view_general_setting'))
                <li class="{{ (Route::current()->uri() == config('adminPrefix') . '/settings') ? 'active' : '' }}">
                    <a data-group="settings" href="{{ url(config('adminPrefix').'/settings') }}">
                        <i class="fa fa-gear">
                        </i>
                        <span>
                            {{ __('General') }}
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(auth('admin')->user()->id, 'view_admin_security'))
            <li <?= $settings_menu == 'admin-security-settings' ? ' class="treeview active"' : 'treeview'?>>
                <a href="{{ url(config('adminPrefix').'/settings/admin-security-settings') }}">
                    <i class="fa fa-shield">
                    </i>
                    <span>
                        {{ __('Admin Security') }}
                    </span>
                </a>
            </li>
            @endif

            @if(Common::has_permission(auth('admin')->user()->id, 'view_social_links'))
                <li <?= $settings_menu == 'social_links' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url(config('adminPrefix').'/settings/social_links') }}">
                        <i class="fa fa-share-alt">
                        </i>
                        <span>
                           {{ __(' Social Links') }}
                        </span>
                    </a>
                </li>
            @endif



            @if(Common::has_permission(auth('admin')->user()->id, 'view_api_credentials'))
                <li <?= $settings_menu == 'api_informations' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url(config('adminPrefix').'/settings/api_informations') }}">
                        <i class="fa fa-key">
                        </i>
                        <span>
                            {{ __('Google reCaptcha') }}
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(auth('admin')->user()->id, 'view_appstore_credentials'))
                <li <?= $settings_menu == 'app-store-credentials' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url(config('adminPrefix').'/settings/app-store-credentials') }}">
                        <i class="fa fa-key">
                        </i>
                        <span>
                            {{ __('App Store Credentials') }}
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(auth('admin')->user()->id, 'view_conversion_rate_api'))
                <li <?= $settings_menu == 'currency_conversion_rate_api' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url(config('adminPrefix').'/settings/currency-conversion-rate-api') }}">
                        <i class="fa fa-exchange"></i>
                        <span>
                            {{ __('Currency Conversion Api') }}
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(auth('admin')->user()->id, 'view_email_setting'))
                <li <?= $settings_menu == 'email' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url(config('adminPrefix').'/settings/email') }}">
                        <i class="fa fa-envelope">
                        </i>
                        <span>
                            {{ __('Email Settings') }}
                        </span>
                    </a>
                </li>
            @endif


            @if(Common::has_permission(auth('admin')->user()->id, 'view_sms_setting'))
                <li <?= $settings_menu == 'sms' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url(config('adminPrefix').'/settings/sms/twilio') }}">
                        <i class="fa fa-envelope-o"></i>
                        <span>
                            {{ __('SMS Settings') }}
                        </span>
                    </a>
                </li>
            @endif

            {{-- Notification Settings --}}
            @if(Common::has_permission(auth('admin')->user()->id, 'view_notification_setting'))
            <li <?= $settings_menu == 'notification-settings' ? ' class="treeview active"' : 'treeview'?>>
                <a href="{{ url(config('adminPrefix').'/settings/notification-types') }}">
                    <i class="fa fa-bell">
                    </i>
                    <span>
                        {{ __('Notification Settings') }}
                    </span>
                </a>
            </li>
            @endif

            <!-- Referral -->
            @if(config('referral.is_active') && Common::has_permission(auth('admin')->user()->id, 'view_referral_settings'))
                <li <?= $settings_menu == 'referral_settings' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url(config('adminPrefix').'/settings/referral-preferences') }}">
                        <i class="fa fa-user-plus"></i>
                        <span>
                            {{ __('Referral Settings') }}
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(auth('admin')->user()->id, 'view_country'))
                <li <?= $settings_menu == 'country' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url(config('adminPrefix').'/settings/country') }}">
                        <i class="fa fa-flag">
                        </i>
                        <span>
                            {{ __('Countries') }}
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(auth('admin')->user()->id, 'view_language'))
                <li <?= $settings_menu == 'language' ? ' class="treeview active"' : 'treeview'?>>
                    <a data-group="language" href="{{ url(config('adminPrefix').'/settings/language') }}">
                        <i class="fa fa-language">
                        </i>
                        <span>
                            {{ __('Languages') }}
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(auth('admin')->user()->id, 'view_merchant_group'))
                <li <?= $settings_menu == 'merchant_group' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url(config('adminPrefix').'/settings/merchant-group') }}">
                        <i class="fa fa-user-secret"></i>
                        <span>
                            {{ __('Merchant Packages') }}
                        </span>
                    </a>
                </li>
            @endif


            @if(Common::has_permission(auth('admin')->user()->id, 'view_group'))
                <li <?= $settings_menu == 'user_role' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url(config('adminPrefix').'/settings/user_role') }}">
                        <i class="fa fa-object-group"></i>
                        <span>
                            {{ __('User Groups') }}
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(auth('admin')->user()->id, 'view_role'))
                <li <?= $settings_menu == 'role' ? ' class="treeview active"' : 'treeview'?>>
                    <a data-group="permissions_roles" href="{{ url(config('adminPrefix').'/settings/roles') }}">
                        <i class="fa fa-key"></i>
                        <span>
                            {{ __('Roles Permissions') }}
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(auth('admin')->user()->id, 'view_database_backup'))
                <li <?= $settings_menu == 'backup' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url(config('adminPrefix').'/settings/backup') }}">
                        <i class="fa fa-database">
                        </i>
                        <span>
                            {{ __('Database Backup') }}
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(auth('admin')->user()->id, 'view_meta'))
                <li <?= $settings_menu == 'meta' ? ' class="treeview active"' : 'treeview'?>>
                    <a data-group="metas" href="{{ url(config('adminPrefix').'/settings/metas') }}">
                        <i class="fa fa-info-circle">
                        </i>
                        <span>
                            {{ __('Metas') }}
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(auth('admin')->user()->id, 'view_page'))
                <li <?= $settings_menu == 'pages' ? ' class="treeview active"' : 'treeview'?>>
                    <a data-group="metas" href="{{ url(config('adminPrefix').'/settings/pages') }}">
                        <i class="fa fa-pagelines"></i>
                        <span>
                            {{ __('Pages') }}
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(auth('admin')->user()->id, 'view_preference'))
                <li <?= $settings_menu == 'preference' ? ' class="treeview active"' : 'treeview'?>>
                    <a data-group="preference" href="{{ url(config('adminPrefix').'/settings/preference') }}">
                        <i class="fa fa-cogs">
                        </i>
                        <span>
                            {{ __('Preferences') }}
                        </span>
                    </a>
                </li>
            @endif

        </ul>
    </div>
</div>
