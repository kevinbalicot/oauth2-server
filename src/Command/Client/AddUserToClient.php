<?php

namespace AuthenticationServer\Command\Client;

use AuthenticationServer\Command\Command;
use AuthenticationServer\Entity\Client;
use AuthenticationServer\Repository\ClientRepository;
use AuthenticationServer\Repository\UserRepository;
use Doctrine\ORM\EntityNotFoundException;
use League\OAuth2\Server\Entities\UserEntityInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class AddUserToClient extends Command
{
    protected function configure()
    {
        $this->setName('client:user')
            ->addArgument('clientIdentifier', InputArgument::OPTIONAL, 'Client identifier')
            ->addArgument('userIdentifier', InputArgument::OPTIONAL, 'User identifier')
            ->setHelp('Add user to client.')
            ->setDescription('Add user to client.')
        ;
    }

    /**
     * Create client
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        /** @var ClientRepository $clientRepository */
        $clientRepository = $this->container->get('client_repository');

        $clientIdentifier = $input->getArgument('clientIdentifier');
        $userIdentifier = $input->getArgument('userIdentifier');

        if (null === $clientIdentifier) {
            $clientIdentifierQuestion = new Question('What client identifier ? : ', null);
            $clientIdentifier = $helper->ask($input, $output, $clientIdentifierQuestion);
        }

        /** @var Client $client */
        $client = $clientRepository->find($clientIdentifier);

        if (is_null($client)) {
            throw new EntityNotFoundException('Client "' . $clientIdentifier . '" not found.');
        }

        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('user_repository');

        if (null === $userIdentifier) {
            $userIdentifierQuestion = new Question('What user identifier ? : ', null);
            $userIdentifier = $helper->ask($input, $output, $userIdentifierQuestion);
        }

        /** @var UserEntityInterface $user */
        $user = $userRepository->find($userIdentifier);

        if (is_null($user)) {
            throw new EntityNotFoundException('User "' . $userIdentifier . '" not found.');
        }

        $client->addUser($user);

        $clientRepository->persist($client);

        $output->writeln('User "' . $userIdentifier . '" added to "' . $clientIdentifier . '" client');
    }
}
