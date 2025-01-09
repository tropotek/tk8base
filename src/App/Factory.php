<?php
namespace App;

use Bs\Mvc\PageDomInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tk\Config;

class Factory extends \Bs\Factory
{

    public function initEventDispatcher(): ?EventDispatcher
    {
        if ($this->getEventDispatcher()) {
            new Dispatch($this->getEventDispatcher());
        }
        return $this->getEventDispatcher();
    }

    public function createDomPage(string $templatePath = ''): PageDomInterface
    {
        // So we can change the mintion template from the settings page
        if (str_contains($templatePath, '/minton/')) {
            $templatePath = $this->getRegistry()->get('minton.template');
            if (empty($templatePath)) {
                $templatePath = '/html/minton/sn-admin.html';
            }
            $templatePath = Config::makePath($templatePath);
        }
        return new Page($templatePath);
    }

    public function getConsole(): Application
    {
        if (!$this->has('console')) {
            $app = parent::getConsole();

            $app->add(new \App\Console\Cron());
            if (Config::isDev()) {
                $app->add(new \App\Console\Test());
            }
        }
        return $this->get('console');
    }

}