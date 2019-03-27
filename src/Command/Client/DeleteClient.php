<?php

namespace AuthenticationServer\Command\Client;

use AuthenticationServer\Command\Command;
use AuthenticationServer\Entity\Client;
use AuthenticationServer\Entity\Scope;
use AuthenticationServer\Entity\User;
use AuthenticationServer\Repository\AccessTokenRepository;
use AuthenticationServer\Repository\ClientRepository;
use AuthenticationServer\Repository\RefreshTokenRepository;
use AuthenticationServer\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class DeleteClient extends Command
{
    protected function configure()
    {
        $this->setName('client:delete')
            ->addArgument('identifier', InputArgument::OPTIONAL, 'Client identifier')
            ->setHelp('Delete client.')
            ->setDescription('Delete client.')
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

        $clientIdentifier = $input->getArgument('identifier');

        /** @var ClientRepository $clientRepository */
        $clientRepository = $this->container->get('client_repository');

        if (null === $clientIdentifier) {
            $clientIdentifierQuestion = new Question('What client identifier ? : ', null);
            $clientIdentifier = $helper->ask($input, $output, $clientIdentifierQuestion);
        }

        /** @var Client $client **/
        $client = $clientRepository->findOneBy(['identifier' => $clientIdentifier]);

        if (null === $client) {
            throw new EntityNotFoundException('Client "' . $clientIdentifier . '" not found');
        }

        $output->writeln('========== INFO ==========');

        $output->writeln('Identifier: ' . $client->getIdentifier());
        $output->writeln('Name: ' . $client->getName());
        $output->writeln('Secret: ' . $client->getSecret());

        $output->writeln('========== USERS ==========');

        /** @var User $user */
        foreach ($client->getUsers() as $user) {
            $output->writeln($user->getIdentifier());
        }

        $continueQuestion = new ConfirmationQuestion('DO YOU REALLY WANT TO DELETE THIS CLIENT? [n] ', false);

        if (!$input->isInteractive() || ($input->isInteractive() && $helper->ask($input, $output, $continueQuestion))) {
            /** @var EntityManager $entityManager */
            $entityManager = $this->container->get('em');

            $entityManager->remove($client);
            $entityManager->flush();

            $output->writeln('Client "' . $clientIdentifier . '" removed.');
        }
    }
}
