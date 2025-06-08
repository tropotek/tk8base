<?php
namespace App\Controller\Team;

use App\Db\Team;
use App\Db\User;
use Bs\Mvc\ControllerAdmin;
use Bs\Mvc\Table;
use Dom\Template;
use Tk\Form\Field\Input;
use Tk\Table\Cell;
use Tk\Table\Cell\RowSelect;
use Tk\Table\Action\Csv;
use Tk\Table\Action\Delete;
use Tk\Table\Action\Select;
use Tk\Uri;
use Tk\Db;

class Manager extends ControllerAdmin
{
    protected ?Table $table = null;

    public function doDefault(): void
    {
        $this->setUserAccess();
        $this->getPage()->setTitle('Team Manager', 'fa fa-cogs');

        // init table
        $this->table = new Table('team');
        $this->table->setOrderBy('team_id');
        $this->table->setLimit(25);

        $rowSelect = RowSelect::create('id', 'teamId');
        $this->table->appendCell($rowSelect);

        $this->table->appendCell('actions')
            ->addCss('text-nowrap text-center')
            ->addOnValue(function(Team $obj, Cell $cell) {
                $url = Uri::create('/teamEdit')->set('teamId', $obj->teamId);
                return <<<HTML
                    <a class="btn btn-outline-success" href="$url" title="Edit"><i class="fa fa-fw fa-edit"></i></a>
                HTML;
            });

        $this->table->appendCell('name')
            ->addCss('text-nowrap')
            ->setSortable(true)
            ->addHeaderCss('max-width')
            ->addOnValue(function(\App\Db\Team $obj, Cell $cell) {
                $url = Uri::create('/teamEdit', ['teamId' => $obj->teamId]);
                return sprintf('<a href="%s">%s</a>', $url, $obj->name);
            });

        $this->table->appendCell('active')
            ->addCss('text-nowrap')
            ->setSortable(true)
            ->addOnValue('\Tk\Table\Type\Boolean::onValue');

        $this->table->appendCell('modified')
            ->addCss('text-nowrap')
            ->setSortable(true)
            ->addOnValue('\Tk\Table\Type\DateFmt::onValue');

        $this->table->appendCell('created')
            ->addCss('text-nowrap')
            ->setSortable(true)
            ->addOnValue('\Tk\Table\Type\DateFmt::onValue');


        // Add Filter Fields
        $this->table->getForm()->appendField(new Input('search'))
            ->setAttr('placeholder', 'Search');

        $list = ['' => '-- All --', 'y' => 'Active', 'n' => 'Disabled'];
        $this->table->getForm()->appendField(new \Tk\Form\Field\Select('active', $list))->setValue('y');


        // Add Table actions
        $this->table->appendAction(Delete::create()
            ->addOnGetSelected([$rowSelect, 'getSelected'])
            ->addOnDelete(function(Delete $action, array $selected) {
                foreach ($selected as $team_id) {
                    Db::delete('team', compact('team_id'));
                }
            }));

        $this->table->appendAction(Select::create('Active Status', 'fa fa-fw fa-times')
            ->setActions(['Active' => 'active', 'Disable' => 'disable'])
            ->setConfirmStr('Toggle active/disable on the selected rows?')
            ->addOnGetSelected([$rowSelect, 'getSelected'])
            ->addOnSelect(function(Select $action, array $selected, string $value) {
                foreach ($selected as $id) {
                    $obj = Team::find($id);
                    $obj->active = (strtolower($value) == 'active');
                    $obj->save();
                }
            })
        );

        $this->table->appendAction(Csv::create()
            ->addOnCsv(function(Csv $action) {
                $action->setExcluded(['actions']);
                if (!$this->table->getCell(Team::getPrimaryProperty())) {
                    $this->table->prependCell(Team::getPrimaryProperty())->setHeader('id');
                }
                $this->table->getCell('name')->getOnValue()->reset();
                $filter = $this->table->getDbFilter()->resetLimits();
                return Team::findFiltered($filter);
            }));

        // execute table
        $this->table->execute();

        // todo: remove cell orderBy validation before release
        // if (!$this->table->validateCells(Team::getDataMap())) {
        //     $this->table->getTableSession()->remove($this->table->makeRequestKey(Table::PARAM_ORDERBY));
        // }

        // Set the table rows
        $filter = $this->table->getDbFilter();
        $rows = Team::findFiltered($filter);
        $this->table->setRows($rows, Db::getLastStatement()->getTotalRows());
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setText('title', $this->getPage()->getTitle());
        $template->addCss('icon', $this->getPage()->getIcon());

        $template->appendTemplate('content', $this->table->show());

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div>
  <div class="page-actions card mb-3">
    <div class="card-body">
      <a href="/teamEdit" title="Create Team" class="btn btn-outline-secondary"><i class="fa fa-plus"></i> Create Team</a>
    </div>
  </div>
  <div class="card mb-3">
    <div class="card-header"><i var="icon"></i> <span var="title"></span></div>
    <div class="card-body" var="content"></div>
  </div>
</div>
HTML;
        return Template::load($html);
    }

}