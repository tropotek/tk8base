<?php
namespace App\Component;

use App\Db\Team;
use App\Db\User;
use Bs\Mvc\ComponentInterface;
use Bs\Mvc\Table;
use Dom\Template;
use Tk\Db;
use Tk\Log;
use Tk\Table\Action;
use Tk\Table\Cell;
use Tk\Uri;

class TeamMemberTable extends \Dom\Renderer\Renderer implements ComponentInterface
{
    protected Table $table;
    protected ?Team $team = null;


    public function doDefault(): ?Template
    {
        if (!User::getAuthUser()) return null;

        $teamId = (int)($_REQUEST['teamId'] ?? 0);
        $action = trim($_REQUEST['action'] ?? '');

        $this->team = Team::find($teamId);
        if (!($this->team instanceof Team)) {
            Log::error("invalid Team ID {teamId}");
            return null;
        }

        if ($action == 'rem') {
            $userId = (int)($_REQUEST['userId'] ?? 0);
            Team::removeMember($teamId, $userId);
        } else if ($action == 'add') {
            $userId = (int)($_REQUEST['userId'] ?? 0);
            Team::addMember($teamId, $userId);
        }

        // init table
        $this->table = new Table('team-members');
        $this->table->hideReset();
        //$this->table->setOrderBy('name_short');
        $this->table->setLimit(0);
        $this->table->addCss('tk-table-sm');

        $this->table->appendCell('actions')
            ->addCss('text-nowrap text-center')
            ->addOnHtml(function(User $obj, Cell $cell) {
                $url = Uri::create()->set('action', 'rem')->set('userId', $obj->userId);
                return <<<HTML
                    <button class="btn btn-danger btn-xs" title="Remove Team Member"
                        hx-post="$url"
                        hx-swap="outerHTML"
                        hx-target="#team-members"
                        hx-select="#team-members"
                        hx-confirm="Are you sure you want to remove team member?">
                        <i class="fas fa-user-times"></i>
                    </button>
                HTML;
            });

        $this->table->appendCell('nameShort')
            ->setHeader('Name')
            ->addCss('max-width text-nowrap')
            ->setSortable(false);


        // Add Table actions
        $this->table->appendAction('addMember')
            ->addOnShow(function (Action $action) {
                $url = Uri::create('/component/userSelect');
                return <<<HTML
                    <button type="button"
                        class="btn btn-xs btn-light"
                        title="Add team member"
                        hx-get="{$url}"
                        hx-trigger="click queue:none"
                        hx-target="body"
                        hx-swap="beforeend">
                        <i class="fa fa-user-check"></i> Add Member
                    </button>
                HTML;
            });

        // execute table
        $this->table->execute();

        // Set the table rows
        $rows = Team::getMembers($teamId);
        $this->table->setRows($rows, Db::getLastStatement()->getTotalRows());

        return $this->show();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        $this->table->getRenderer()->setFooterEnabled(false);
        $template->appendTemplate('content', $this->table->show());

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $teamId = $this->team->teamId;

        $html = <<<HTML
<div id="team-member-table">
    <div class="card mb-3">
        <div class="card-header"><i class="fas fa-users"></i> <span var="title">Members</span></div>
        <div class="card-body" var="content">
            <p>Manage users who will receive an email and notification on their dashboard when team actions are performed.</p>
        </div>
    </div>

<script>
jQuery(function($) {
    const table  = '#{$this->table->getId()}';
    const dialog = '#user-select';
    let   teamId =  {$teamId};

    $(document).on('userSelect:selected', dialog, function(e, userId, name) {
        let url = tkConfig.baseUrl + `/component/teamMemberTable?action=add&teamId=\${teamId}&userId=\${userId}`;
        htmx.ajax('POST', url, {
            source: table,
            target: table,
            select: table,
            swap: 'outerHTML',
        });
    });
});
</script>
</div>
HTML;
        return Template::load($html);
    }

}
