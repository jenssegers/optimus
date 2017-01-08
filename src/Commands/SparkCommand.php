<?php

namespace Jenssegers\Optimus\Commands;

use Jenssegers\Optimus\Energon;
use Jenssegers\Optimus\Exceptions\InvalidPrimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SparkCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('spark')
            ->setDescription('Generate constructor values for your prime')
            ->addArgument(
               'prime',
               InputArgument::OPTIONAL,
               'Your prime number'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            list($prime, $inverse, $rand) = Energon::generate($input->getArgument('prime'));
        } catch (InvalidPrimeException $e) {
            $output->writeln('<error>Invalid prime number</>');

            return;
        }

        $output->writeln('Prime: ' . $prime);
        $output->writeln('Inverse: ' . $inverse);
        $output->writeln('Random: ' . $rand);
        $output->writeln('');
        $output->writeln('    new Optimus(' . $prime . ', ' . $inverse . ', ' . $rand . ');');
    }
}
