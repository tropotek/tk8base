<?php
namespace App;

use App\Db\User;
use Bs\Mvc\PageDomInterface;
use Bs\Registry;
use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tk\Config;
use Tk\Path;

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
            $selected = Registry::getValue('minton.template', 'sn-admin');
            if (User::getAuthUser()->template) {
                $selected = User::getAuthUser()->template;
            }
            $cleanName = preg_replace('|[^0-9a-z_-]|i', '', $selected);
            $tplPath = Path::createTemplatePath(sprintf('/minton/%s.html', $cleanName));

            if (!is_file($tplPath)) {
                $tplPath = Path::createTemplatePath('/minton/sn-admin.html');
            }
            $templatePath = $tplPath->toString();
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