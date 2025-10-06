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


        $idnumber = 'com-fram-1';
        $shortname = 'comp-course-1';
        //$userid = 81;        // completed
        $userid = 73;
        $userid = 11;
        $moodle = Moodle::create();

        $idnumber = 'HTMX001';
        $result = $moodle->getCategoryIdByIdnumber($idnumber);
        vd($result);

        //$result = $moodle->getAllUsers();
        //$result = $moodle->getAllCohorts();
        //$result = $moodle->get_course_competencies(1);
        //$result = $moodle->get_course_completion_status();
        //$result = $moodle->getRoles();

        //$result = $moodle->get_user_competencies($userid, $idnumber);
        //$result = $moodle->get_enrolled_competencies($shortname, $idnumber);
        //$result = $moodle->get_competency_frameworks();

//        $result = $moodle->get_competency_list($idnumber);
//        vd($result);

//        $key = hash('sha256', 'Tropotek_'.microtime());
//        //vd($key);
////        $key = md5('Tropotek_'.microtime());
////        vd($key);
//        $message = 'Hello World!';
//        $enc = new Encrypt($key);
//
//        $this->write('Basic Encrypt: ' . $message);
//        $code = $enc->encrypt($message);
//        $message = $enc->decrypt($code);
//        $this->write('  Result: ' . $message);
//
//        $message = 'Hello World!';
//        $this->write('Unsafe Encrypt: ' . $message);
//        $code = $enc->unsafeEncrypt($message);
//        $message = $enc->unsafeDecrypt($code);
//        $this->write('  Result: ' . $message);
//
//        $message = 'Hello World!';
//        $this->write('Safe Encrypt: ' . $message);
//        $code = $enc->safeEncrypt($message);
//        $message = $enc->safeDecrypt($code);
//        $this->write('  Result: ' . $message);


//        $openai = OpenAi::create('http://192.168.0.42:1234/v1', 'mistralai/devstral-small-2505');
//        $models = $openai->getModels();
//        if (!$models) {
//            $this->writeError('Error: No models found.');
//            return self::FAILURE;
//        }
//        $models = array_column($models, 'id');
//        $model = $models[0];
//
//        $q = 'What is the capital of Australia?';
//        $this->writeInfo($q);
//        $a = $openai->askQuestion($model, $q);
//        $this->write($a);



        return self::SUCCESS;
    }



}
