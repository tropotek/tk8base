<?php
namespace App\Component;

use App\Db\User;
use Bs\Mvc\ComponentInterface;
use Bs\Mvc\Table;
use Dom\Template;
use Tk\Db;
use Tk\FileUtil;
use Tk\Form\Field\Input;
use Tk\Log;
use Tk\Path;
use Tk\Table\Cell;
use Tk\Uri;

class UserPhoto extends \Dom\Renderer\Renderer implements ComponentInterface
{
    const string CONTAINER_ID = 'user-photo';

    protected ?User $user = null;
    protected string $uploadError = '';


    public function doDefault(): ?Template
    {
        if (!User::getAuthUser()) return null;

        $userId = intval($_REQUEST['userId'] ?? 0);
        $action = trim($_REQUEST['action'] ?? '');

        $this->user = User::find($userId);
        if (!($this->user instanceof User)) {
            return null;
        }

        if ($action == 'upload') {
            $f = $_FILES['file'] ?? null;
            if ($f != null) {
                $dataPath = $this->user->getDataPath() . '/' . $f['full_path'] ?? '';
                $filename = Path::createDataPath($dataPath);
                vd($dataPath, $filename);

                if (empty($f['error'])) {
                    FileUtil::mkdir(dirname($filename));

                    // overwrite an existing file without creating a new file record
                    move_uploaded_file($f['tmp_name'] ?? '', $filename);
                    $this->user->image = $dataPath;
                    $this->user->save();
                } else {
                    $this->uploadError = \Tk\Form\Field\File::ERROR_MSG[$f['error']] ?? 'Failed to upload file.';
                    Log::error($this->uploadError);
                }
            }
        }

        return $this->show();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setAttr('container', 'id', self::CONTAINER_ID);

        if ($this->uploadError) {
            $template->setText('error', $this->uploadError);
            $template->setVisible('error');
        }

        if ($this->user->getImageUrl()) {
            $template->setAttr('img', 'src', $this->user->getImageUrl());
            $template->setVisible('image');
        }

        $maxBytes = min(
            \Tk\FileUtil::string2Bytes(strval(ini_get('upload_max_filesize'))),
            \Tk\FileUtil::string2Bytes(strval(ini_get('post_max_size')))
        );
        $maxBytes = FileUtil::bytes2String($maxBytes);
        $template->setText('max-bytes', $maxBytes);

        $url = Uri::create();
        $template->setAttr('form', 'hx-post', $url);

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $containerId = self::CONTAINER_ID;

        $html = <<<HTML
<div var="container">
    <div class="card card-edit mb-3">
        <div class="card-header"><i class="far fa-image"></i> Profile Photo</div>
        <div class="card-body">

            <form class="form" var="form"
                hx-encoding="multipart/form-data"
                hx-post=""
                hx-swap="outerHTML"
                hx-target="#{$containerId}"
                hx-trigger="change from:#file-upload">
				<input type="hidden" name="action" value="upload" />

                <div class="mb-2 text-center" choice="image">
                    <img src="#" class="img-thumbnail" style="min-width: 100%" var="img" />
                </div>

                <div class="col">
                    <input type="file" name="file" id="file-upload" class="form-control" accept="image/*" placeholder="Click to upload photo" var="file">
                    <div class="invalid-feedback text-danger" id="file-invalid-feedback" choice="error">This is an error</div>
                    <small class="text-muted" style="font-weight: normal;">Max upload size: <span var="max-bytes"></span></small>
                </div>
            </form>

        </div>
    </div>
</div>
HTML;
        return Template::load($html);
    }

}
