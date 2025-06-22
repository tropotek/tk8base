<?php
namespace App;

use App\Db\User;
use Bs\Auth;
use Bs\Db\UserInterface;
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
        // So we can change the minton template from the settings page
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

    /**
     *
     */
    public function createNewUser(string $username, string $email, string $password, int $perms = 0, string $type = 'staff'): ?UserInterface
    {
        $user = new User();
        $user->givenName = ucfirst($username);
        $user->type = $type;
        $user->country = 'AU';
        $user->save();

        $auth = Auth::create($user);
        $auth->username = $username;
        $auth->email = $email;
        $auth->permissions = $perms;
        $auth->password = Auth::hashPassword($password);
        $auth->save();

        return $user;
    }

}