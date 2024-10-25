<?php

return [

    /**
     *
     * Welcome page.
     *
     */
    'welcome'         => [
        'name'                      => 'Pay Money',
        'version'                   => env('APP_VERSION'),
        'title'                     => 'Welcome to the Installer !',
        'message'                   => 'Easy installation and setup wizard',
        'requirementcheckingbutton' => 'Start with checking requirements',
        'serverrequirements'        => 'Server Requirements',
        'back_button'               => 'Back',
        'check_permission'          => 'Check permissions',
        'serverpermissions'         => 'Folder permissions',
        'check_perchase_code'       => 'Verify Envato Purchase code',
        'verify_code_title'         => 'Verify Envato Purchase Code',
        'envato_label_text'         => 'Envato Purchase code',
        'envato_placeholder_text'   => 'Enter Purchase Code',
        'verify_button'             => 'Verify Purchase Code',
        'current_text'              => 'Current',
        'version_text'              => 'version',
        'required_text'             => 'required',
        'cancel'                    => 'Cancel',
        'envato_username'           => 'Envato Username',
        'envato_username_placeholder' => 'Enter envato username'
    ],

    /**
     *
     * Database page.
     *
     */
    'database'        => [
        'title'          => 'Database setting',
        'sub-title'      => 'If you dont know how to fill this form contact your hoster',
        'dbname-label'   => 'Database name (where you want your application to be)',
        'username-label' => 'Username (Your database login)',
        'password-label' => 'Password (Your database password)',
        'host-label'     => 'Host name (should be "localhost", if it doesn\'t work ask your hoster)',
        'wait'           => 'Please wait a moment...',
        'dbbutton'       => 'Create database',
        'port'           => 'Port (For MySQL use port 3306 and for MariaDB 3307)'
    ],

    /**
     *
     * Database error page.
     *
     */
    'database-error'  => [
        'title'     => 'Database connection error',
        'sub-title' => 'We cant connect to database with your settings :',
        'item1'     => 'Are you sure of your username and password ?',
        'item2'     => 'Are you sure of your host name ?',
        'item3'     => 'Are you sure that your database server is working ?',
        'message'   => 'If your are not very sure to understand all these terms you should contact your hoster.',
        'button'    => 'Try again !',
    ],

    /**
     *
     * Register page.
     *
     */
    'register'        => [
        'title'              => 'Administrator creation',
        'sub-title'          => 'Now you must enter informations to create administrator',
        'base-label'         => 'Your ',
        'message'            => 'You\'ll need your password to login, so keep it safe !',
        'create_user_button' => 'Create user',
    ],

    /**
     *
     * Register fields for labels.
     *
     */
    'register-fields' => [
        'first_name' => 'First Name',
        'last_name'  => 'Last Name',
        'email'      => 'email',
        'password'   => 'password',
    ],

    /**
     *
     * End page.
     *
     */
    'end'             => [
        'title'     => 'Your application has been successfully installed!',
        'sub-title' => 'The application and now installed and you can use it',
        'login'     => 'Your login : ',
        'password'  => 'Your password :',
        'button'    => 'Login',
    ],
    
    'error' => [
        'invalid' => 'There is a server maintance going on. We are hoping for your co-operation.',
        'oops' => 'OOPS',
        'code' => 'Please verify your purchase code and username.'
    ]

];
