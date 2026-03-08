<?php

$existing_routes = [
    'homepage' => [],
    'client' => [
        'subsidiaries' => [
            'list' => [],
            'single' => [],
        ]
    ],
    'orders' => [
        'default' => 'editace-objednavek',
        'list' => 'editace-objednavek',
        'single' => 'zobrazit-objednavku',
        'edit' => 'upravit-objednavku',
    ],
    'accessories' => [
        'default' => 'editace-prislusenstvi',
        'list' => 'editace-prislusenstvi',
        'single' => 'zobrazit-prislusenstvi',
        'add' => 'pridat-prislusenstvi',
        'edit' => 'upravit-prislusenstvi',
    ],
    'demands' => [
        'default' => 'editace-poptavek',
        'list' => 'editace-poptavek',
        'single' => 'zobrazit-poptavku',
        'add' => 'pridat-poptavku',
        'edit' => 'upravit-poptavku',
        'generate' => 'udaje-pro-generovani',
    ]
];

// todo very nice working - naplánovat

//echo $_REQUEST['main'];

echo $_REQUEST['page'];

$main_subs = $existing_routes[$_REQUEST['main']];

if(empty($_REQUEST['page'])){ $_REQUEST['page'] = 'default';  }

$page = $main_subs[$_REQUEST['page']];

echo $page;

if(!empty($_REQUEST['main']) && !empty($_REQUEST['page'])){

include($_SERVER['DOCUMENT_ROOT'].'/admin/pages/'.$_REQUEST['main'].'/'.$page.'.php');

//    echo $_SERVER['DOCUMENT_ROOT'].'/admin/pages/'.$_REQUEST['main'].'/'.$page.'.php';

}