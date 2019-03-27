<?php

namespace AuthenticationServer\Command\Client;

use AuthenticationServer\Command\Command;
use AuthenticationServer\Entity\Client;
use AuthenticationServer\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListClients extends Command
{
    protected function configure()
    {
        $this->setName('client:list')
            ->setHelp('Get list of clients.')
            ->setDescription('Get list of clients.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws EntityNotFoundException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container->get('em')->flush();

        /** @var ClientRepository $clientRepository */
        $clientRepository = $this->container->get('client_repository');

        /** @var ArrayCollection $clients **/
        $clients = $clientRepository->findAll();

        if (count($clients) === 0) {
            throw new EntityNotFoundException('No clients');
        }

        $output->writeln('========== CLIENTS ==========');
        
        /** @var Client $client */
        foreach ($clients as $client) {
            $output->writeln('Identifier: ' . $client->getIdentifier());
            $output->writeln('Name: ' . $client->getName());
            $output->writeln('Secret: ' . $client->getSecret());
            $output->writeln('----------');
        }
    }
}
