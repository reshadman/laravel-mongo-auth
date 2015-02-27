<?php namespace Reshadman\LmAuth;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class LmAuthServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__.'lmauth.php' => config_path('lmauth	.php'),
		]);

		$this->app['auth']->extend('lmauth', function(Application $app){

			$config = $app['config']->get('lmauth');

			return new MongoDbUserProvider($app['lmauth.collection'], $app['hash'], $config);

		});
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$config = $config = $this->app['config']->get('lmauth');

		if($config['use_default_collection_provider']) {

			if(! is_null($closure = $config['default_connection_closure'])){

				$connection = $closure($this->app);

				$this->app->singleton('lmauth.connection', $connection);

			}else {

				$this->app->singleton('lmauth.connection', function(Application $app){

					return new \MongoClient();

				});

			}

			$this->app->singleton('lmauth.collection', function(Application $app) use($config) {

				$mongoClient = $app['lmauth.connection'];

				return (new MongoConnection($mongoClient, $config))
					->getDefaultDatabase()->{$config['auth_collection_name']};

			});

		}
	}

}
