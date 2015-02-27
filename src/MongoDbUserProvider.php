<?php namespace Reshadman\LmAuth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher;
use MongoId;

class MongoDbUserProvider implements UserProvider {

    /**
     * @var \MongoCollection
     */
    protected $collection;

    /**
     * The mongodb config
     *
     * @var array
     */
    protected $config;

    /**
     * Should id field be decorated or not
     *
     * @var bool
     */
    protected $idShouldBeDecorated = false;

    /**
     * @param \MongoCollection $collection
     * @param Hasher $hasher
     * @param array $config
     */
    public function __construct(\MongoCollection $collection,Hasher $hasher, array $config)
    {
        $this->collection = $collection;
        $this->hasher = $hasher;
        $this->config = $config;

        $this->checkMongoObjectId();
    }

    /**
     * Check that id should be decorated with object id or not
     */
    protected function checkMongoObjectId()
    {
        if($this->getIdentificationField() == '_id')
        {
            $this->idShouldBeDecorated = true;
        }
    }

    /**
     * Should id be decorated with object id or not ?
     *
     * @return bool
     */
    protected function idShouldBeDecorated()
    {
        return $this->idShouldBeDecorated;
    }

    /**
     * Get our hasher contract
     *
     * @return Hasher
     */
    protected function getHasher()
    {
        return $this->hasher;
    }

    /**
     * Get collection name for users storage
     *
     * @return string
     */
    protected function getCollectionName()
    {
        return $this->config['auth_collection_name'];
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        if($this->idShouldBeDecorated()) $identifier = $this->makeObjectId($identifier);

        $user = $this->getCollection()->findOne([$this->getIdentificationField() => $identifier]);

        return $this->makeUserContract($user);
    }

    /**
     * Make mongo objectId
     *
     * @param $identifier
     * @return MongoId
     */
    protected function makeObjectId($identifier)
    {
        return new MongoId($identifier);
    }

    /**
     * Make the authenticatable user
     *
     * @param $user
     * @return GenericUser|null
     */
    protected function makeUserContract($user)
    {
        if(! is_null($user))
        {
            return new $this->config['user_class']($user);
        }

        return null;
    }

    /**
     * Get id field in mongo db collection
     *
     * @return string
     */
    protected function getIdentificationField()
    {
        return $this->config['auth_id_field'];
    }

    /**
     * Retrieve a user by by their unique identifier and "remember me" token.
     *
     * @param  mixed $identifier
     * @param  string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        if($this->idShouldBeDecorated()) $identifier = $this->makeObjectId($identifier);

        $user = $this->getCollection()->findOne([
            $this->getIdentificationField() => $identifier,
            $this->getRememberTokenField() => $token
        ]);

        return $this->makeUserContract($user);
    }

    /**
     * Get remember token field name
     *
     * @return string
     */
    public function getRememberTokenField()
    {
        return $this->config['auth_remember_token_field'];
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        $id = $user->getAuthIdentifier();

        if($this->idShouldBeDecorated()) $id = $this->makeObjectId($user->getAuthIdentifier());

        $this->getCollection()->findAndModify(
            [$this->getIdentificationField() => $id],
            ['$set' => [$this->getRememberTokenField() => $token]]
        );
    }

    /**
     * Get password field
     *
     * @return string
     */
    public function getPasswordField()
    {
        return $this->config['auth_password_field'];
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $query = []; $password = $this->getPasswordField();

        foreach($credentials as $key => $value)
        {
            if($key == '_id') $value = $this->makeObjectId($value);

            if(! str_contains($key, $password)) $query[$key] = $value;
        }

        return $this->makeUserContract($this->getCollection()->findOne($query));
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $password = $credentials[$this->getPasswordField()];

        return $this->hasher->check($password, $user->getAuthPassword());
    }

    /**
     * Get mongodb collection instance
     *
     * @return \MongoCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }
}