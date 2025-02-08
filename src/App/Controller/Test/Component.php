<?php
namespace App\Controller\Test;

use App\Component\Test;
use Bs\Auth;
use Bs\Mvc\ControllerAdmin;
use Dom\Template;
use Tk\Alert;
use Tk\Uri;

class Component extends ControllerAdmin
{

    public function doDefault(): void
    {
        $this->getPage()->setTitle('Component Test');
        $this->setUserAccess();

    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setText('title', $this->getPage()->getTitle());

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div class="row">
    <div class="col-8">
        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-cogs"></i> <span var="title"></span></div>
            <div class="card-body" var="content">
                <p>Main Content</p>
            </div>
        </div>
    </div>
    <div class="col-4" var="components">
        <div hx-get="/component/test" hx-trigger="load" hx-swap="outerHTML" var="component">
          <p class="text-center mt-4"><i class="fa fa-fw fa-spin fa-spinner fa-3x"></i><br>Loading...</p>
        </div>
    </div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}