<?php

namespace AuthenticationServer\Command\User;

use AuthenticationServer\Command\Command;
use AuthenticationServer\Entity\Client;
use AuthenticationServer\Entity\Scope;
use AuthenticationServer\Entity\User;
use AuthenticationServer\Repository\AccessTokenRepository;
use AuthenticationServer\Repository\RefreshTokenRepository;
use AuthenticationServer\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class DeleteUser extends Command
{
    protected function configure()
    {
        $this->setName('user:delete')
            ->addArgument('identifier', InputArgument::OPTIONAL, 'User identifier')
            ->setHelp('Delete user.')
            ->setDescription('Delete user.')
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
        $this->container->get('em')->flush();

        $userIdentifier = $input->getArgument('identifier');

        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('user_repository');

        if (null === $userIdentifier) {
            $userIdentifierQuestion = new Question('What user identifier ? : ', null);
            $userIdentifier = $helper->ask($input, $output, $userIdentifierQuestion);
        }

        /** @var User $user **/
        $user = $userRepository->findOneBy(['identifier' => $userIdentifier]);

        if (null === $user) {
            throw new EntityNotFoundException('User "' . $userIdentifier . '" not found');
        }

        $output->writeln('========== INFO ==========');

        $output->writeln('Identifier: ' . $user->getIdentifier());
        $output->writeln('Email: ' . $user->getEmail());
        $output->writeln('First Name: ' . $user->getFirstName());
        $output->writeln('Last Name: ' . $user->getLastName());

        $output->writeln('========== SCOPES ==========');

        /** @var Scope $scope */
        foreach ($user->getScopes() as $scope) {
            $output->writeln($scope->getIdentifier());
        }

        $output->writeln('========== CLIENTS ==========');

        /** @var Client $client */
        foreach ($user->getClients() as $client) {
            $output->writeln($client->getIdentifier());
        }

        $continueQuestion = new ConfirmationQuestion('DO YOU REALLY WANT TO DELETE THIS USER? [n] ', false);

        if (!$input->isInteractive() || ($input->isInteractive() && $helper->ask($input, $output, $continueQuestion))) {
            $this->deleteUser($user);
            $output->writeln('User removed.');
        }
    }

    /**
     * @param User $user
     */
    private function deleteUser(User $user)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get('em');
        /** @var AccessTokenRepository $accessTokenRepository */
        $accessTokenRepository = $this->container->get('access_token_repository');
        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = $this->container->get('refresh_token_repository');

        $accessTokens = $accessTokenRepository->findBy(['userIdentifier' => $user->getIdentifier()]);
        $refreshTokens = $refreshTokenRepository->findBy(['userIdentifier' => $user->getIdentifier()]);

        foreach ($refreshTokens as $refreshToken) {
            $entityManager->remove($refreshToken);
        }

        foreach ($accessTokens as $accessToken) {
            $entityManager->remove($accessToken);
        }

        $entityManager->remove($user);
        $entityManager->flush();
    }
}
