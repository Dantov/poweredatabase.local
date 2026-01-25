<?php
namespace app\models\serviceClasses;

use app\models\serviceTables\{Service_data,Users,Permissions};
use app\models\{User,Common,Validator};

use Yii;

class UsersAll extends Common
{
    protected array $all; 
    public array $user; 
    protected array $permissions;

    protected array $userfields;
    protected int $uid;

	function __construct( int $id = null)
    {
        $this->userfields = [
            'id','login','name','lastname','thirdname','fio','fullFio','role','clients','permissions',
            'location','about','email','access'];
        $this->getAllUsers();
        
        if ($id > 0 && $id < PHP_INT_MAX)
        {
            $this->uid = $id;
            $this->getUserByID($this->uid);
            $this->getAllPermissions();    
        }

        parent::__construct();
	}
    public function getBasicData( string $stab='' ) : array
    {
        $res = ['clients'=>[],'roles'=>[],'perm'=>[]];
        $res['clients'] = Service_data::find()->where(['tab'=>'client'])->asArray()->orderBy('name')->all();
        $res['roles'] = Service_data::find()->where(['tab'=>'role'])->asArray()->orderBy('name')->all();
        $res['perm'] = Permissions::find()->select(['id','name','description'])->asArray()->all();

        foreach( $res as &$tab )
        {
            foreach( $tab as &$single )
            {
                $single['active'] = '';
                $single['applied'] = '';
            }
        }

        switch( $stab )
        {
            case "clients":
                return $res['clients'];
            break;
            case "roles":
                return $res['roles'];
            break;
            case "perm":
                return $res['perm'];
            break;
            default:
                return $res;    
            break;
        }
        debug($res,'$res',1);
        return $res;
    }
    protected function getUserByID(int $id) : array
    {
        $this->user = Users::find()
            ->select($this->userfields)
            ->where(['id'=>$id])
            ->asArray()
            ->one();
        return $this->user;
    }
    public function getAllUsers() : array
    {
        if ( isset( $this->all ) ) return $this->all;
        $this->all = Users::find()
            ->select($this->userfields)
            ->asArray()
            ->all();
        return $this->all;
    }

	protected function getAllPermissions()
    {
        if ( isset( $this->permissions ) ) return $this->permissions;
		$this->permissions = Permissions::find()->select(['id','name','description'])->asArray()->all();
	}
    public function hasPermission( int $permID ) : bool
    {
        return array_key_exists($permID, $this->hisPermissions());
    }
    public function permissionsApplyed()
    {
        $permissions = $this->getAllPermissions();
        foreach ( $permissions as &$permA )
            $permA['applied'] = 0;

        $uPermissions = json_decode($this->user['permissions'],true);
        foreach ( $permissions as &$perm )
        {
            if ( in_array($perm['id'], $uPermissions) )
                $perm['applied'] = 1;
        }

        return $permissions;
    }
    public function hisPermissions() : array
    {
        if ( !isset($this->user['permissions']) ) 
            return [];

        $userPermissions = json_decode($this->user['permissions'],true); 
        $permissions = $this->getAllPermissions();
        
        $permittedFieldAll = [];
        foreach ( $permissions as $permission )
        {
            $pID = $permission['id'];
            if ( in_array( $pID, $userPermissions ) )
                $permittedFieldAll[$pID] = $permission;
        }

        return $permittedFieldAll;
    }
    public function getClients() : array
    {
        $clients = Service_data::find()->where(['tab'=>'client'])->asArray()->orderBy('name')->all();
        $userClients = json_decode($this->user['clients'],true);
        foreach( $clients as &$sClient )
            $sClient['active'] = 0;

        foreach( $userClients as $userClientID )
        {
            foreach( $clients as &$client )
                if ( (int)$client['id'] === (int)$userClientID ) 
                    $client['active'] = 1;
        }

        return $clients;
    }
    public function getRoleNames( string $roles = '' ) : string
    {
        if ( empty($roles) )
            $roles = $this->user['role'];

        $userRoles = json_decode($roles, true);
        $allRoles = $this->getAllRoles();
        $names = '';
        foreach( $userRoles as $roleID )
        {
            foreach( $allRoles as $sRole )
            {
                if ( (int)$sRole['id'] === (int)$roleID ) {
                    $names .= $sRole['name'] . ", ";
                }
            }
        }
        return trim($names,', ');
    }
    public function getRoles( string $roles = '' )
    {
        if ( empty($roles) )
            $roles = $this->user['role'];

        $userRoles = json_decode($roles, true);
        $allRoles = $this->getAllRoles();
        
        foreach( $allRoles as &$singRole )
            $singRole['active'] = 0;
        
        foreach( $userRoles as $roleID )
        {
            foreach( $allRoles as &$sRole )
                if ( (int)$sRole['id'] === (int)$roleID ) 
                    $sRole['active'] = 1;
        }
        return $allRoles;
    }
    protected function localFieldsValidate( array $validatedData ) : bool
    {
        $session = Yii::$app->session;
        $isAllValid = true;
        foreach( $validatedData as $field => $value )
        {
            if ( !$value ) {
                $session->setFlash($field, 'Заполнено не верено!');
                $isAllValid = false;
            };
        }
        return $isAllValid;
    }
    /*
     * Edit user information
     */
    public function saveUserData( array $post ) : bool
    {
        $thisuser = Users::find()->where(['id'=>$this->uid]);
        if ( !$thisuser->exists() )
            return false;

        $v = new Validator("edit user");
        $udata = [];

        $udata['logname'] = true;
        if ( !empty(trim($post['logname'])) )
            $udata['logname'] = $v->validateLogInput($post['logname'], $this->getAllUsers());

        $udata['bypass'] = true;
        if ( !empty(trim($post['bypass'])) )
            $udata['bypass'] = $v->validatePassInput($post['bypass']);

        $udata['firstName'] = true;
        if ( !empty(trim($post['firstName'])) )
            $udata['firstName'] = $v->validateString($post['firstName']);

        $udata['lastName'] = true;
        if ( !empty(trim($post['lastName'])) )
            $udata['lastName'] = $v->validateString($post['lastName']);

        $udata['thirdname'] = true;
        if ( !empty(trim($post['thirdname'])) )
            $udata['thirdname'] = $v->validateString($post['thirdname']);

        $udata['email'] = true;
        if ( !empty(trim($post['email'])) )
            $udata['email'] = $v->validateEmail($post['email'], $this->getAllUsers());

        if ( !$this->localFieldsValidate($udata) ) return false;
        
        $thisuser = $thisuser->one();

        if ( !empty(trim($post['logname'])) )
            $thisuser->login = $post['logname'];
        if ( !empty(trim($post['bypass'])) )
            $thisuser->pass = password_hash($post['bypass'], PASSWORD_DEFAULT); 
        if ( !empty(trim($post['email'])) )
            $thisuser->email = $post['email'];

        $thisuser->name = $post['firstName']; 
        $thisuser->lastname = $post['lastName']; 
        $thisuser->thirdname = $post['thirdname']; 
        $thisuser->fio = $post['firstName'] . " " . $post['lastName']; 
        $thisuser->fullFio = $post['firstName']. " " .$post['thirdname']. " " .$post['lastName']; 
        $thisuser->about = $v->sanitarizePost('usernote');

        $uRoles = [];
        $uClients = [];
        if ( isset($post['role']) ) 
            $uRoles = $this->applyUser("role", $post['role'] );
        if ( isset($post['clients']) )
            $uClients = $this->applyUser("client", $post['clients']);

        $thisuser->role = json_encode($uRoles);
        $thisuser->clients = json_encode($uClients);

        return $thisuser->save(false);
    }
    protected function applyUser( string $tab, array $data ) : array
    {
        $all = Service_data::find()->where(['tab'=>$tab])->asArray()->all();
        $valid = [];
        foreach( $data as $dataID )
        {
            foreach( $all as $single )
                if ( (int)$single['id'] === (int)$dataID ) 
                    $valid[] = $single['id'];
        }
        return $valid;
    }

    public function applyRight( int $permid ) : bool
    { 
        $thisuser = $this->applyRightPrepare( $permid );
        if ( $thisuser === false ) return false;

        if ( $this->hasPermission($permid) ) return false;

        $oldUP = json_decode($thisuser->permissions,true);
        $oldUP[] = $permid;

        $thisuser->permissions = json_encode($oldUP);
        return $thisuser->save(false);
    }
    public function removeRight( int $permid ) : bool
    {
        $thisuser = $this->applyRightPrepare( $permid );
        if ( $thisuser === false ) return false;
        if ( !$this->hasPermission($permid) ) return false;
        $oldUP = json_decode($thisuser->permissions);
        foreach ( $oldUP as $key => $upID )
        {
            if ( $upID === $permid )
                unset($oldUP[$key]);
        }

        $thisuser->permissions = json_encode($oldUP);
        return $thisuser->save(false);
    }
    protected function applyRightPrepare( int $permid ) : mixed
    {
        if ( !($permid > 0 && $permid < PHP_INT_MAX) ) return false;

        $thisuser = Users::find()->where(['id'=>$this->uid]);

        if ( !$thisuser->exists() )
            return false;

        $allPerms = $this->getAllPermissions();

        //Search for valid permission
        $valid = false;
        foreach( $allPerms as $perm )
        {
            if ( (int)$perm['id'] === $permid ) {
                $valid = true;
                break;
            }
        }
        if (!$valid) return false;

        return $thisuser->select(['id','permissions'])->one();
    }

    public function addNewUser( array $post )
    {
        $v = new Validator("add user");
        $udata = [];
        $udata['logname'] = $v->validateLogInput($post['logname'], $this->getAllUsers() );
        $udata['bypass'] = $v->validatePassInput($post['bypass']);

        $udata['firstName'] = $v->validateString($post['firstName']);
        $udata['lastName']  = $v->validateString($post['lastName']);

        $udata['thirdname'] = true;
        if ( !empty(trim($post['thirdname'])) )
            $udata['thirdname'] = $v->validateString($post['thirdname']);

        $udata['email'] = true;
        if ( !empty(trim($post['email'])) )
            $udata['email'] = $v->validateEmail($post['email'], $this->getAllUsers());

        if ( !$this->localFieldsValidate( $udata ) ) return false;

        $newUser = new Users();
        $newUser->login = $post['logname']; 
        $newUser->pass = password_hash($post['bypass'], PASSWORD_DEFAULT);
        $newUser->name = $post['firstName']; 
        $newUser->lastname = $post['lastName']; 
        $newUser->fio = $post['firstName'] . " " . $post['lastName']; 
        $newUser->thirdname = $post['thirdname']; 
        $newUser->fullFio = $post['firstName']. " " .$post['thirdname']. " " .$post['lastName']; 
        $newUser->about = $v->sanitarizePost('usernote');
        $newUser->email = $post['email']; 

        $uRoles  = [];
        $uClients = [];
        if ( isset($post['role']) ) 
            $uRoles = $this->applyUser("role", $post['role'] );
        if ( isset($post['clients']) )
            $uClients = $this->applyUser("client", $post['clients']);

        $newUser->role = json_encode($uRoles);
        $newUser->clients = json_encode($uClients);

        $newUser->permissions = json_encode([]);
        $newUser->location = '';
        $newUser->access = 0;

        $res = $newUser->save(false);
        $this->uid = $newUser->getPrimaryKey();

        return $this->uid;
    }

    public function deleteUser( int $id ) : bool
    {
        $usr = Users::find()->where(['id'=>$id]);
        if ( $usr->count() )
        {
            $usr = $usr->limit(1)->one();
            return $usr->delete();
        }
        return false;
    }
    public function accessControl() : bool
    {
        if ( User::hasPermission('Users') ) 
            return true;
        return false;
    }

}