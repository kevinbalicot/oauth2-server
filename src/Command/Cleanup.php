<?php

namespace AuthenticationServer\Command;

use AuthenticationServer\Entity\AccessToken;
use AuthenticationServer\Entity\RefreshToken;
use AuthenticationServer\Repository\AccessTokenRepository;
use AuthenticationServer\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Cleanup extends Command
{
    protected function configure()
    {
        $this->setName('server:clean')
            ->setHelp('Clean all tokens.')
            ->setDescription('Clean all tokens.')
        ;
    }

    /**
     * Create client
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get('em');
        /** @var AccessTokenRepository $accessTokenRepository */
        $accessTokenRepository = $this->container->get('access_token_repository');
        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = $this->container->get('refresh_token_repository');

        $accessTokens = $accessTokenRepository->findBy([], [], 1000);
        $output->writeln('Find "' . count($accessTokens) . '" access token(s)');

        $nbAT = 0;
        $nbRT = 0;
        /** @var AccessToken $accessToken */
        foreach ($accessTokens as $accessToken) {
            if ($accessToken->isExpired()) {
                $nbAT++;
                $accessToken->revoke();

                $refreshTokens = $refreshTokenRepository->findBy(['accessToken' => $accessToken]);

                /** @var RefreshToken $refreshToken */
                foreach ($refreshTokens as $refreshToken) {
                    if ($refreshToken instanceof RefreshToken) {
                        $nbRT++;
                        $refreshToken->revoke();
                        $entityManager->remove($refreshToken);
                    }
                }

                $entityManager->remove($accessToken);
                $entityManager->flush();
            }
        }

        $refreshTokens = $refreshTokenRepository->findAll();
        $output->writeln('Find "' . count($refreshTokens) . '" refresh token(s)');

        /** @var RefreshToken $refreshToken */
        foreach ($refreshTokens as $refreshToken) {
            if ($refreshToken->isExpired()) {
                $nbRT++;
                $refreshToken->revoke();
                $entityManager->remove($refreshToken);
            }

            $entityManager->flush();
        }

        $output->writeln($nbAT . ' access token(s) deleted');
        $output->writeln($nbRT . ' refresh token(s) deleted');
    }
}