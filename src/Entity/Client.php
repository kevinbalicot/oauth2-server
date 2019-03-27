<?php

namespace AuthenticationServer\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;

class Client implements ClientEntityInterface {

    use EntityTrait, ClientTrait;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var ArrayCollection
     */
    private $scopes;

    /**
     * @var ArrayCollection
     */
    private $users;

    /**
     * User constructor.
     * @param $identifier
     * @param $name
     * @param $secret
     * @param $redirectUri
     */
    public function __construct($identifier, $name, $secret, $redirectUri = null)
    {
        $this->secret = $secret;
        $this->identifier = $identifier;
        $this->name = $name;
        $this->redirectUri = $redirectUri;
        $this->scopes = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param ScopeEntityInterface $scope
     * @return $this
     */
    public function addScope(ScopeEntityInterface $scope)
    {
        /** @var Scope $scope */
        foreach ($this->scopes as $s) {
            if ($s->getIdentifier() == $scope->getIdentifier()) {
                return false;
            }
        }

        $this->scopes->add($scope);

        return $this;
    }

    /**
     * @param UserEntityInterface $user
     * @return $this
     */
    public function addUser(UserEntityInterface $user)
    {
        /** @var User $u */
        foreach ($this->users as $u) {
            if ($u->getIdentifier() == $user->getIdentifier()) {
                return false;
            }
        }

        $this->users->add($user);

        return $this;
    }

    /**
     * @param UserEntityInterface $user
     * @return bool|mixed|null
     */
    public function hasUser(UserEntityInterface $user)
    {
        /** @var Scope $scope */
        foreach ($this->users as $u) {
            if ($u->getIdentifier() == $user->getIdentifier()) {
                return $u;
            }
        }

        return false;
    }

    /**
     * @param ScopeEntityInterface $scope
     * @return bool|mixed|null
     */
    public function hasScope(ScopeEntityInterface $scope)
    {
        /** @var Scope $scope */
        foreach ($this->scopes as $s) {
            if ($s->getIdentifier() == $scope->getIdentifier()) {
                return $s;
            }
        }

        return false;
    }

    /**
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param ScopeEntityInterface $scope
     * @return $this
     */
    public function removeScope(ScopeEntityInterface $scope)
    {
        $this->scopes->removeElement($scope);

        return $this;
    }

    /**
     * @param array $scopes
     *
     * @return array
     */
    public function validateScopes(array $scopes)
    {
        $result = [];

        /** @var Scope $scope */
        foreach ($scopes as $scope) {
            $validScope = $this->hasScope($scope);

            if ($validScope) {
                $result[] = $validScope;
            }
        }

        return $result;
    }
}
