<li class="nav-item">
    <a class="nav-link {{ $user_tab_menu == 'user_profile' ? 'active' : '' }}" href="{{ url(config('adminPrefix'). '/users/edit' , $users->id) }}">{{ __('Profile') }}</a>
</li>
<li class="nav-item">
    <a class="nav-link {{ $user_tab_menu == 'user_transactions' ? 'active' : '' }}" href="{{ url(config('adminPrefix')."/users/transactions", $users->id) }}">{{ __('Transactions') }}</a>
</li>
<li class="nav-item" >
    <a class="nav-link {{ $user_tab_menu == 'user_wallets' ? 'active' : '' }}" href="{{ url(config('adminPrefix')."/users/wallets", $users->id) }}">{{ __('Wallets') }}</a>
</li>
<li class="nav-item">
    <a class="nav-link {{ $user_tab_menu == 'user_tickets' ? 'active' : '' }}" href="{{ url(config('adminPrefix')."/users/tickets", $users->id) }}">{{ __('Tickets') }}</a>
</li>
<li class="nav-item">
    <a class="nav-link {{ $user_tab_menu == 'user_disputes' ? 'active' : '' }}" href="{{ url(config('adminPrefix')."/users/disputes", $users->id) }}">{{ __('Disputes') }}</a>
</li>

@if (isActive('Referral') && count($users->referralAwardAwardedUser) > 0)
    <li class="nav-item">
        <a class="nav-link {{ $user_tab_menu == 'user_referral' ? 'active' : '' }}" href="{{ route('admin.referral_awards.user_award', $users->id) }}">{{ __('Referral Awards') }}</a>
    </li>
@endif