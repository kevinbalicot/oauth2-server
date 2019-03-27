<?php

namespace AuthenticationServer\Command\Scope;

use AuthenticationServer\Command\Command;
use AuthenticationServer\Entity\Scope;
use AuthenticationServer\Repository\ScopeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListScopes extends Command
{
    protected function configure()
    {
        $this->setName('scope:list')
            ->setHelp('List scopes.')
            ->setDescription('List scopes.')
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
        $this->container->get('em')->flush();

        /** @var ScopeRepository $scopeRepository */
        $scopeRepository = $this->container->get('scope_repository');

        /** @var ArrayCollection $scopes */
        $scopes = $scopeRepository->findAll();

        if (count($scopes) === 0) {
            throw new EntityNotFoundException('No scopes');
        }

        $output->writeln('========== SCOPES ==========');

        /** @var Scope $scope */
        foreach ($scopes as $scope) {
            $output->writeln($scope->getIdentifier());
        }
    }
}
