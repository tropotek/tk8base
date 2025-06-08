<?php

namespace App\Ui;

use App\Db\User;
use Dom\Template;
use Tk\Config;
use Tk\Ui\Traits\AttributesTrait;

class Nav
{
    use AttributesTrait;

    protected function getNavList(): array
    {
        return [
            'Dashboard' => [
                'icon' => 'ri-dashboard-line',
                'visible' => fn($i) => (bool)$this->getUser(),
                'url' => '/dashboard',
            ],

            'Teams' => [
                'icon' => 'fas fa-users',
                'url' => '/teamManager',
                'visible' => fn($i) => (bool)$this->getUser(),
            ],

            'Admin' => [
                'icon' => 'ri-settings-2-line',
                'visible' => fn($i) => $this->getUser()?->hasPermission(User::PERM_SYSADMIN),
                'Site Settings' => [
                    'icon' => 'ri-settings-2-fill',
                    'visible' => fn($i) => $this->getUser()?->hasPermission(User::PERM_SYSADMIN),
                    'url' => '/settings',
                ],
                'Staff' => [
                    'icon' => 'fas fa-users',
                    'visible' => fn($i) => $this->getUser()?->hasPermission(User::PERM_SYSADMIN),
                    'url' => '/user/staffManager',
                ],
                'Members' => [
                    'icon' => 'fas fa-users',
                    'visible' => fn($i) => $this->getUser()?->hasPermission(User::PERM_SYSADMIN),
                    'url' => '/user/memberManager',
                ],
            ],

            'Dev' => [
                'icon' => 'ri-bug-line',
                'visible' => fn($i) => Config::isDev() && $this->getUser()->isStaff(),
                'PHP Info' => [
                    'icon' => 'ri-information-line',
                    'url' => '/info',
                ],
                'Tail Log' => [
                    'icon' => 'ri-terminal-box-fill',
                    'url' => '/tailLog',
                ],
                'Inline Image' => [
                    'icon' => 'fas fa-image',
                    'url' => '/util/inlineImage',
                ],
                'DB Search' => [
                    'icon' => 'fas fa-database',
                    'url' => '/util/dbSearch',
                ],
            ],

        ];
    }

    public function getProfileNav(): Template
    {
        $html = <<<HTML
<div>
  <a href="/profile" class="dropdown-item notify-item">
    <i class="fe-user me-1"></i>
    <span>My Account</span>
  </a>
  <a href="/settings" class="dropdown-item notify-item" choice="sysadmin">
    <i class="fe-settings me-1"></i>
    <span>Settings</span>
  </a>
  <div class="dropdown-divider"></div>
  <a class="dropdown-item notify-item" data-bs-toggle="offcanvas" href="#theme-settings-offcanvas" choice="sysadmin">
    <i class="ri-palette-line me-1"></i>
    <span>Customizer</span>
  </a>
  <a href="#" class="dropdown-item notify-item" data-bs-toggle="modal" data-bs-target="#about-modal">
    <i class="fa fa-info-circle me-1"></i>
    <span>About</span>
  </a>
  <a href="/logout" class="dropdown-item notify-item btn-logout">
    <i class="fe-log-out me-1"></i>
    <span>Logout</span>
  </a>
</div>
HTML;
        $template = Template::load($html);

        if ($this->getUser()) {
            $template->setVisible('sysadmin', $this->getUser()->hasPermission(User::PERM_SYSADMIN));
        }

        return $template;
    }

    public function getTopNav(): string
    {
        $nav = sprintf('<ul class="navbar-nav %s" %s>', $this->getCssString(), $this->getAttrString());
        foreach ($this->getNavList() as $name => $item) {
            if (!$this->isVisible($item)) continue;
            if (empty($item['url'])) {
                if ($this->hasItems($item)) { // is dropdown item
                    $nav .= $this->makeTopDropdown($name, $item['icon'] ?? '', $item);
                } else {    // is title item
                    //$nav .= sprintf('<li class="menu-title">%s</li>', $name);
                }
            } else {

                $nav .= '<li class="nav-item">';
                $badge = '';
                if (!empty($item['badge'])) {
                    $badge = $item['badge']();
                }
                $ico = '';
                if ($item['icon'] ?? false) {
                    $ico = sprintf('<i class="%s me-1"></i>', $item['icon']);
                }
                $nav .= sprintf('<a href="%s" class="nav-link">%s %s %s</a>', $item['url'], $ico, $badge, $name);
                $nav .= '</li>';
            }
        }
        $nav .= '</ul>';
        return $nav;
    }

    protected function makeTopDropdown(string $name, string $icon, array $items): string
    {
        unset($items['icon']);
        unset($items['visible']);
        $items = array_filter($items, fn($itm) => $this->isVisible($itm));
        if (!count($items)) return '';
        $ico = '';
        if ($icon) {
            $ico = sprintf('<i class="%s"></i>', $icon);
        }
        $nav  = '<li class="nav-item dropdown">';
        $nav .= sprintf('<a class="nav-link dropdown-toggle arrow-none" href="javascript:;" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">%s %s <div class="arrow-down"></div></a>', $ico, $name);
        $nav .= '<div class="dropdown-menu">';
        foreach ($items as $sub_name => $item) {
            if (!$this->isVisible($item)) continue;
            if (empty($item['url'])) {
                if ($this->hasItems($item)) { // is dropdown item
                    $nav .= $this->makeSideDropdown($name, $item['icon'] ?? '', $item);
                } else {    // is title item
                    $nav .= sprintf('<li class="menu-title">%s</li>', $name);
                }
            } else {
                $ico = '';
                if ($item['icon'] ?? false) {
                    $ico = sprintf('<i class="%s me-1"></i>', $item['icon']);
                }
                $nav .= sprintf('<a class="dropdown-item" href="%s">%s %s</a>', $item['url'], $ico, $sub_name);
            }
        }
        $nav .= '</div></li>';
        return $nav;
    }

    protected function makeTopSubDropdown(string $name, string $icon, array $items): string
    {
        unset($items['icon']);
        unset($items['visible']);
        $items = array_filter($items, fn($itm) => $this->isVisible($itm));
        if (!count($items)) return '';
        $ico = '';
        if ($icon) {
            $ico = sprintf('<i class="%s"></i>', $icon);
        }
        $nav  = '<div class="dropdown">';
        $nav .= sprintf('<a class="dropdown-item dropdown-toggle arrow-none" href="javascript:;" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">%s %s <div class="arrow-down"></div></a>', $ico, $name);
        $nav .= '<div class="dropdown-menu">';
        foreach ($items as $sub_name => $item) {
            if (empty($item['url'])) {
                if ($this->hasItems($item)) { // is dropdown item
                    $nav .= $this->makeSideDropdown($name, $item['icon'] ?? '', $item);
                } else {    // is title item
                    $nav .= sprintf('<li class="menu-title">%s</li>', $name);
                }
            } else {
                $ico = '';
                if ($item['icon'] ?? false) {
                    $ico = sprintf('<i class="%s me-1"></i>', $item['icon']);
                }
                $nav .= sprintf('<a class="dropdown-item" href="%s">%s %s</a>', $item['url'], $ico, $sub_name);
            }
        }
        $nav .= '</div></div>';
        return $nav;
    }

    public function getSideNav(): string
    {
        $nav = sprintf('<ul class="%s" %s>', $this->getCssString(), $this->getAttrString());
        foreach ($this->getNavList() as $name => $item) {
            if (!$this->isVisible($item)) continue;
            if (empty($item['url'])) {
                if ($this->hasItems($item)) { // is dropdown item
                    $nav .= $this->makeSideDropdown($name, $item['icon'] ?? '', $item);
                } else {    // is title item
                    $nav .= sprintf('<li class="menu-title">%s</li>', $name);
                }
            } else {
                $nav .= '<li>';
                $badge = '';
                if (!empty($item['badge'])) {
                    $badge = $item['badge']();
                }
                $ico = '';
                if ($item['icon'] ?? false) {
                    $ico = sprintf('<i class="%s me-1"></i>', $item['icon']);
                }
                $nav .= sprintf('<a href="%s">%s %s <span>%s</span></a>', $item['url'], $ico, $badge, $name);
                $nav .= '</li>';
            }
        }
        $nav .= '</ul>';
        return $nav;
    }

    protected function makeSideDropdown(string $name, string $icon, array $items): string
    {
        unset($items['icon']);
        unset($items['visible']);
        $items = array_filter($items, fn($itm) => $this->isVisible($itm));
        if (!count($items)) return '';
        $ico = '';
        if ($icon) {
            $ico = sprintf('<i class="%s"></i>', $icon);
        }
        $id = strtolower(preg_replace('/[^a-z0-9_-]+/i', '', $name));
        $nav  = '<li>';
        $nav .= sprintf('<a href="#%s" class="waves-effect" data-bs-toggle="collapse" aria-expanded="false">%s <span>%s</span> <span class="menu-arrow"></span></a>', $id, $ico, $name);
        $nav .= sprintf('<div class="collapse" id="%s"><ul class="nav-second-level">', $id);
        foreach ($items as $sub_name => $item) {
            if (empty($item['url'])) {
                if ($this->hasItems($item)) { // is dropdown item
                    $nav .= $this->makeSideDropdown($sub_name, $item['icon'] ?? '', $item);
                } else {    // is title item
                    $nav .= sprintf('<li class="menu-title">%s</li>', $sub_name);
                }
            } else {
                $ico = '';
                if ($item['icon'] ?? false) {
                    $ico = sprintf('<i class="%s me-1"></i>', $item['icon']);
                }
                $nav .= sprintf('<li><a href="%s">%s <span>%s</span></a></li>', $item['url'], $ico, $sub_name);
            }
        }
        $nav .= '</ul></div></li>';
        return $nav;
    }

    protected function hasItems(array $item): bool
    {
        unset($item['icon']);
        unset($item['url']);
        unset($item['visible']);
        return count($item) > 0;
    }

    protected function isVisible(array $item): bool
    {
        if (is_bool($item['visible'] ?? '')) return $item['visible'];

        if (is_callable($item['visible'] ?? '')) {
            return $item['visible']($item) ?? false;
        }
        return true;
    }

    public function getUser(): ?User
    {
        return User::getAuthUser();
    }
}