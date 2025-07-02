<?php
namespace App\Controller;

use Bs\Auth;
use Bs\Mvc\ControllerPublic;
use Bs\Ui\Breadcrumbs;
use Dom\Template;
use Tk\Alert;
use Tk\Exception;
use Tk\Uri;

class Home extends ControllerPublic
{

    public function doDefault(): void
    {
        Breadcrumbs::reset();
        $this->getPage()->setTitle('Home', 'fas fa-home');

        if (isset($_GET['e'])){
            throw new Exception('This is a test exception...', 500);
        }
        if (isset($_GET['a'])) {
            Alert::addSuccess('This is a success alert', '', 'fa-solid fa-circle-check');
            Alert::addInfo('This is a info alert', '', 'fa-solid fa-circle-info');
            Alert::addWarning('This is a warning alert', '', 'fa-solid fa-triangle-exclamation');
            Alert::addError('This is a error alert', '', 'fa-solid fa-circle-exclamation');
            Uri::create()->remove('a')->redirect();
        }

    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->appendText('title', $this->getPage()->getTitle());
        $template->addCss('icon', $this->getPage()->getIcon());


        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div>
    <div class="card mb-3">
        <div class="card-header"><i var="icon"></i> <span var="title"></span></div>
        <div class="card-body" var="content">
            <div>
                <h3>Welcome To Our Default Public Template</h3>
                <p>This site is currently using a bare-bones Bootstrap 5 template for the public facing pages.</p>
                <p>
                    We recommend using Bootstrap based templates. To find a Bootstrap template that would suite you
                    business needs check out the following template sites:
                </p>
                <ul>
                    <li><a href="https://wrapbootstrap.com/" target="_blank">{w}WrapBootstrap</a></li>
                    <li><a href="https://startbootstrap.com/templates" target="_blank">Start Bootstrap</a></li>
                </ul>
                <p>We can integrate most templates for public users on request.</p>
                <hr>
                <h3>Demo</h3>
                <p>
                    <a href="/contact" target="_blank">Contact Us</a> today for a demo.<br>
                    See the full features of this site, and how your business could benefit with one of our online management solutions.
                </p>

            </div>
        </div>
    </div>

</div>
HTML;
        return $this->loadTemplate($html);
    }

}


