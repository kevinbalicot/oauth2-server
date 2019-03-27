<?php

namespace AuthenticationServer\Command\User;

use AuthenticationServer\Command\Command;
use AuthenticationServer\Entity\Client;
use AuthenticationServer\Entity\Scope;
use AuthenticationServer\Repository\ClientRepository;
use AuthenticationServer\Repository\ScopeRepository;
use AuthenticationServer\Repository\UserRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class CreateUser extends Command
{
    protected function configure()
    {
        $this->setName('user:create')
            ->addArgument('identifier', InputArgument::OPTIONAL, 'User identifier')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password user')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email user')
            ->addArgument('client', InputArgument::OPTIONAL, 'Client identifier')
            ->addArgument('firstname', InputArgument::OPTIONAL, 'First name user')
            ->addArgument('lastname', InputArgument::OPTIONAL, 'Last name user')
            ->addArgument('attributes', InputArgument::OPTIONAL, 'Attributes (json string)')
            ->setHelp('Create a new user.')
            ->setDescription('Create a new user.')
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
        $helper = $this->getHelper('question');

        $userIdentifier = $input->getArgument('identifier');
        $userPassword = $input->getArgument('password');
        $userEmail = $input->getArgument('email');
        $clientIdentifier = $input->getArgument('client');
        $userFirstName = $input->getArgument('firstname');
        $userLastName = $input->getArgument('lastname');
        $userAttribute = $input->getArgument('attributes');

        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('user_repository');
        /** @var ClientRepository $clientRepository */
        $clientRepository = $this->container->get('client_repository');
        /** @var ScopeRepository $scopeRepository */
        $scopeRepository = $this->container->get('scope_repository');

        if (null === $clientIdentifier) {
            $clientIdentifierQuestion = new Question('What client identifier ? : ', null);
            $clientIdentifier = $helper->ask($input, $output, $clientIdentifierQuestion);
        }

        /** @var Client $client */
        $client = $clientRepository->findOneBy(['identifier' => $clientIdentifier]);

        if (null === $client) {
            throw new EntityNotFoundException('Client "' . $clientIdentifier . '" not found');
        }

        if (null === $userIdentifier) {
            $userIdentifierQuestion = new Question('What user identifier ? : ', null);
            $userIdentifier = $helper->ask($input, $output, $userIdentifierQuestion);
        }

        $user = $userRepository->find($userIdentifier);

        if (null != $user) {
            throw new \Exception('User "' . $userIdentifier . '" already exists');
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $userIdentifier)) {
            throw new \Exception('Identifier has to match ^[a-zA-Z0-9_]+$');
        }

        if (null === $userPassword) {
            $userPasswordQuestion = new Question('What user password ? : ', null);
            $userPasswordQuestion->setHidden(true);
            $userPassword = $helper->ask($input, $output, $userPasswordQuestion);
        }

        if (null === $userEmail) {
            $userEmailQuestion = new Question('What user email ? : ', null);
            $userEmail = $helper->ask($input, $output, $userEmailQuestion);
        }

        if (null === $userFirstName && $input->isInteractive()) {
            $userFirstNameQuestion = new Question('What user firstname ? : ', null);
            $userFirstName = $helper->ask($input, $output, $userFirstNameQuestion);
        }

        if (null === $userLastName && $input->isInteractive()) {
            $userLastNameQuestion = new Question('What user lastname ? : ', null);
            $userLastName = $helper->ask($input, $output, $userLastNameQuestion);
        }

        if (null === $userAttribute && $input->isInteractive()) {
            $userAttributesQuestion = new Question('What user attributes (json string) ? : ', null);
            $userAttribute = $helper->ask($input, $output, $userAttributesQuestion);
        }

        $user = $userRepository->create(
            $userIdentifier,
            $userPassword,
            $userEmail,
            $userFirstName,
            $userLastName,
            null !== $userAttribute ? json_decode($userAttribute) : []
        );

        $client->addUser($user);
        $clientRepository->persist($client);

        $output->writeln('========== USER ==========');
        $output->writeln('Identifier: ' . $user->getIdentifier());
        $output->writeln('Email: ' . $user->getEmail());
        $output->writeln('First name: ' . $user->getFirstName());
        $output->writeln('Last name: ' . $user->getLastName());

        if (count($user->getAttributes()) > 0) {
            $output->writeln('Attributes');

            foreach ($user->getAttributes() as $key => $value) {
                $output->writeln($key . ': ' .$value);
            }
        }

        $addScopeQuestion = new ConfirmationQuestion('Do you want to add scope at user ? [n] ', false);
        $scopeNameQuestion = new Question('What scope name ? : ', null);

        if ($input->isInteractive() && $helper->ask($input, $output, $addScopeQuestion)) {
            do {
                $scopeName = $helper->ask($input, $output, $scopeNameQuestion);

                /** @var Scope $scope */
                $scope = $scopeRepository->find($scopeName);

                if (null != $scope) {
                    $user->addScope($scope);
                    $userRepository->persist($user);

                    $output->writeln('Scope "' . $scope->getIdentifier() . '" added');
                } else {
                    $output->writeln('No scope found.');
                }

                $continue = $helper->ask($input, $output, $addScopeQuestion);
            } while ($continue);
        }
    }
}
