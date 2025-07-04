<?php
namespace App\Db;

use Tk\Db\Model;
use Tk\Db;
use Tk\Db\Filter;

class Team extends Model
{
    public int        $teamId      = 0;
    public string     $name        = '';
    public ?string    $description = null;
    public bool       $active      = true;
    public ?\DateTime $confirmedAt = null;
    public \DateTime  $completedOn;
    public ?\DateTime $modified    = null;
    public ?\DateTime $created     = null;

    public int        $memberTotal  = 0;
    public array      $members      = [];


    public function __construct()
    {
        $this->completedOn = new \DateTime();
    }

    public function save(): void
    {
        $map = static::getDataMap();

        $values = $map->getArray($this);
        if ($this->teamId) {
            $values['team_id'] = $this->teamId;
            Db::update('team', 'team_id', $values);
        } else {
            unset($values['team_id']);
            Db::insert('team', $values);
            $this->teamId = Db::getLastInsertId();
        }

        $this->reload();
    }

    /**
     * @return array<int,self>
     */
    public static function findFiltered(array|Filter $filter): array
    {
        $filter = Filter::create($filter);
        $filter->appendFrom('v_team a');

        if (!empty($filter['search'])) {
            $filter['lSearch'] = '%' . strtolower($filter['search']) . '%';
            $w  = "a.team_id = :search ";
            $w  = "OR LOWER(CONCAT_WS(' ', a.name)) LIKE :lSearch ";
            $filter->appendWhere('AND (%s)', $w);
        }

        if (!empty($filter['id'])) {
            $filter['teamId'] = $filter['id'];
        }
        if (!empty($filter['teamId'])) {
            if (!is_array($filter['teamId'])) $filter['teamId'] = [$filter['teamId']];
            $filter->appendWhere('AND a.team_id IN :teamId');
        }

        if (!empty($filter['exclude'])) {
            if (!is_array($filter['exclude'])) $filter['exclude'] = [$filter['exclude']];
            $filter->appendWhere('AND a.team_id NOT IN :exclude');
        }

        if (!empty($filter['name'])) {
            $filter->appendWhere('AND a.name = :name');
        }
        if (is_bool(truefalse($filter['active'] ?? null))) {
            $filter['active'] = truefalse($filter['active']);
            $filter->appendWhere('AND a.active = :active');
        }

        return Db::query("
            SELECT *
            FROM {$filter->getSql()}",
            $filter->all(),
            self::class
        );
    }

    /**
     * @return array<int,User>
     */
    public static function getMembers(int $teamId): array
    {
        return Db::query("
            SELECT u.*
            FROM v_user u
            LEFT JOIN team_has_user USING (user_id)
            WHERE team_id = :teamId
            ORDER BY name_short",
            compact('teamId'),
            User::class
        );
    }

    public static function hasMember(int $teamId, int $userId): bool
    {
        return Db::queryBool("
            SELECT EXISTS (
                SELECT *
                FROM team_has_user
                WHERE team_id = :teamId
                AND user_id = :userId
            )",
            compact('teamId', 'userId'),
        );
    }

    public static function addMember(int $teamId, int $userId): bool
    {
        return Db::insertIgnore('team_has_user', ['team_id' => $teamId, 'user_id' => $userId]);
    }

    public static function removeMember(?int $teamId = null, ?int $userId = null): bool
    {
        if ($teamId && $userId) {
            return (false !== Db::delete('team_has_user', ['team_id' => $teamId, 'user_id' => $userId]));
        } else if(!$teamId && $userId) {
            return (false !=- Db::delete('team_has_user', ['user_id' => $userId]));
        } else if ($teamId && !$userId) {
            return (false !== Db::delete('team_has_user', ['team_id' => $teamId]));
        }
        return false;
    }

    public function validate(): array
    {
        $errors = [];

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
        }

        return $errors;
    }

}