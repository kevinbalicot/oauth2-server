<?php

namespace AuthenticationServer\Repository;

use AuthenticationServer\Entity\AccessToken;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository extends EntityRepository implements AccessTokenRepositoryInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * AccessTokenRepository constructor.
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
     * @param ClientEntityInterface $client
     * @param array $scopes
     * @param null $userIdentifier
     * @return mixed
     */
    public function getNewToken(ClientEntityInterface $client, array $scopes, $userIdentifier = null)
    {
        $user = null;

        if (null !== $userIdentifier) {
            /** @var UserEntityInterface $user */
            $user = $this->userRepository->find($userIdentifier);
        }

        return AccessToken::create($user, $client, $scopes);
    }

    /**
     * @param AccessTokenEntityInterface $accessToken
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessToken)
    {
        $this->getEntityManager()->persist($accessToken);
        $this->getEntityManager()->flush();
    }

    /**
     * @param string $tokenId
     */
    public function revokeAccessToken($tokenId)
    {
        /** @var AccessToken $accessToken */
        $accessToken = $this->find($tokenId);

        if (null !== $accessToken) {
            $this->getEntityManager()->persist($accessToken->revoke());
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param string $tokenId
     * @return bool
     */
    public function isAccessTokenRevoked($tokenId)
    {
        /** @var AccessToken $accessToken */
        $accessToken = $this->find($tokenId);

        if (null !== $accessToken && !$accessToken->isExpired()) {
            return false;
        }

        return true;
    }
}
