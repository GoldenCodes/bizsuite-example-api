<?php
return [
//    'url_api'       => 'api.bizsuite.com.br',
//    'url_site'      => 'www.bizsuite.com.br',
//    'client_id'     => '6',
//    'client_secret' => 'jPKjFk5tk97xPIlemRxszLAAdqgYC8hgaNpGLhai',
    'url_api'       => 'api.bizhub.local',
    'url_site'      => 'bizhub.local:4210',
    'client_id'     => '9',
    'client_secret' => 'XuPWAMF0n11KYbPvGPhC5bRWkoeVX16k0P1DkcDq',
    'redirect_uri'  => 'http://0.0.0.0:8888/oauth/callback',
    'scopes' => [
        'catalog.category.read',
        'catalog.category.create',
        'catalog.category.update',
        'catalog.category.delete',
        'catalog.product.read',
        'catalog.product.create',
        'catalog.product.update',
        'catalog.product.delete',
    ],
];