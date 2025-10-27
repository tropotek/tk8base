<?php
namespace App\Component;

use App\Db\File;
use App\Db\User;
use Bs\Mvc\ComponentInterface;
use Bs\Mvc\Table;
use Bs\Traits\ForeignModelTrait;
use Dom\Template;
use Tk\Date;
use Tk\Db;
use Tk\FileUtil;
use Tk\Log;
use Tk\Path;
use Tk\Table\Cell;
use Tk\Uri;

class Files extends \Dom\Renderer\Renderer implements ComponentInterface
{
    const string CONTAINER_ID = 'comp-files';

    protected Table     $table;
    protected ?Db\Model $model       = null;
    protected string    $uploadError = '';
    protected bool      $canEdit     = true;


    public function doDefault(): ?Template
    {
        if (!User::getAuthUser()->isStaff()) return null;

        $fid      = (int)($_REQUEST['fid'] ?? 0);
        $fkey     = trim($_REQUEST['fkey'] ?? '');
        $dataPath = trim($_REQUEST['dataPath'] ?? '');
        $this->canEdit = truefalse($_REQUEST['canEdit'] ?? true);
        $action   = trim($_REQUEST['action'] ?? '');

        if (!class_exists($fkey)) {
            Log::error("failed to find model {$fkey}");
            return null;
        }

        $this->model = File::findDbModel($fkey, $fid);
        if (!$this->model) {
            Log::error("failed to find model {$fkey} with id {$fid}");
            return null;
        }


        if ($action == 'del') {
            $fileId = intval($_GET['fileId'] ?? 0);
            $file = File::find($fileId);
            if ($file instanceof File) {
                $file->delete();
            }
            Uri::create()->remove('action')->remove('fileId')->redirect();
        } else if ($action == 'sel') {
            $selected = $_POST['selected'] ?? [];
            $files = File::findFiltered(['model' => $this->model]);
            foreach ($files as $file) {
                $file->selected = in_array($file->fileId, $selected);
                $file->save();
            }
            Uri::create()->remove('action')->remove('fileId')->redirect();
        } else if ($action == 'upload') {
            $f = $_FILES['file'] ?? null;
            if ($f != null) {
                $filename = Path::create($dataPath . '/' . $f['full_path']);
                $file = File::create($filename, $this->model);
                $file->mime = $f['type'] ?? '';
                $file->bytes = $f['size'] ?? 0;

                if (empty($f['error'])) {
                    FileUtil::mkdir(dirname($file->getFullPath()));
                    if (is_file($file->getFullPath())) {
                        // overwrite existing file without creating a new file record
                        move_uploaded_file($f['tmp_name'] ?? '', $file->getFullPath());
                    } else {
                        if (move_uploaded_file($f['tmp_name'] ?? '', $file->getFullPath())) {
                            $file->save();
                        }
                    }
                    Uri::create()->remove('action')->redirect();
                } else {
                    $this->uploadError = \Tk\Form\Field\File::ERROR_MSG[$f['error']] ?? '';
                    Log::error($this->uploadError);
                }
            }
        }

        // init table
        $this->table = new Table('com-files');
        $this->table->hideReset();
        $this->table->setOrderBy('-created');
        $this->table->setLimit(10);
        $this->table->addCss('tk-table-sm');

        $this->table->appendCell('actions')
            ->addCss('text-nowrap text-center')
            ->addOnHtml(function(File $obj, Cell $cell) {
                $del = Uri::create()->set('action', 'del')->set('fileId', $obj->fileId);
                $wrapId = '';
                if ($cell->getTable() instanceof Table) {
                    $wrapId = $cell->getTable()->getWrapId();
                }
                $disabled = $this->canEdit ? '' : 'disabled';
                return <<<HTML
                    <button class="btn btn-danger btn-xs" title="Delete File"
                        hx-post="{$del}"
                        hx-swap="outerHTML"
                        hx-target="#{$wrapId}"
                        hx-select="#{$wrapId}"
                        hx-confirm="Are you sure you want to delete this file?" {$disabled}>
                        <i class="fas fa-trash"></i>
                    </button>
                HTML;
            });

        $this->table->appendCell('label')
            ->addHeaderCss('max-width')
            ->addOnHtml(function(File $obj, Cell $cell) {
                $uploader = User::find(intval($obj->userId));
                $title = $obj->created->format(Date::FORMAT_AU_DATETIME);
                if ($uploader instanceof User) {
                    $title = sprintf('%s - %s', e($uploader->nameShort), e($obj->created->format(Date::FORMAT_AU_DATETIME)));
                }
                $cell->getTable()->getRowAttrs()->setAttr('title', $title);
                return sprintf('<a href="%s" target="_blank" title="Click to view file">%s</a>', $obj->getUrl()->toString(), $obj->label);
            });

        $this->table->appendCell('selected')
            ->addCss('text-center')
            ->addOnHtml(function(File $obj, Cell $cell) {
                $url = Uri::create()->set('action', 'sel')->set('fileId', $obj->fileId);
                $disabled = $this->canEdit ? '' : 'disabled';
                return sprintf('<input type="checkbox" name="selected[]" value="%s" %s
                    hx-post="%s"
                    hx-swap="none"
                    hx-trigger="change" %s/>',
                    $obj->fileId,
                    $obj->selected ? 'checked' : '',
                    $url->toString(),
                    $disabled
                );
            });

        $this->table->appendCell('size')
            ->addCss('text-nowrap')
            ->addOnValue(function(File $obj, Cell $cell) {
                return FileUtil::bytes2String($obj->bytes);
            });


        // execute table
        $this->table->execute();

        // Set the table rows
        $filter = $this->table->getDbFilter();
        $filter['model'] = $this->model;
        $rows = File::findFiltered($filter);
        $this->table->setRows($rows, Db::getLastStatement()->getTotalRows());

        return $this->show();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setAttr('container', 'id', $this->getContainerId());

        $this->table->getRenderer()->setFooterEnabled(false);
        $template->appendTemplate('content', $this->table->htmxShow());

        if ($this->uploadError) {
            $template->addCss('file', 'test-danger is-invalid');
            $template->setText('error', $this->uploadError);
            $template->setVisible('error');
        }

        if (!$this->canEdit) {
            $template->setAttr('file', 'disabled', 'disabled');
        }

        return $template;
    }

    public function getContainerId(): string
    {
        return self::CONTAINER_ID;
    }

    public function __makeTemplate(): ?Template
    {
        $url = Uri::create()->set('action', 'upload');
        $containerId = $this->getContainerId();
        $maxBytes = min(
            \Tk\FileUtil::string2Bytes(strval(ini_get('upload_max_filesize'))),
            \Tk\FileUtil::string2Bytes(strval(ini_get('post_max_size')))
        );
        $maxBytes = FileUtil::bytes2String($maxBytes);

        $html = <<<HTML
<div var="container">
    <div>
        <p><small>Use the checkbox to select any files to be included in the email report.</small></p>
    </div>
    <div class="file-upload-form">
        <div var="content"></div>
        <form id="file-upload-form" class="form-horizontal"
            hx-encoding="multipart/form-data"
            hx-post="{$url}"
            hx-swap="outerHTML"
            hx-target="#{$containerId}"
            hx-trigger="change from:#file-upload">
            <div class="col">
                <label class="col-form-label" for="file-upload">
                    Attatch file to case:
                </label>
                <input type="file" class="form-control form-control-sm" accept=".zip,.doc,.xls,.xlsx,.csv,.pdf,image/*,audio/*,video/*" var="file" name="file" id="file-upload" aria-describedby="file-invalid-feedback">
                <div class="invalid-feedback text-danger" id="file-invalid-feedback" choice="error">This is an error</div>
                <small class="text-muted" style="font-weight: normal;">Max upload size: {$maxBytes}</small>
            </div>
            <div class="mb-0" id="file-upload-progress-spacer" style="height: 5px;"></div>
            <div class="progress mb-0" id="file-upload-progress" style="display: none;height: 5px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"></div>
            </div>
        </form>
    </div>

<script>
    jQuery(function($) {
        htmx.on('#file-upload-form', 'htmx:xhr:progress', function(evt) {
            let val = (evt.detail.loaded/evt.detail.total * 100);
            let progress = $('#file-upload-progress');
            let spacer = $('#file-upload-progress-spacer');
            if (val > 0 && val < 100) {
                progress.css('display', 'block');
                spacer.css('display', 'none');
            } else {
                progress.css('display', 'none');
                spacer.css('display', 'block');
            }
            progress.css('width', `\${val}%`);
        });

        $('#files-badge').text($('#com-files tbody tr').length);
    });
</script>
</div>
HTML;
        return Template::load($html);
    }

}
