<?php

namespace AuthenticationServer\Command\Scope;

use AuthenticationServer\Command\Command;
use AuthenticationServer\Repository\ScopeRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateScope extends Command
{
    protected function configure()
    {
        $this->setName('scope:create')
            ->addArgument('identifier', InputArgument::OPTIONAL, 'Scope identifier')
            ->setHelp('Create a new scope.')
            ->setDescription('Create a new scope.')
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
        $this->container->get('em')->flush();

        $identifier = $input->getArgument('identifier');

        /** @var ScopeRepository $scopeRepository */
        $scopeRepository = $this->container->get('scope_repository');

        if (null === $identifier) {
            $identifierQuestion = new Question('What scope identifier ? : ', null);
            $identifier = $helper->ask($input, $output, $identifierQuestion);
        }

        $scope = $scopeRepository->find($identifier);

        if (null != $scope) {
            throw new \Exception('Scope "' . $identifier . '" already exists');
        }

        if (!preg_match('/^[a-zA-Z0-9_:]+$/', $identifier)) {
            throw new \Exception('Identifier has to match ^[a-zA-Z0-9_:]+$');
        }

        $scope = $scopeRepository->create($identifier);

        $output->writeln('========== SCOPE ==========');
        $output->writeln('Identifier: ' . $scope->getIdentifier());
    }
}
