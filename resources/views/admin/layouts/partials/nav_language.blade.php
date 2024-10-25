<?php
    $flag = Session::get('dflt_lang').'.png';
?>
<li class="dropdown notifications-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <span><img class="flags"  src='{{url("public/img/flags/$flag")}}' ></span>
    </a>
    <ul class="dropdown-menu task-bar">
        <li class="header">{{ __('Select Language') }}</li>
        <li>
            <ul class="menua">
                <li>
                <a href="#" class="lang" id='en' >
                  <img src="{{url('public/img/flags/en.png')}}" class="img"> {{ __('English') }}
                </a>
                </li>
                <li>
                <a href="#" class="lang" id='fr' >
                  <img src="{{url('public/img/flags/fr.png')}}" class="img"> {{ __('French') }}
                </a>
                </li>
                <li>
                <a href="#" class="lang" id='ar' >
                  <img src="{{url('public/img/flags/ar.png')}}" class="img"> {{ __('Arabic') }}
                </a>
                </li>
            </ul>
        </li>
    </ul>
</li>

