<?php

namespace AuthenticationServer\Repository;

use AuthenticationServer\Entity\Client;
use AuthenticationServer\Entity\User;
use AuthenticationServer\Exception\ClientHasNotUserException;
use AuthenticationServer\Hasher\HasherInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\Exception\OAuthServerException;

class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param EntityManager $em
     * @param ClassMetadata $class
     * @param HasherInterface $hasher
     */
    public function __construct(EntityManager $em, ClassMetadata $class, HasherInterface $hasher)
    {
        parent::__construct($em, $class);
        $this->hasher = $hasher;
    }

    /**
     * @var HasherInterface
     */
    private $hasher;

    /**
     * Get a user entity.
     *
     * @param string $identifier
     * @param string $password
     * @param string $grantType The grant type used
     * @param \League\OAuth2\Server\Entities\ClientEntityInterface $clientEntity
     * @return \League\OAuth2\Server\Entities\UserEntityInterface
     * @throws ClientHasNotUserException
     */
    public function getUserEntityByUserCredentials(
        $identifier,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        /** @var User $user */
        $user = $this->findOneBy(['identifier' => $identifier]);

        if (is_null($user)) {
            return null;
        } elseif ('google' !== $grantType && !$this->hasher->verify($password, $user->getPassword())) {
            return null;
        }

        if (!$user->isEnabled()) {
            throw OAuthServerException::accessDenied('Your account is disabled, please contact your administrator.');
        }

        if ($clientEntity instanceof Client && !$clientEntity->hasUser($user)) {
            throw new ClientHasNotUserException('Client hasn\'t user ' . $user->getIdentifier());
        }

        return $user;
    }

    /**
     * @param string $identifier
     * @param string $password
     * @param string $email
     * @param string null $firstName
     * @param string null $lastName
     * @param array $attributes
     *
     * @return User
     */
    public function create($identifier, $password, $email, $firstName = null, $lastName = null, $attributes = [])
    {
        $password = $this->hasher->hash($password);
        $user = new User($identifier, $password, $email, $firstName, $lastName, $attributes);
        $this->persist($user);

        return $user;
    }

    /**
     * @param User $user
     * @param $secret
     * @param $password
     * @return bool
     */
    public function changePassword(User $user, $secret, $password)
    {
        if (
            !is_null($user->getPasswordSecret()) &&
            $user->getPasswordSecret() === $secret
        ) {
            $password = $this->hasher->hash($password);
            $user->setPassword($password);
            $user->setPasswordSecret(null);

            $this->persist($user);

            return true;
        }

        return false;
    }

    /**
     * @param User $user
     * @param null $password
     * @param null $email
     *
     * @return User
     */
    public function update(User $user, $password = null, $email = null)
    {
        if (null != $password) {
            $user->setPassword($this->hasher->hash($password));
        }

        if (null != $email) {
            $user->setEmail($email);
        }

        $this->persist($user);

        return $user;
    }

    /**
     * @param User $user
     */
    public function persist(User $user)
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
