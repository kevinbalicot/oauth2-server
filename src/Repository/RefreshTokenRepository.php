<?php

namespace AuthenticationServer\Repository;

use AuthenticationServer\Entity\RefreshToken;
use Doctrine\ORM\EntityRepository;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository extends EntityRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @return RefreshToken
     */
    public function getNewRefreshToken()
    {
        return new RefreshToken();
    }

    /**
     * @param RefreshTokenEntityInterface $refreshTokenEntity
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $this->getEntityManager()->persist($refreshTokenEntity);
        $this->getEntityManager()->flush();
    }

    /**
     * @param string $tokenId
     */
    public function revokeRefreshToken($tokenId)
    {
        /** @var RefreshToken $refreshToken */
        $refreshToken = $this->find($tokenId);

        if (null !== $refreshToken) {
            $this->getEntityManager()->persist($refreshToken->revoke());
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param string $tokenId
     * @return bool
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        /** @var RefreshToken $refreshToken */
        $refreshToken = $this->find($tokenId);

        if (null !== $refreshToken && !$refreshToken->isExpired()) {
            return false;
        }

        return true;
    }
}