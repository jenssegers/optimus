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
                'The number of bits used to obfuscate the integer. E.g. 16 bits will produce numbers in the range 0 to 65535.',
                Optimus::DEFAULT_BIT_LENGTH
            )->addArgument(
               'prime',
               InputArgument::OPTIONAL,
               'Your prime number'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bitLength = $input->getOption('bits');

        $minBitLength = 4;
        $maxBitLength = 62;

        if (!filter_var(
            $bitLength,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => $minBitLength, 'max_range' => $maxBitLength]]
        )) {
            throw new \InvalidArgumentException(
                "The bits option must be an integer between $minBitLength and $maxBitLength."
            );
        }

        try {
            list($prime, $inverse, $rand) = Energon::generate(
                $input->getArgument('prime'),
                $bitLength
            );
        } catch (InvalidPrimeException $e) {
            $output->writeln('<error>Invalid prime number</>');

            return 1;
        }

        $output->writeln('Prime: ' . $prime);
        $output->writeln('Inverse: ' . $inverse);
        $output->writeln('Random: ' . $rand);
        $output->writeln('Bit length: ' . $bitLength);
        $output->writeln('');
        $output->writeln(
            sprintf(
                '    new Optimus(%s, %s, %s, %s);',
                $prime,
                $inverse,
                $rand,
                $bitLength
            )
        );
    }
}
