<?php

namespace AuthenticationServer\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use JsonSerializable;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;
use Doctrine\ORM\Mapping as ORM;

class User implements UserEntityInterface, JsonSerializable {

    use EntityTrait;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $email;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var \DateTime
     */
    private $disabledAt;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var ArrayCollection
     */
    private $scopes;

    /**
     * @var ArrayCollection
     */
    private $clients;

    /**
     * @var string
     */
    private $googleId;

    /**
     * @var string
     */
    private $passwordSecret;

    /**
     * User constructor.
     * @param $identifier
     * @param $password
     * @param $email
     * @param $firstName
     * @param $lastName
     * @param array $attributes
     */
    public function __construct($identifier, $password, $email, $firstName = null, $lastName = null, $attributes = [])
    {
        $this->identifier = $identifier;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->attributes = $attributes;
        $this->enabled = true;
        $this->disabledAt = null;
        $this->createdAt = new \DateTime();
        $this->scopes = new ArrayCollection();
        $this->clients = new ArrayCollection();
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
     * @param ScopeEntityInterface $scope
     * @return $this
     */
    public function removeScope(ScopeEntityInterface $scope)
    {
        $this->scopes->removeElement($scope);

        return $this;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     * @return $this
     */
    public function setEnabled($enabled)
    {
        if ($enabled) {
            $this->enabled = true;
            $this->disabledAt = null;
        } else {
            $this->enabled = false;
            $this->disabledAt = new \DateTime();
        }

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @return ArrayCollection
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param string $id
     */
    public function setGoogleId($id)
    {
        $this->googleId = $id;
    }

    /**
     * @return string
     */
    public function getPasswordSecret()
    {
        return $this->passwordSecret;
    }

    /**
     * @param string $passwordSecret
     */
    public function setPasswordSecret($passwordSecret)
    {
        $this->passwordSecret = $passwordSecret;
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

    /**
     * @param boolean $extended
     */
    function jsonSerialize($extended = false)
    {
        $params = [
            'identifier' => $this->identifier,
            'email' => $this->email,
            'firstname' => $this->firstName,
            'lastname' => $this->lastName,
            'created_at' => $this->createdAt,
            'attributes' => $this->attributes
        ];

        if ($extended) {
            $params['google_id'] = $this->googleId;
            $params['scopes'] = array_map(function($scope) {
                return $scope->jsonSerialize();
            }, $this->getScopes()->toArray());

            $params['enabled'] = $this->enabled;
            $params['disabled_at'] = $this->disabledAt;
        }

        return $params;
    }
}
