<?php

namespace Elao\ElaoCommandMigration\Command;

use Elao\ElaoCommandMigration\Process\Exception\ConnectionException;
use Elao\ElaoCommandMigration\Process\Run;
use Elao\ElaoCommandMigration\Process\ResultView;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RunCommand extends Command
{
    public function configure(): void
    {
        $this
            ->setName('elao:command-migration:run')
            ->setDescription('Run command migrations')
            ->addArgument('path', InputArgument::REQUIRED, 'Migration configuration path')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->title('Starting command migrations');

        $run = new Run($input->getArgument('path'));
        $executedCommand = 0;

        try {
            /** @var ResultView $resultView */
            foreach($run() as $resultView) {
                if ($resultView->isSuccessful()) {
                    $symfonyStyle->success($resultView->getCommand());
                    ++$executedCommand;
                } else {
                    $symfonyStyle->error(
                        sprintf(
                            'Error with command "%s" with output: "%s"',
                            $resultView->getCommand(),
                            $resultView->getOutput()
                        )
                    );
                }
            }

            $endingOutput = '';
            if (0 === $executedCommand) {
                $endingOutput = 'No command executed';
            } elseif (1 === $executedCommand) {
                $endingOutput = '%d command successfully executed';
            } else {
                $endingOutput = '%d command(s) successfully executed';
            }

            $symfonyStyle->text(sprintf($endingOutput, $executedCommand));
        } catch (ConnectionException $exception) {
            $symfonyStyle->error($exception->getMessage());
        }
    }
}