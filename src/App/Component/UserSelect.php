<?php
namespace App\Component;

use App\Db\User;
use Bs\Mvc\Table;
use Dom\Template;
use Tk\Db;
use Tk\Form\Field\Input;
use Tk\Table\Cell;

class UserSelect extends \Dom\Renderer\Renderer
{
    const string CONTAINER_ID = 'user-select';

    protected Table $table;


    public function doDefault(): ?Template
    {
        if (!User::getAuthUser()) return null;

        $this->table = new Table('user-select-dlg');
        $this->table->hideReset();
        $this->table->setOrderBy('name_short');
        $this->table->setLimit(10);
        $this->table->addCss('tk-table-sm');

        $this->table->appendCell('name_short')
            ->setHeader('Name')
            ->addCss('text-nowrap')
            ->setSortable(true)
            ->addHeaderCss('max-width')
            ->addOnValue(function(\App\Db\User $obj, Cell $cell) {
                return sprintf('<a class="user-item" data-user-id="%s" href="#" title="Select %s">%s</a>',
                    $obj->userId,
                    $obj->nameShort,
                    $obj->nameShort
                );
            });

        // Add Filter Fields
        $this->table->getForm()->appendField(new Input('search'))
            ->setAttr('placeholder', 'Search');

        // execute table
        $this->table->execute();

        // Set the table rows
        $filter = $this->table->getDbFilter();
        $filter->replace([
            'active' => true,
        ]);
        $rows = User::findFiltered($filter);
        $this->table->setRows($rows, Db::getLastStatement()->getTotalRows());

        return $this->show();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setAttr('dialog', 'id', self::CONTAINER_ID);

        // convert all table urls to HTMX urls
        $template->appendTemplate('content', $this->table->htmxShow());

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $dialogId = self::CONTAINER_ID;
        $tableId = $this->table->getId();

        $html = <<<HTML
<div class="modal fade" var="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Select User</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" var="content"></div>
    </div>
  </div>

<script>
jQuery(function($) {
    const dialog = '#{$dialogId}';
    const table = '#{$tableId}';

    function init() {
        tkInit(table);
        $(table).on('click', 'tbody tr', function() {
            let el = $('.user-item', this);
            $(dialog).trigger('userSelect:selected', [
                el.data('userId'),
                el.text()
            ]);
            $(dialog).modal('hide');
        });
    }

    $(document).on('htmx:afterSettle', dialog, function(e) {
        init();
    });

    // open the dialog as soon as HTMX settles
    init();
    $(dialog).modal('show');

    // remove the dialog element from the dom when it closes
    $(dialog).on('hidden.bs.modal', function() {
        $(dialog).remove();
    });
});
</script>
</div>
HTML;
        return Template::load($html);
    }

}
