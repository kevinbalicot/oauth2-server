<?php

namespace AuthenticationServer\Command\Client;

use AuthenticationServer\Command\Command;
use AuthenticationServer\Entity\Client;
use AuthenticationServer\Entity\Scope;
use AuthenticationServer\Repository\ClientRepository;
use AuthenticationServer\Repository\ScopeRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class AddScopeToClient extends Command
{
    protected function configure()
    {
        $this->setName('client:scope')
            ->addArgument('clientIdentifier', InputArgument::OPTIONAL, 'Client identifier')
            ->addArgument('scopeIdentifier', InputArgument::OPTIONAL, 'Scope identifier')
            ->setHelp('Add scope to client.')
            ->setDescription('Add scope to client.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $clientIdentifier = $input->getArgument('clientIdentifier');
        $scopeIdentifier = $input->getArgument('scopeIdentifier');

        /** @var ClientRepository $clientRepository */
        $clientRepository = $this->container->get('client_repository');

        if (null === $clientIdentifier) {
            $clientIdentifierQuestion = new Question('What client identifier ? : ', null);
            $clientIdentifier = $helper->ask($input, $output, $clientIdentifierQuestion);
        }

        /** @var Client $client */
        $client = $clientRepository->find($clientIdentifier);

        if (is_null($client)) {
            throw new EntityNotFoundException('Client "' . $clientIdentifier . '" not found.');
        }

        /** @var ScopeRepository $scopeRepository */
        $scopeRepository = $this->container->get('scope_repository');

        if (null === $scopeIdentifier) {
            $scopeIdentifierQuestion = new Question('What scope identifier ? : ', null);
            $scopeIdentifier = $helper->ask($input, $output, $scopeIdentifierQuestion);
        }

        /** @var Scope $scope */
        $scope = $scopeRepository->find($scopeIdentifier);

        if (is_null($scope)) {
            throw new EntityNotFoundException('Scope "' . $scopeIdentifier . '" not found.');
        }

        $client->addScope($scope);

        $clientRepository->persist($client);

        $output->writeln('Scope "' . $scopeIdentifier . '" added at client "' . $clientIdentifier . '"');
    }
}
