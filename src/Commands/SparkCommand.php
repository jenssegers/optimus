<?php

namespace Jenssegers\Optimus\Commands;

use Jenssegers\Optimus\Energon;
use Jenssegers\Optimus\Exceptions\InvalidPrimeException;
use Jenssegers\Optimus\Optimus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SparkCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('spark')
            ->setDescription('Generate constructor values for your prime')
            ->addOption(
                'bits',
                'b',
                InputOption::VALUE_REQUIRED,
                'The number of bits to use in the obfuscation. E.g. 16 bits will produce numbers in the range 1 to 65536.',
                Optimus::DEFAULT_MAX_BITS
            )->addArgument(
               'prime',
               InputArgument::OPTIONAL,
               'Your prime number'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bits = $input->getOption('bits');

        try {
            list($prime, $inverse, $rand) = Energon::generate(
                $input->getArgument('prime'),
                $bits
            );
        } catch (InvalidPrimeException $e) {
            $output->writeln('<error>Invalid prime number</>');

            return;
        }

        $output->writeln('Prime: ' . $prime);
        $output->writeln('Inverse: ' . $inverse);
        $output->writeln('Random: ' . $rand);
        $output->writeln('Bits: ' . $bits);
        $output->writeln('');
        $output->writeln(
            sprintf(
                '    new Optimus(%s, %s, %s, %s);',
                $prime,
                $inverse,
                $rand,
                $bits
            )
        );
    }
}
