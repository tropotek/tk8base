<?php
namespace App;

use App\Db\User;
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
            $selected = $this->getRegistry()->get('minton.template', 'sn-admin');
            if (User::getAuthUser()->template) {
                $selected = User::getAuthUser()->template;
            }
            $templatePath = sprintf('/html/minton/%s.html', preg_replace('|[^0-9a-z_-]|i', '', $selected));
            $templatePath = Config::makePath($templatePath);
            if (!is_file($templatePath)) {
                $templatePath = Config::makePath('/html/minton/sn-admin.html');
            }
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