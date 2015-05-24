<?php namespace Jenssegers\Optimus\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Jenssegers\Optimus\Optimus;
use phpseclib\Crypt\Random;
use phpseclib\Math\BigInteger;

class SparkCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('spark')
            ->setDescription('Generate constructor values for your prime')
            ->addArgument(
               'prime',
               InputArgument::REQUIRED,
               'Your prime number'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $prime = $input->getArgument('prime');

        // Calculate the inverse.
        $a = new BigInteger($prime);
        $b = new BigInteger(Optimus::MAX_INT + 1);

        if ( ! $inverse = $a->modInverse($b))
        {
            $output->writeln('<error>Invalid prime number</>');

            return;
        }

        $rand = hexdec(bin2hex(Random::string(4))) & Optimus::MAX_INT;

        $output->writeln('Prime: ' . $prime);
        $output->writeln('Inverse: ' . $inverse);
        $output->writeln('Random: ' . $rand);
        $output->writeln('');
        $output->writeln('    new Optimus(' . $prime . ', ' . $inverse . ', ' . $rand . ');');
    }
}
