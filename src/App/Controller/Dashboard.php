<?php
namespace App\Controller;

use App\Db\Notify;
use App\Db\User;
use Bs\Auth;
use Bs\Mvc\ControllerAdmin;
use Bs\Ui\Breadcrumbs;
use Dom\Template;
use Tk\Alert;
use Tk\Date;
use Tk\Exception;
use Tk\Uri;

class Dashboard extends ControllerAdmin
{

    public function doDefault(): void
    {
        Breadcrumbs::reset();
        $this->getPage()->setTitle('User Dashboard', 'fas fa-cogs');

        // Check for a logged-in user, redirect if none
        $this->setUserAccess();

        if (isset($_GET['e'])){
            throw new Exception('This is a test exception...', 500);
        }
        if (isset($_GET['a'])) {
            Alert::addSuccess('This is a success alert', '', 'fa-solid fa-circle-check');
            Alert::addInfo('This is a info alert', '', 'fa-solid fa-circle-info');
            Alert::addWarning('This is a warning alert', '', 'fa-solid fa-triangle-exclamation');
            Alert::addError('This is a error alert', '', 'fa-solid fa-circle-exclamation');
            Uri::create()->reset()->redirect();
        }
        if (isset($_GET['n'])) {
            Notify::create(
                User::getAuthUser()->userId,
                'Test notify message',
                Date::create()->format(Date::FORMAT_ISO_DATETIME) . ' - This is a test with some HTML',
                Uri::create()->reset()->toRelativeString(),
                User::getAuthUser()->getImageUrl()->toRelativeString(),
                5
            );
            Alert::addInfo("Notification Message Set");
            Uri::create()->reset()->redirect();
        }

    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->appendText('title', $this->getPage()->getTitle());
        $template->addCss('icon', $this->getPage()->getIcon());


        $user = User::getAuthUser();
        $template->setAttr('img', 'src', $user->getImageUrl());
        $template->setText('username', $user->username);
        $template->setText('name', $user->nameShort);

        $template->setAttr('eurl', 'href', Uri::create()->set('e', true));
        $template->setAttr('aurl', 'href', Uri::create()->set('a', true));
        $template->setAttr('nurl', 'href', Uri::create()->set('n', true));

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div>

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="widget-rounded-circle card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-lg">
                                <img src="#" class="img-fluid rounded-circle img-thumbnail" alt="user" var="img">
                            </div>
                        </div>

                        <div class="col">
                            <h5 class="mt-0 mb-1">Chadengle</h5>
                            <p class="text-muted mb-2 font-13 text-truncate">user@example.com.au</p>
                            <small class="text-warning"><b>Admin</b></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="widget-rounded-circle card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-lg">
                                <img src="#" class="img-fluid rounded-circle img-thumbnail" alt="user" var="img">
                            </div>
                        </div>
                        <div class="col">
                            <h5 class="mt-0 mb-1">Tomaslau</h5>
                            <p class="text-muted mb-2 font-13 text-truncate">user@example.com.au</p>
                            <small class="text-success"><b>User</b></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="widget-rounded-circle card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-lg">
                                <img src="#" class="img-fluid rounded-circle img-thumbnail" alt="user" var="img">
                            </div>
                        </div>
                        <div class="col">
                            <h5 class="mt-0 mb-1">Stillnotdavid</h5>
                            <p class="text-muted mb-2 font-13 text-truncate">user@example.com.au</p>
                            <small class="text-pink"><b>Admin</b></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="widget-rounded-circle card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-lg">
                                <img src="#" class="img-fluid rounded-circle img-thumbnail" alt="user" var="img">
                            </div>
                        </div>
                        <div class="col">
                            <h5 class="mt-0 mb-1">Arashasghari</h5>
                            <p class="text-muted mb-2 font-13 text-truncate">user@example.com.au</p>
                            <small class="text-info"><b>User</b></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->


    <div class="card mb-3">
        <div class="card-header"><i var="icon"></i> <span var="title"></span></div>
        <div class="card-body" var="content">

            <p>
                This is an example of the logged-in user template. This admin template can be customized to meet your
                business requirements.
            </p>
            <p>
                We have added a 'Teams' example accessible via the menu. This example highlights the listing and edit pages
                that will allow you to manage your datasets.
            </p>
            <p>
                <a href="/contact" target="_blank">Contact Us</a> today and enquire how we
                can create your next online buisines solution to centralise a streamline your business data and workflows.
            </p>
            <p>&nbsp;</p>

            <hr>

            <p>User Details:</p>
            <p>
                <b>Username:</b> <span var="username"></span><br>
                <b>Name:</b> <span var="name"></span>
            </p>

            <p>Test Exception Error:</p>
            <p>
                <a href="#?e" class="btn btn-outline-dark" var="eurl">Test Exception</a>
            </p>

            <p>Test Confirmation dialog:</p>
            <p>
                <a href="/dashboard" class="btn btn-outline-dark" title="Confirmation Dialog Test" data-confirm="<p><em>Are you sure?</em></p>" data-cancel="Nuh!!">Confirm Test</a>
            </p>

            <p>Test Alert Notifications:</p>
            <p>
                <a href="#?a" class="btn btn-outline-dark" var="aurl">Alert Test</a>
            </p>

            <p>Test User Notification Messages:</p>
            <p>
                <a href="#?n" class="btn btn-outline-dark" var="nurl">Test Notify Message</a>
            </p>

            <p>&nbsp;</p>

        </div>
    </div>

</div>
HTML;
        return $this->loadTemplate($html);
    }

}


