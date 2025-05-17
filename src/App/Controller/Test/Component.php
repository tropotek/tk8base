<?php
namespace App\Controller\Test;

use Bs\Mvc\ControllerAdmin;
use Dom\Template;

class Component extends ControllerAdmin
{

    public function doDefault(): void
    {
        $this->getPage()->setTitle('Component Test', 'fa fa-cogs');
        $this->setUserAccess();

    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->appendText('title', $this->getPage()->getTitle());
        $template->addCss('icon', $this->getPage()->getIcon());

        // (test) comment out, for primary panel full width
        $template->setVisible('components');

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div class="row">
    <div class="col">
        <div class="card mb-3">
            <div class="card-header"><i var="icon"></i> <span var="title"></span></div>
            <div class="card-body" var="content">
                <p>Main Content</p>
            </div>
        </div>
    </div>
    <div class="col-4" choice="components">
        <div hx-get="/component/test" hx-trigger="load" hx-swap="outerHTML" var="component">
          <p class="text-center mt-4"><i class="fa fa-fw fa-spin fa-spinner fa-3x"></i><br>Loading...</p>
        </div>
    </div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}