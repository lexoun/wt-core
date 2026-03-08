<?php

// Info : ROLES
// 1 : ADMIN
// 2 : BLOGGER
// 3 : MEMBER
// 4 : ANONYMOUS (Default)

// List all categories
$existing_routes = [
    'homepage' => [],
    'client' => [
        'subsidiaries' => [
            'list' => [],
            'single' => [],
        ]
    ]
];

// List all actions
$existing_actions = [
    'action_homepage' => [],
    'action_login' => [],
    'action_logout' => [],
    'action_signup' => [],
    'action_article_new' => [
        'min_access' => 2
    ],
    'action_profile_edit' => [
        'min_access' => 3
    ],
    'action_admin' => [
        'min_access' => 1
    ],
    'action_article_edit' => [
        'min_access' => 3
    ],
];

if (!empty($_GET['main'])) {

    // Check if route exist
    if (array_key_exists($_GET['main'], $existing_routes)) {

        $main = $_GET['main'];

        $main_subs = $existing_routes[$_GET['main']];

        $list_subs = $main_subs['subsidiaries'];

        // Check if subsidiary exist
        if (array_key_exists($_GET['subsidiary'], $list_subs)) {


            $subsidiary = $_GET['subsidiary'];

        // Check if action exist
         } elseif (array_key_exists($_GET['action'], $existing_actions)) {

            $action = $_GET['action'];
            
        }


   

    } else {

        $message = [
            'type' => 'error',
            'title' => 'Error 404',
            'text' => 'Stránka neexistuje'
        ];

    }
}
