<?php

namespace AuthenticationServer\Repository;

use AuthenticationServer\Entity\Client;
use Doctrine\ORM\EntityRepository;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Ramsey\Uuid\Uuid;

class ClientRepository extends EntityRepository implements ClientRepositoryInterface
{
    /**
     * @param string $clientIdentifier
     * @param string $grantType
     * @param null $clientSecret
     * @param bool $mustValidateSecret
     * @return \League\OAuth2\Server\Entities\ClientEntityInterface|null|object
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
        if ($mustValidateSecret && null === $clientSecret) {
            return null;
        }

        if ($mustValidateSecret) {
            return $this->findOneBy(['identifier' => $clientIdentifier, 'secret' => $clientSecret]);
        }

        return $this->findOneBy(['identifier' => $clientIdentifier]);
    }

    /**
     * @param string $identifier
     * @param string $name
     * @return Client
     */
    public function create($identifier, $name)
    {
        $secret = Uuid::uuid4()->toString();
        $client = new Client($identifier, $name, $secret);
        $this->persist($client);

        return $client;
    }

    /**
     * @param Client $client
     */
    public function persist(Client $client)
    {
        $this->getEntityManager()->persist($client);
        $this->getEntityManager()->flush();
    }
}