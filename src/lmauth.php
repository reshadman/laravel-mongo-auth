<?php return [
    'user_class' => '\Reshadman\LmAuth\GenericUser',
    'auth_collection_name' => 'users',
    'auth_id_field' => '_id',
    'auth_remember_token_field' => 'remember_token',
    'auth_password_field' => 'password',
    'database_name' => 'mongo_test',
    'use_default_collection_provider' => true,
    'default_connection_closure' => null
];