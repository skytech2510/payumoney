<?php
return [
    'welcome'         => [
        'name'                      => 'Verser de l\'argent',
        'version'                   => env('APP_VERSION'),
        'title'                     => 'Bienvenue à l\'installateur!',
        'message'                   => "Assistant d'installation et de configuration facile",
        'requirementcheckingbutton' => 'Commencez par vérifier les exigences',
        'serverrequirements'        => 'Configuration requise pour le serveur',
        'back_button'               => 'Retour',
        'check_permission'          => 'Vérifier les autorisations',
        'serverpermissions'         => 'Autorisations de dossier',
        'check_perchase_code'       => "Vérifier le code d'achat Envato",
        'verify_code_title'         => "Vérifier le code d'achat Envato",
        'envato_label_text'         => "Code d'achat Envato",
        'envato_placeholder_text'   => "Entrez le code d'achat",
        'verify_button'             => "Vérifier le code d'achat",
        'current_text'              => 'Actuel',
        'version_text'              => 'version',
        'required_text'             => 'Champs obligatoires',
        'cancel'                    => 'Annuler',
        'envato_username'           => 'Nom d\'utilisateur envato',
        'envato_username_placeholder' => 'Entrer envah Nom d\'utilisateur'
    ],
    'database'        => [
        'title'          => 'Paramètre de base',
        'sub-title'      => 'Si vous ne savez pas comment remplir ce formulaire, contactez votre hébergeur',
        'dbname-label'   => 'Nom de la base de données (où vous voulez que votre application soit)',
        'username-label' => 'Nom d\'utilisateur (Identifiant de votre base de données)',
        'password-label' => 'Mot de passe (mot de passe de votre base de données',
        'host-label'     => 'Nom d\'hôte (devrait être "localhost", si cela ne fonctionne pas, demandez à votre hébergeur)',
        'wait'           => 'Un peu de patience ...',
        'dbbutton'       => 'Créer une base de données',

    ],
    'database-error'  => [
        'title'     => 'Erreur de connexion à la base',
        'sub-title' => 'Nous ne pouvons pas nous connecter à la base de données avec vos paramètres:',
        'item1'     => 'Êtes-vous sûr de votre nom d\'utilisateur et mot de passe?',
        'item2'     => 'Êtes-vous sûr de votre nom d\'hôte?',
        'item3'     => 'Êtes-vous sûr que votre serveur de base de données fonctionne?',
        'message'   => 'Si vous n\'êtes pas très sûr de comprendre tous ces termes, vous devriez contacter votre hébergeur.',
        'button'    => 'Réessayer !',

    ],
    'register'        => [
        'title'              => 'Création d\'un administrateur',
        'sub-title'          => 'Vous devez maintenant entrer les informations pour créer l\'administrateur',
        'base-label'         => 'Votre',
        'message'            => 'Vous aurez besoin de votre mot de passe pour vous connecter, alors gardez-le en sécurité!',
        'create_user_button' => 'Créer un utilisateur',

    ],
    'register-fields' => [
        'first_name' => 'Prénom',
        'last_name'  => 'Nom de famille',
        'email'      => 'email',
        'password'   => 'mot de passe',

    ],
    'end'             => [
        'title'     => 'Installation réussie!',
        'sub-title' => 'L\'application et maintenant installé et vous pouvez l\'utiliser',
        'login'     => 'Votre identifiant:',
        'password'  => 'Votre mot de passe :',
        'button'    => 'S\'identifier',
    ],
    'error' => [
        'invalid' => 'Il y a une maintenance de serveur en cours.Nous espérons votre coopération.',
        'oops' => 'OUPS',
        'code' => 'Veuillez vérifier votre code d\'achat et votre nom d\'utilisateur.'
    ]
];
