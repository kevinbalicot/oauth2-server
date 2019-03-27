<?php

namespace AuthenticationServer\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;

class Scope implements ScopeEntityInterface {

    use EntityTrait;

    /**
     * @var ArrayCollection
     */
    private $users;

    /**
     * @var ArrayCollection
     */
    private $clients;

    /**
     * Scope constructor.
     * @param $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
        $this->users = new ArrayCollection();
        $this->clients = new ArrayCollection();
    }

    /**
     * @param UserEntityInterface $user
     * @return $this
     */
    public function addUser(UserEntityInterface $user)
    {
        /** @var User $user */
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
        /** @var User $user */
        foreach ($this->users as $u) {
            if ($u->getIdentifier() == $user->getIdentifier()) {
                return $u;
            }
        }

        return false;
    }

    /**
     * @param UserEntityInterface $user
     * @return $this
     */
    public function removeUser(UserEntityInterface $user)
    {
        $this->users->removeElement($user);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param ClientEntityInterface $client
     * @return $this
     */
    public function addClient(ClientEntityInterface $client)
    {
        /** @var Client $c */
        foreach ($this->clients as $c) {
            if ($c->getIdentifier() == $client->getIdentifier()) {
                return false;
            }
        }

        $this->users->add($client);

        return $this;
    }

    /**
     * @param ClientEntityInterface $client
     * @return bool|mixed|null
     */
    public function hasClient(ClientEntityInterface $client)
    {
        /** @var Client $c */
        foreach ($this->clients as $c) {
            if ($c->getIdentifier() == $client->getIdentifier()) {
                return $client;
            }
        }

        return false;
    }

    /**
     * @param ClientEntityInterface $client
     * @return $this
     */
    public function removeClient(ClientEntityInterface $client)
    {
        $this->users->removeElement($client);

        return $this;
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
    function jsonSerialize()
    {
        return $this->identifier;
    }
}
