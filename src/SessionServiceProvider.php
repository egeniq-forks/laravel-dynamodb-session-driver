<?php
namespace OryxCloud\DynamoDbSessionDriver;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Session;
use Aws\DynamoDb\DynamoDbClient;
use OryxCloud\DynamoDbSessionDriver\Extensions\DynamoHandler;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/dynamodb-session.php' => config_path('dynamodb-session.php'),
        ]);

        Session::extend('dynamodb', function($app)
        {
            $config = [
                'version' => 'latest'
            ];

            if (config('dynamodb-session.region') !== null) {
                $config['region'] = config('dynamodb-session.region');
            }
            if (config('dynamodb-session.endpoint') !== null) {
                $config['endpoint'] = config('dynamodb-session.endpoint');
            }
            if (config('dynamodb-session.key') !== null && config('dynamodb-session.secret') !== null) {
                $config['credentials'] = [
                    'key'    => config('dynamodb-session.key'),
                    'secret' => config('dynamodb-session.secret'),
                ];
            } elseif (config('dynamodb-session.endpoint') === null) {
                $config['credentials'] = false;
            }

            $client = new DynamoDbClient($config);

            $config = [
                'table_name'       => config('session.table'),
                'hash_key'         => config('dynamodb-session.hash_key'),
                // Laravel lifetime is in minutes
                'session_lifetime' => config('session.lifetime') / 60
            ];

            return new DynamoHandler($client, $config);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/dynamodb-session.php', 'dynamodb-session'
        );
    }
}
