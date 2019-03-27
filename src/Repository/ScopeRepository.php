<?php

namespace AuthenticationServer\Repository;

use AuthenticationServer\Entity\Client;
use AuthenticationServer\Entity\Scope;
use AuthenticationServer\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeRepository extends EntityRepository implements ScopeRepositoryInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * ScopeRepository constructor.
     * @param EntityManager $em
     * @param ClassMetadata $class
     * @param UserRepository $userRepository
     */
    public function __construct(EntityManager $em, ClassMetadata $class, UserRepository $userRepository)
    {
        parent::__construct($em, $class);
        $this->userRepository = $userRepository;
    }

    /**
     * Return information about a scope.
     *
     * @param string $identifier
     * @return \League\OAuth2\Server\Entities\ScopeEntityInterface|null|object
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        return $this->find($identifier);
    }

    /**
     * Given a client, grant type and optional user identifier validate the set of scopes requested are valid and optionally
     * append additional scopes or remove requested scopes.
     *
     * @param array $scopes
     * @param string $grantType
     * @param ClientEntityInterface $clientEntity
     * @param null $userIdentifier
     *
     * return array
     * @return array|\League\OAuth2\Server\Entities\ScopeEntityInterface[]
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        if (null !== $userIdentifier) {
            /** @var User $user */
            $user = $this->userRepository->find($userIdentifier);

            if (null !== $user) {
                return $user->validateScopes($scopes);
            }
        }

        if ($clientEntity instanceof Client) {
            $scopes = $clientEntity->validateScopes($scopes);
        }

        $scopeIdentifiers = array_map(function($scope) {
            return $scope->getIdentifier();
        }, $scopes);

        return $this->findBy(['identifier' => $scopeIdentifiers]) ?: [];
    }

    /**
     * @param $identifier
     * @return Scope
     */
    public function create($identifier)
    {
        $scope = new Scope($identifier);
        $this->getEntityManager()->persist($scope);
        $this->getEntityManager()->flush();

        return $scope;
    }
}