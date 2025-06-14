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
        $menu->addLink('Preview Site', Uri::create('/home'), 'fas fa-home', true, ['attrs' => ['target' => '_blank']]);

        $menu->addSeparator();
        $menu->addLink('Teams', Uri::create('/teamManager'), 'fas fa-users');
        $menu->addSeparator();

        $admin = $menu->addSubmenu('Admin', 'ri-settings-2-line', $user->hasPermission(User::PERM_SYSADMIN));
        $admin->addLink('Staff', Uri::create('/user/staffManager'), 'fas fa-users', $user->hasPermission(User::PERM_MANAGE_STAFF));
        $admin->addLink('Members', Uri::create('/user/memberManager'), 'fas fa-users', $user->hasPermission(User::PERM_MANAGE_MEMBERS));

        $dev = $menu->addSubmenu('Dev', 'ri-bug-line', (Config::isDev() && $user->hasPermission(User::PERM_ADMIN)));
        $dev->addLink('PHP Info', Uri::create('/info'), 'ri-information-line');
        $dev->addLink('Tail Log', Uri::create('/tailLog'), 'ri-terminal-box-fill');
        $dev->addLink('Inline Image', Uri::create('/util/inlineImage'), 'fas fa-image');
        $dev->addLink('DB Search', Uri::create('/util/dbSearch'), 'fas fa-database');

        $menu->addHeader('Tropotek');
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
        $menu->addLink('Settings', Uri::create('/settings'), 'fe-settings', (bool)$user?->hasPermission(User::PERM_SYSADMIN));
        $menu->addSeparator(($user instanceof User));
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
                    'data-bs-toggle' => 'modal',
                    'data-bs-target' => '#about-modal',
                ]
            ]
        );
        $menu->addLink('Logout', Uri::create('/logout'), 'fe-log-out', true,
            [
                'css' => [
                    'btn-logout',
                ]
            ]
        );

        return $menu;
    }

}