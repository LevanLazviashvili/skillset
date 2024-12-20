<?php namespace Backend\Controllers;

use Lang;
use Flash;
use Backend;
use RainLab\User\Models\User;
use Redirect;
use Response;
use BackendMenu;
use BackendAuth;
use Backend\Models\UserGroup;
use Backend\Classes\Controller;
use System\Classes\SettingsManager;
use System\Models\File;

/**
 * Backend user controller
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 *
 */
class Users extends Controller
{
    /**
     * @var array Extensions implemented by this controller.
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class
    ];

    /**
     * @var array `FormController` configuration.
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var array `ListController` configuration.
     */
    public $listConfig = 'config_list.yaml';

    /**
     * @var array Permissions required to view this page.
     */
    public $requiredPermissions = ['backend.manage_users'];

    /**
     * @var string HTML body tag class
     */
    public $bodyClass = 'compact-container';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if ($this->action == 'myaccount') {
            $this->requiredPermissions = null;
        }

        BackendMenu::setContext('October.System', 'system', 'users');
        SettingsManager::setContext('October.System', 'administrators');
    }

    /**
     * Extends the list query to hide superusers if the current user is not a superuser themselves
     */
    public function listExtendQuery($query)
    {
        if (!$this->user->isSuperUser()) {
            $query->where('is_superuser', false);
        }
    }

    /**
     * Prevents non-superusers from even seeing the is_superuser filter
     */
    public function listFilterExtendScopes($filterWidget)
    {
        if (!$this->user->isSuperUser()) {
            $filterWidget->removeScope('is_superuser');
        }
    }

    /**
     * Strike out deleted records
     */
    public function listInjectRowClass($record, $definition = null)
    {
        if ($record->trashed()) {
            return 'strike';
        }
    }

    /**
     * Extends the form query to prevent non-superusers from accessing superusers at all
     */
    public function formExtendQuery($query)
    {
        if (!$this->user->isSuperUser()) {
            $query->where('is_superuser', false);
        }

        // Ensure soft-deleted records can still be managed
        $query->withTrashed();
    }

    /**
     * Update controller
     */
    public function update($recordId, $context = null)
    {
        // Users cannot edit themselves, only use My Settings
        if ($context != 'myaccount' && $recordId == $this->user->id) {
            return Backend::redirect('backend/users/myaccount');
        }
        return $this->asExtension('FormController')->update($recordId, $context);
    }

    public function formAfterUpdate($model)
    {
        $this->CloneImageOnFrontUser($model);
    }

    public function formAfterCreate($model)
    {
        $this->CloneImageOnFrontUser($model);
    }

    /**
     * Handle restoring users
     */
    public function update_onRestore($recordId)
    {
        $this->formFindModelObject($recordId)->restore();

        Flash::success(Lang::get('backend::lang.form.restore_success', ['name' => Lang::get('backend::lang.user.name')]));

        return Redirect::refresh();
    }

    /**
     * Impersonate this user
     */
    public function update_onImpersonateUser($recordId)
    {
        if (!$this->user->hasAccess('backend.impersonate_users')) {
            return Response::make(Lang::get('backend::lang.page.access_denied.label'), 403);
        }

        $model = $this->formFindModelObject($recordId);

        BackendAuth::impersonate($model);

        Flash::success(Lang::get('backend::lang.account.impersonate_success'));

        return Backend::redirect('backend/users/myaccount');
    }

    /**
     * Unsuspend this user
     */
    public function update_onUnsuspendUser($recordId)
    {
        $model = $this->formFindModelObject($recordId);

        $model->unsuspend();

        Flash::success(Lang::get('backend::lang.account.unsuspend_success'));

        return Redirect::refresh();
    }

    /**
     * My Settings controller
     */
    public function myaccount()
    {
        SettingsManager::setContext('October.Backend', 'myaccount');

        $this->pageTitle = 'backend::lang.myaccount.menu_label';
        return $this->update($this->user->id, 'myaccount');
    }

    /**
     * Proxy update onSave event
     */
    public function myaccount_onSave()
    {
        $result = $this->asExtension('FormController')->update_onSave($this->user->id, 'myaccount');

        /*
         * If the password or login name has been updated, reauthenticate the user
         */
        $loginChanged = $this->user->login != post('User[login]');
        $passwordChanged = strlen(post('User[password]'));
        if ($loginChanged || $passwordChanged) {
            BackendAuth::login($this->user->reload(), true);
        }

        return $result;
    }

    /**
     * Add available permission fields to the User form.
     * Mark default groups as checked for new Users.
     */
    public function formExtendFields($form)
    {
        if ($form->getContext() == 'myaccount') {
            return;
        }

        if (!$this->user->isSuperUser()) {
            $form->removeField('is_superuser');
        }

        /*
         * Add permissions tab
         */
        $form->addTabFields($this->generatePermissionsField());

        /*
         * Mark default groups
         */
        if (!$form->model->exists) {
            $defaultGroupIds = UserGroup::where('is_new_user_default', true)->lists('id');

            $groupField = $form->getField('groups');
            if ($groupField) {
                $groupField->value = $defaultGroupIds;
            }
        }
    }

    /**
     * Adds the permissions editor widget to the form.
     * @return array
     */
    protected function generatePermissionsField()
    {
        return [
            'permissions' => [
                'tab' => 'backend::lang.user.permissions',
                'type' => 'Backend\FormWidgets\PermissionEditor',
                'trigger' => [
                    'action' => 'disable',
                    'field' => 'is_superuser',
                    'condition' => 'checked'
                ]
            ]
        ];
    }

    private function CloneImageOnFrontUser($model)
    {
        $FrontUser = (new User)->where('admin_user_id', $model->id)->first();
        $file = new File();
        (new File)->where('attachment_type', 'RainLab\User\Models\User')->where('attachment_id', $FrontUser->id)->delete();
        if ($model->avatar) {
            $file->disk_name = $model->avatar->disk_name;
            $file->file_name = $model->avatar->file_name;
            $file->file_size = $model->avatar->file_size;
            $file->content_type = $model->avatar->content_type;
            $file->field = $model->avatar->field;
            $file->attachment_id = $FrontUser->id;
            $file->attachment_type = 'RainLab\User\Models\User';
            $file->is_public = $model->avatar->is_public;
            $file->save();
        }
    }
}
