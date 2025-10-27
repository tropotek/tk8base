<?php

namespace App\Ui;

use App\Db\User;
use Bs\Menu\Item;
use Tk\Config;
use Tk\Uri;

class Nav
{

    public static function getNavMenu(): Item
    {
        $menu = new Item();
        $user = User::getAuthUser();

        if (!$user) {
            $menu->addLink('Login', Uri::create('/login'), 'ri-login-box-line');
            return $menu;
        }

        $menu->addLink('Dashboard', Uri::create('/dashboard'), 'ri-dashboard-line');
        $menu->addLink('Site Settings', Uri::create('/settings'), 'ri-settings-2-fill', $user->hasPermission(User::PERM_SYSADMIN));
        $menu->addLink('Preview Site', Uri::create('/home'), 'fas fa-home', true,
            [
                'attrs' => ['target' => '_blank']
            ]
        );

        $menu->addHeader('System');
        $menu->addLink('Teams', Uri::create('/teamManager'), 'fas fa-layer-group');
        $menu->addLink('Staff', Uri::create('/user/staffManager'), 'fas fa-users', $user->hasPermission(User::PERM_SYSADMIN));
        $menu->addLink('Members', Uri::create('/user/memberManager'), 'fas fa-users', $user->hasPermission(User::CHANGE_USERS));

        $menu->addHeader('Help');
        $menu->addLink('Contact Us', Uri::create('/contact'), 'bx bx-mail-send');

        return $menu;
    }

    public static function getProfileMenu(): Item
    {
        $menu = new Item('profile', 1);
        $user = User::getAuthUser();

        if (!$user) {
            $menu->addLink('Login', Uri::create('/login'), 'ri-login-box-line');
            return $menu;
        }

        $menu->addLink('My Account', Uri::create('/profile'), 'fe-user', true);
        $menu->addLink('Settings', Uri::create('/settings'), 'fe-settings', $user->hasPermission(User::PERM_SYSADMIN));

        $visible = (Config::isDev() && $user->hasPermission(User::PERM_ADMIN));
        $menu->addSeparator($visible);
        $menu->addLink('PHP Info', Uri::create('/info'), 'ri-information-line', $visible);
        $menu->addLink('Tail Log', Uri::create('/tailLog'), 'ri-terminal-box-fill', $visible);
        $menu->addLink('Inline Image', Uri::create('/util/inlineImage'), 'fas fa-image', $visible);
        $menu->addLink('DB Search', Uri::create('/util/dbSearch'), 'fas fa-database', $visible);
        $menu->addLink('DB Size', Uri::create('/util/dbSize'), 'fas fa-database text-info', $visible);

        $menu->addSeparator(true);
        $menu->addLink('Customizer', null, 'ri-palette-line', true,
            [
                'attrs' => [
                    'data-bs-toggle' => 'offcanvas',
                    'href' => '#theme-settings-offcanvas',
                ]
            ]
        );
        $menu->addLink('About', null, 'fa fa-info-circle', true,
            [
                'attrs' => [
                    'hx-get' => '/component/aboutDialog',
                    'hx-trigger' => 'click queue:none',
                    'hx-target' => 'body',
                    'hx-swap' => 'beforeend',
                ]
            ]
        );
        $menu->addLink('Logout', Uri::create('/logout'), 'fe-log-out', true,
            [
                'css' => [
                    'btn-logout',
                ],
                'attrs' => [
                    'hx-get' => '/component/logoutDialog',
                    'hx-trigger' => 'click queue:none',
                    'hx-target' => 'body',
                    'hx-swap' => 'beforeend',
                ]
            ]
        );

        return $menu;
    }

}