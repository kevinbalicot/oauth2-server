<?php

namespace AuthenticationServer\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;

class AccessToken implements AccessTokenEntityInterface {

    use EntityTrait, AccessTokenTrait, TokenEntityTrait;

    /**
     * @var User
     */
    private $user;

    /**
     * @var bool
     */
    private $revoked;

    /**
     * AccessToken constructor.
     *
     * @param UserEntityInterface $user
     * @param ClientEntityInterface $client
     */
    public function __construct(UserEntityInterface $user = null, ClientEntityInterface $client = null)
    {
        $this->user = $user;
        $this->userIdentifier = $user !== null ? $user->getIdentifier() : null;
        $this->client = $client;
        $this->scopes = new ArrayCollection();
        $this->revoked = false;
    }

    /**
     * @param UserEntityInterface $user
     * @param ClientEntityInterface $client
     * @param array $scopes
     *
     * @return mixed
     */
    static public function create(UserEntityInterface $user = null, ClientEntityInterface $client = null, array $scopes = [])
    {
        $accessToken =  new self($user, $client);

        /** @var ScopeEntityInterface $scope */
        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }

        return $accessToken;
    }

    /**
     * @param CryptKey $privateKey
     *
     * @return mixed
     */
    public function convertToJWT(CryptKey $privateKey)
    {
        $builder = (new Builder())
            ->setAudience($this->getClient()->getIdentifier())
            ->setId($this->getIdentifier(), true)
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration($this->getExpiryDateTime()->getTimestamp())
            ->setSubject($this->getUserIdentifier())
            ->set('scopes', $this->getScopes());

        if (null !== $this->user) {
            $clientName = $this->client !== null ? $this->client->getIdentifier() . ':' : '';
            $builder = $builder
                ->set($clientName . 'user:first_name', $this->user->getFirstName())
                ->set($clientName . 'user:last_name', $this->user->getLastName())
                ->set($clientName . 'user:email', $this->user->getEmail());

            foreach ($this->user->getAttributes() as $key => $value) {
                $builder = $builder->set($clientName . 'user:attribute:' . $key, $value);
            }
        }

        return $builder->sign(new Sha256(), new Key($privateKey->getKeyPath(), $privateKey->getPassPhrase()))
            ->getToken();
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        $now = new \DateTime();
        return $now > $this->expiryDateTime;
    }

    /**
     * @return $this
     */
    public function revoke()
    {
        $this->revoked = true;

        return $this;
    }

    /**
     * @return array
     */
    public function getScopes()
    {
        return count($this->scopes) > 0 ? array_values($this->scopes->toArray()) : [];
    }
}
