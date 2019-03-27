<?php

namespace AuthenticationServer\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class RefreshToken implements RefreshTokenEntityInterface {

    use EntityTrait, TokenEntityTrait;

    /**
     * @var AccessTokenEntityInterface
     */
    private $accessToken;

    /**
     * @var bool
     */
    private $revoked;

    /**
     * RefreshToken constructor.
     */
    public function __construct()
    {
        $this->scopes = new ArrayCollection();
        $this->revoked = false;
    }

    /**
     * Set the access token that the refresh token was associated with.
     *
     * @param \League\OAuth2\Server\Entities\AccessTokenEntityInterface $accessToken
     *
     * @return $this
     */
    public function setAccessToken(AccessTokenEntityInterface $accessToken)
    {
        $this->accessToken = $accessToken;
        $this->userIdentifier = $accessToken->getUserIdentifier();
        $this->client = $accessToken->getClient();

        return $this;
    }

    /**
     * Get the access token that the refresh token was originally associated with.
     *
     * @return \League\OAuth2\Server\Entities\AccessTokenEntityInterface
     */
    public function getAccessToken()
    {
        return $this->accessToken;
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
}
