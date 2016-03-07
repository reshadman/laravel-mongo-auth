## Laravel Native MongoDB Authentication Driver
This package does not require any external MongoDB related dependencies except the Php MongoDB Driver and simply uses ```Auth::extend()``` to extend the native Laravel Auth module.

### Installation
1. Run ```composer require reshadman/laravel-mongo-auth``` in your project's composer root.
2. Add the ```Reshadman\LmAuth\LmAuthServiceProvider``` service provider to your app.
4. Run ```php artisan vendor:publish``` command to generate package config files.
3. In ```auth.php``` config set the ```driver``` to ```lmauth``` :
```php
<?php return [
    //...
    'driver' => 'lmauth'
];
```

### Approach
An instance of ```MongoCollection``` is passed to ```\Reshadman\LmAuth\MongoDbUserProvider``` like below :

From ```\Reshadman\LmAuth\LmAuthServiceProvider``` :
```php
<?php
$this->app['auth']->extend('lmauth', function(Application $app){

    $config = $app['config']->get('lmauth');

	return new MongoDbUserProvider(
	    $app['lmauth.collection'], // Should be bound to an instance of \MongoCollection
	    $app['hash'],
	    $config
	);

});
```
The above code needs ```$app['lmauth.collection']``` to be available in IoC container which is an instance of ```MongoCollection```.
If you set the ```use_default_collection_provider``` config option to true, the package will create a new binding for that.

You may also create your own driver with another driver name and pass your own config and mongo collection instance to it.

```php
<?php
Auth::extend('my-lmauth', function($app) {
    $config = $app['config']->get('lmauth'); // or your desired
    $mongoCollection = $app['global_mongo_connection']->myDb->myCollection;

    return new \Reshadman\LmAuth\MongoDbUserProvider($mongoCollection, $app['hash'], $config);
});
```

#### The ```default_connection_closure``` config
If you pass a closure to this config key then the package will use this closure to get the ```MongoClient``` connection to connect to mongodb. Usefull when using Doctrine Mongo db package or alternatives.
```php
<?php return [
    //...
    'default_connection_closure' => function($app) {
        return new \MongoClient('192.168.0.2:27013'); // or $app['mongo.singleton_connection']
    }
];
```
If you set the above option to ``` null ``` the the package will use a singleton shared instance ( ```new \MongoClient()```) which has ```lmauth.connection``` key in the container.
