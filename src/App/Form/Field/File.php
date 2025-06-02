<?php
namespace App\Form\Field;

use Tk\Db\Model;
use Tk\FileUtil;
use Tk\Path;

/**
 * Use this field in conjunction with the \App\Db\File object
 */
class File extends \Tk\Form\Field\File
{

    /**
     * The file owner object that will be used as the fkey and fid for the file records
     */
    protected Model $model;


    public function __construct(string $name, ?Model $model = null)
    {
        parent::__construct($name);
        $this->model = $model;

        $this->setAttr('multiple', 'multiple');
        $this->setAttr('data-uploader', self::class);
        //$this->addCss('tk-multiinput');
    }

    public static function create(string $name, ?Model $model = null): self
    {
        return new self($name, $model);
    }

    /**
     * This is called only once the form has been submitted
     *   and new data loaded into the fields
     */
    public function execute(array $values = []): static
    {
        if ($this->hasFile()) {
            foreach ($this->getUploads() as $file) {
                $dest = Path::createDataPath($this->getModel()->dataPath . '/' . $file['name']);
                FileUtil::mkdir(dirname($dest));
                move_uploaded_file($file['tmp_name'], $dest->toString());

                $file = \App\Db\File::create($dest, $this->getModel());
                // Remove any existing File if path matches
                $exists = \App\Db\File::findByFilename($file->filename);
                $exists?->delete();
                $file->save();
            }
        }
        return $this;
    }

    public function getModel(): ?Model
    {
        return $this->model;
    }

}