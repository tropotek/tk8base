<?php
namespace App\Table;

use App\Db\User;
use Bs\Mvc\Table;
use Dom\Template;
use Tk\Alert;
use Tk\FileUtil;
use Tk\Table\Action\ColumnSelect;
use Tk\Uri;
use Tk\Db;
use Tk\Table\Action\Csv;
use Tk\Table\Action\Delete;
use Tk\Table\Cell;
use Tk\Table\Cell\RowSelect;

class File extends Table
{

    protected string $fkey = '';


    public function init(): static
    {
        $rowSelect = RowSelect::create('id', 'fileId');
        $this->appendCell($rowSelect);

        $this->appendCell('actions')
            ->addHeaderCss('text-center')
            ->addCss('text-nowrap text-center')
            ->addOnHtml(function(\App\Db\File $file, Cell $cell) {
                $view = $file->getUrl();
                $del  = Uri::create()->set('del', strval($file->fileId));
                return <<<HTML
                    <a class="btn btn-success" href="$view" title="View" target="_blank"><i class="fa fa-fw fa-eye"></i></a> &nbsp;
                    <a class="btn btn-danger" href="$del" title="Delete" data-confirm="Are you sure you want to delete file {$file->filename}"><i class="fa fa-fw fa-trash"></i></a>
                HTML;
            });

        $this->appendCell('filename')
            ->addHeaderCss('text-start max-width')
            ->setSortable(true)
            ->addOnHtml(function(\App\Db\File $file, Cell $cell) {
                return sprintf('<a href="%s" target="_blank">%s</a>', $file->getUrl(), $file->filename);
            });

        $this->appendCell('userId')
            ->setSortable(true)
            ->addOnValue(function(\App\Db\File $file, Cell $cell) {
                $user = User::find($file->userId);
                return $user->nameShort ?? '';
            });

//        $this->appendCell('fkey')->setHeader('Key')
//            ->setSortable(true);
//        $this->appendCell('fid')->setHeader('Key ID')
//            ->setSortable(true);

        $this->appendCell('bytes')
            ->setSortable(true)
            ->addCss('text-nowrap text-end')
            ->addOnValue(function(\App\Db\File $file, Cell $cell) {
                return FileUtil::bytes2String($file->bytes);
            });

        $this->appendCell('selected')
            ->setSortable(true)
            ->addHeaderCss('text-center')
            ->addCss('text-center text-nowrap')
            ->addOnValue('\Tk\Table\Type\Boolean::onValue');

        $this->appendCell('created')
            ->setSortable(true)
            ->addHeaderCss('text-end')
            ->addCss('text-end text-nowrap')
            ->addOnValue('\Tk\Table\Type\Date::getLongDateTime');


        // Add Table actions
        $this->table->appendAction(ColumnSelect::create());
        $this->table->appendAction(Delete::createDefault(\App\Db\File::class, $rowSelect));
        $this->table->appendAction(Csv::createDefault(\App\Db\File::class, $rowSelect));

        return $this;
    }

    public function execute(?callable $onInit = null): static
    {
        if (isset($_GET['del'])) {
            $this->doDelete(intval($_GET['del']));
        }
        parent::execute();
        return $this;
    }

    private function doDelete(int $id): void
    {
        $file = \App\Db\File::find($id);
        if (is_object($file)) {
            $file->delete();
            Alert::addSuccess('File removed successfully.');
        }
        Uri::create()->reset()->redirect();
    }

    public function show(): ?Template
    {
        $renderer = $this->getRenderer();
        $renderer->setFooterEnabled(false);
        return $renderer->show();
    }

    public function getFkey(): string
    {
        return $this->fkey;
    }

    public function setFkey(string $fkey): File
    {
        $this->fkey = $fkey;
        return $this;
    }

}