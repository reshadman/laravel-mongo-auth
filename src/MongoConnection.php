<?php namespace Reshadman\LmAuth;

class MongoConnection {

    /**
     * Our mongo db client
     *
     * @var \MongoClient
     */
    protected $client;

    /**
     * Config for connections etc
     *
     * @var array
     */
    protected $config;

    /**
     * @var \MongoDb
     */
    protected $defaultDb;

    /**
     * @param \MongoClient $client
     * @param array $config
     */
    public function __construct(\MongoClient $client, array $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Get default database
     *
     * @return \MongoDB
     */
    public function getDefaultDatabase()
    {
        if(is_null($this->defaultDb))
        {
            $this->defaultDb = $this->client->selectDB($this->config['database_name']);
        }

        return $this->defaultDb;
    }

    /**
     * Call all not available calls from default db
     *
     * @param $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, array $args = [])
    {
        return call_user_func_array([$this->getDefaultDatabase(), $method], $args);
    }
}
