<?php
namespace App\Component;

use Bs\Auth;
use Dom\Template;
use Tk\System;
use Tk\Uri;

class Test extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{
    const string CONTAINER_ID = 'container-test';
    protected array $hxEvents = [];


    public function doDefault(): ?Template
    {
        if (!Auth::getAuthUser()) return null;


        return $this->show();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setText('title', 'Testing Test');
        $template->setAttr('container', 'id', $this->getContainerId());

        $template->setText('time', time());

        // Send HX event headers
        if (count($this->hxEvents)) {
            header(sprintf('HX-Trigger: %s', json_encode($this->hxEvents)));
        }

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div id="" var="container">
  <div class="card mb-3">
    <div class="card-header"><i class="fas fa-cogs"></i> <span var="title"></span></div>
    <div class="card-body" var="content" id="test-content">
        <p>
            <button type="button" class="btn btn-outline-primary" var="btn1"
                hx-get="/component/test"
                hx-target="#test-content"
                hx-select="#test-content"
                hx-swap="outerHTML">Reload</button>
        </p>
        <p>Test Component ... [<span var="time"></span>]</p>
    </div>
  </div>
<script>
    jQuery(function($) {
        const container = '#{$this->getContainerId()}';
        
        $('.card-body', container).append('<p>Javascript Text</p>');
        
    });
</script>
<style>
.test-com .card-header {
    background-color: #1abc9c;
}
</style>
</div>
HTML;
        return Template::load($html);
    }

    public function getContainerId(): string
    {
        return self::CONTAINER_ID;
    }

}
