<?php
namespace App\Console;

use App\External\Moodle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Bs\Console\Console;
use Tk\Config;

class Test extends Console
{

    protected function configure()
    {
        $this->setName('test')
            ->setDescription('This is a test script');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!Config::isDebug()) {
            $this->writeError('Error: Only run this command in a debug environment.');
            return self::FAILURE;
        }

        $moodle = Moodle::create();
        //$result = $moodle->getCourseCategories();
        //$result = $moodle->getMoodleSiteInfo();
        //$result = $moodle->getAllUsers();

        //$result = $moodle->getCategoryByIdnumber('CAT 1');
        //$result = $moodle->setCategoryVisible(1, false);
        //$result = $moodle->setCategoryVisible(1, true);
        //$result = $moodle->get_curriculum_managers(1);

        $start = (new \DateTime())->add(new \DateInterval('P1W'));
        $end = (new \DateTime())->add(new \DateInterval('P2M'));
        vd($start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'));
        //$result = $moodle->set_course_dates(2, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'));
        //$result = $moodle->set_course_dates(2, $start->format('Y-m-d H:i:s'));

        //$result = $moodle->get_all_recent_quizzes(90);

        vd($result);
        //vd(date_default_timezone_get());




//        $gt = GuestToken::create([
//            Uri::create('/login')->getPath()
//        ],
//        [
//            'hash' => md5('test'),
//            'fooId' => 22,
//            'text' => 'Just a blank message'
//        ], 15);

        return self::SUCCESS;
    }



}
