<?php
namespace App\Db;

use Tk\DataMap\DataMap;
use Tk\Db\Mapper\Filter;
use Tk\Db\Mapper\Mapper;
use Tk\Db\Mapper\Result;
use Tk\Db\Tool;
use Tk\DataMap\Db;
use Tk\DataMap\Form;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class UserMap extends Mapper
{

    public function getDbMap(): DataMap
    {
        if (!$this->dbMap) {
            $this->dbMap = new DataMap();
            $this->dbMap->addDataType(new Db\Integer('id', 'user_id'));
            $this->dbMap->addDataType(new Db\Text('uid'));
            $this->dbMap->addDataType(new Db\Text('type'));
            $this->dbMap->addDataType(new Db\Text('username'));
            $this->dbMap->addDataType(new Db\Text('password'));
            $this->dbMap->addDataType(new Db\Text('nameFirst', 'name_first'));
            $this->dbMap->addDataType(new Db\Text('nameLast', 'name_last'));
            $this->dbMap->addDataType(new Db\Text('email'));
            $this->dbMap->addDataType(new Db\Date('lastLogin', 'last_login'));
            $this->dbMap->addDataType(new Db\Boolean('active'));
            $this->dbMap->addDataType(new Db\Date('modified'));
            $this->dbMap->addDataType(new Db\Date('created'));
            $del = $this->dbMap->addDataType(new Db\Boolean('del'));

            $this->setDeleteType($del);
        }
        return $this->dbMap;
    }

    public function getFormMap(): DataMap
    {
        if (!$this->formMap) {
            $this->formMap = new DataMap();
            $this->formMap->addDataType(new Form\Text('uid'));
            $this->formMap->addDataType(new Form\Text('type'));
            $this->formMap->addDataType(new Form\Text('username'));
            $this->formMap->addDataType(new Form\Text('password'));
            $this->formMap->addDataType(new Form\Text('nameFirst'));
            $this->formMap->addDataType(new Form\Text('nameLast'));
            $this->formMap->addDataType(new Form\Text('email'));
            $this->formMap->addDataType(new Form\Boolean('active'));
        }
        return $this->formMap;
    }

    public function findByUsername(string $username): ?User
    {
        return $this->findFiltered(array('username' => $username))->current();
    }

    public function findFiltered(array|Filter $filter, ?Tool $tool = null): Result
    {
        return $this->selectFromFilter($this->makeQuery(Filter::create($filter)), $tool);
    }

    public function makeQuery(Filter $filter): Filter
    {
        $filter->appendFrom('%s a', $this->quoteParameter($this->getTable()));

        if (!empty($filter['keywords'])) {
            $kw = '%' . $this->getDb()->escapeString($filter['keywords']) . '%';
            $w = '';
            $w .= sprintf('a.uid LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.name_first LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.name_last LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.username LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.email LIKE %s OR ', $this->quote($kw));
            if (is_numeric($filter['keywords'])) {
                $id = (int)$filter['keywords'];
                $w .= sprintf('a.id = %d OR ', $id);
            }
            if ($w) $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
        }

        if (!empty($filter['id'])) {
            $w = $this->makeMultiQuery($filter['id'], 'a.user_id');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        if (!empty($filter['type'])) {
            $w = $this->makeMultiQuery($filter['type'], 'a.type');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        if (!empty($filter['uid'])) {
            $filter->appendWhere('a.uid = %s AND ', $this->getDb()->quote($filter['uid']));
        }

        if (!empty($filter['username'])) {
            $filter->appendWhere('a.username = %s AND ', $this->getDb()->quote($filter['username']));
        }

        if (!empty($filter['email'])) {
            $filter->appendWhere('a.email = %s AND ', $this->quote($filter['email']));
        }

        if (!empty($filter['nameFirst'])) {
            $filter->appendWhere('a.name_first = %s AND ', $this->quote($filter['nameFirst']));
        }

        if (!empty($filter['nameLast'])) {
            $filter->appendWhere('a.name_last = %s AND ', $this->quote($filter['nameLast']));
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        return $filter;
    }

}
