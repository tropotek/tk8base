<?php
namespace App\Console;

use App\Db\User;
use App\External\Moodle;
use App\External\OpenAi;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Bs\Console\Console;
use Tk\Cache\Cache;
use Tk\Config;
use Tk\Encrypt;
use Tk\Uri;

class Test extends Console
{

    protected function configure()
    {
        $this->setName('test')
            ->setDescription('This is a test script');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!Config::isDev()) {
            $this->writeError('Error: Only run this command in a debug environment.');
            return self::FAILURE;
        }


        $moodle = Moodle::create();

        $result = $moodle->getSchedule(4);
        vd($result);



        return self::SUCCESS;
    }



}
