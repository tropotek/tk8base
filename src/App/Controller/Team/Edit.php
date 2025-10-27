<?php
namespace App\Controller\Team;

use App\Db\Team;
use App\Db\User;
use Bs\Mvc\ControllerAdmin;
use Bs\Mvc\Form;
use Dom\Template;
use Tk\Alert;
use Tk\Date;
use Tk\Exception;
use Tk\Form\Action\Link;
use Tk\Form\Action\SubmitExit;
use Tk\Form\Action\Submit;
use Tk\Form\Field\Checkbox;
use Tk\Form\Field\Textarea;
use Tk\Form\Field\Hidden;
use Tk\Form\Field\Input;
use Tk\Form\Field\Select;
use Tk\Uri;
use Bs\Ui\Breadcrumbs;

class Edit extends ControllerAdmin
{
    protected ?Team $team = null;
    protected ?Form  $form = null;


    public function doDefault(): void
    {
        $this->setUserAccess();
        $this->getPage()->setTitle('Edit Team', 'fa fa-edit');

        $teamId = intval($_GET['teamId'] ?? 0);

        $this->team = new Team();
        if ($teamId) {
            $this->team = Team::mustFind($teamId);
        }

        // Get the form template
        $this->form = new Form();

        $this->form->appendField(new Input('name'))
            ->addFieldCss('col-8');

        $this->form->appendField((new Checkbox('active'))
            ->addFieldCss('col-4')
            ->setSwitch(true));

        // Date and datetime tests
        $this->form->appendField((new Input('completedOn'))
            ->setType('date')
            ->setRequired()
            ->addFieldCss('col-6')
        );

        $this->form->appendField((new Input('confirmedAt'))
            ->setType('datetime-local')
            ->addFieldCss('col-6')
        );

        $this->form->appendField(new Textarea('description'));

        $this->form->appendField(new SubmitExit('save', [$this, 'onSubmit']));
        $this->form->appendField(new Link('cancel', Uri::create('/teamManager')));

        $load = $this->team->unmapForm();
        $this->form->setFieldValues($load);

        $this->form->execute($_POST);
    }

    public function onSubmit(Form $form, Submit $action): void
    {
        $values = $form->getFieldValues();
        $this->team->mapForm($values);

        $form->addFieldErrors($this->team->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = ($this->team->teamId == 0);
        $this->team->save();

        Alert::addSuccess('Form save successfully.');
        $action->setRedirect(Uri::create()->set('teamId', $this->team->teamId));
        if ($form->getTriggeredAction()->isExit()) {
            $action->setRedirect(Breadcrumbs::getBackUrl());
        }
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        $template->setText('title', $this->getPage()->getTitle());
        $template->addCss('icon', $this->getPage()->getIcon());

        if ($this->team->teamId) {
            $template->setText('modified', $this->team->modified->format(Date::FORMAT_LONG_DATETIME));
            $template->setText('created', $this->team->created->format(Date::FORMAT_LONG_DATETIME));
            $template->setVisible('edit');

            $url = Uri::create('/component/files', [
                'fkey' => Team::class,
                'fid' => $this->team->teamId,
                'dataPath' => $this->team->getDataPath(),
            ]);
            $template->setAttr('comp-files', 'hx-get', $url);

            $url = Uri::create('/component/teamMemberTable', ['teamId' => $this->team->teamId]);
            $template->setAttr('memberTable', 'hx-get', $url);
        }

        $template->appendTemplate('content', $this->form->show());

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div class="row">
    <div class="col">
        <div class="card mb-3">
            <div class="card-header">
                <i var="icon"></i> <span var="title"></span>
                <div class="info-dropdown dropdown" title="Details" choice="edit">
                    <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false"><i class="mdi mdi-dots-vertical"></i></a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <p class="dropdown-item"><span class="d-inline-block">Modified:</span> <span var="modified">...</span></p>
                        <p class="dropdown-item"><span class="d-inline-block">Created:</span> <span var="created">...</span></p>
                    </div>
                </div>
            </div>
            <div class="card-body" var="content"></div>
        </div>
    </div>
    <div class="col-4" choice="edit">

        <div class="card card-edit mb-3">
          <div class="card-header"><i class="fas fa-sticky-note"></i> Team Files</div>
          <div class="card-body">
            <div hx-get="/component/files" hx-trigger="load" hx-swap="outerHTML" var="comp-files">
              <p class="text-center mt-4"><i class="fa fa-fw fa-spin fa-spinner fa-3x"></i><br>Loading...</p>
            </div>
          </div>
        </div>

        <div hx-get="/component/teamMemberTable" hx-trigger="load" hx-swap="outerHTML" var="memberTable">
          <p class="text-center mt-4"><i class="fa fa-fw fa-spin fa-spinner fa-3x"></i><br>Loading...</p>
        </div>

    </div>
</div>
HTML;
        return Template::load($html);
    }

}