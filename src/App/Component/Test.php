<?php
namespace App\Component;

use Bs\Auth;
use Dom\Template;
use Tk\System;
use Tk\Uri;

class Test extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{

    public function doDefault(): string
    {
        if (!Auth::getAuthUser()) return '';
        return $this->show()->toString();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setText('title', 'Testing Test');

        if (!System::isHtmx()) {
            $template->appendHtml('content', '<p>From controller</p>');
        } else {
            $template->appendHtml('content', '<p>From HTMX</p>');
        }

        $url = Uri::create('/component/test');
        $template->setAttr('btn1', 'hx-get', $url);

        return $template;
    }


    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div id="test-container">
  <div class="card mb-3">
    <div class="card-header"><i class="fas fa-cogs"></i> <span var="title"></span></div>
    <div class="card-body" var="content">
        <p>
        <button type="button" class="btn btn-outline-primary" var="btn1"
            hx-get=""
            hx-target="#test-container"
            hx-swap="outerHTML">Reload</button>
        </p>
        <p>Test Component ...</p>
    </div>
  </div>
<script>
    jQuery(function($) {
        const container = '#test-container';
        
        $('.card-body', container).append('<p>Javascript Text</p>');
        
        // $(document).off('htmx:afterSettle.test-container')
        // .on('htmx:afterSettle.test-container', function(e) {
        //     if (!$(e.detail.target).is(container)) return;
        //     console.log(e);
        //     console.log(e.detail.target);
        // });
        
        
        // Jquery Namespaces see: https://api.jquery.com/on/#event-names
        
        // Add events
        // $('button', container).on('click', function(e) {
        //     console.log('Event1');
        // });
        // $('button', container).on('click.ns1', function(e) {
        //     console.log('Event2');
        // });
        // $('button', container).on('click.ns2', function(e) {
        //     console.log('Event3');
        // });
        // $('button', container).on('click.ns1.ns2', function(e) {
        //     console.log('Event4');
        // });
        
        // Trigger events 
        // Default = browser click event
        // $('button', container).trigger('click');         // triggers: All, Default
        //$('button', container).trigger('click.ns1');      // triggers: Event2, Event4, Default 
        //$('button', container).trigger('click.ns2');      // triggers: Event3, Event4, Default
        //$('button', container).trigger('click.ns1.ns2');  // triggers: Event4, Default
        
        // Event4 object: {e.type = "click", e.namespace = "ns1.ns2"}
        
       // $('button', container).off('click.ns1');      // remove Event2, Event4
       // $('button', container).trigger('click.ns1');  // triggers: Default only (note does not call Event1)
        
    
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

}
