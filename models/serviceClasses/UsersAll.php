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
            'id','name','lastname','thirdname','fio','fullFio','role','clients','permissions',
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

	public function getAllPermissions()
    {
        if ( isset( $this->permissions ) ) return $this->permissions;

		$this->permissions = Permissions::find()->select(['id','name','description'])->asArray()->all();
        $uPermissions = json_decode($this->user['permissions'],true); //User::permissions();

        foreach ( $this->permissions as &$perm )
        {
            $perm['applied'] = 0;
            if ( array_key_exists($perm['id'], $uPermissions) )
                $perm['applied'] = 1;
        }

        return $this->permissions;
	}
    public function getPermissions() : array
    {
        if ( !isset($this->user['permissions']) ) 
            return [];

        $userPermissions = json_decode($this->user['permissions'],true);
        $permissions = Permissions::find()->select(['id','name','description'])->asArray()->all();  
        //debug($uperms ,1,1 );
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

    public function saveUserData( array $post ) : bool
    {
        $session = Yii::$app->session;
        $thisuser = Users::find()->where(['id'=>$this->uid]);
        if ( !$thisuser->exists() )
            return false;

        $thisuser = $thisuser->one();

        $v = new Validator();
        $udata = [];
        $udata['firstName'] = $v->validateString($post['firstName']);
        $udata['lastName']  = $v->validateString($post['lastName']);
        $udata['thirdName'] = $v->validateString($post['thridName']);
        $udata['logname']   = $v->validateString($post['logname']);
        $udata['email']     = $v->validateEmail($post['email']);

        $isAllValid = true;
        foreach( $udata as $field => $value )
        {
            if ( !$value ) {
                $session->setFlash($field, 'Заполнено не верено!');
                $isAllValid = false;
            };
        }
        if ( !$isAllValid ) return false;

        $usernote = $v->sanitarizePost('usernote');
        $password = password_hash($post['bypass'], PASSWORD_DEFAULT);

        $thisuser->name = $post['firstName']; 
        $thisuser->lastname = $post['lastName']; 
        $thisuser->thirdname = $post['thridName']; 
        $thisuser->fio = $post['firstName'] . " " . $post['lastName']; 
        $thisuser->fullFio = $post['firstName']. " " .$post['thridName']. " " .$post['lastName']; 
        $thisuser->email = $post['email']; 
        $thisuser->about = $usernote;
        $thisuser->login = $post['logname']; 
        $thisuser->pass = $password; 

        $uRoles = [];
        $uClients = [];
        if ( isset($post['role']) ) 
            $uRoles = $this->applyUser("role", $post['role'] );
            //$uRoles = $this->applyUser("role", json_decode( $post['role'] ));
        
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

        if ( User::hasPermission($permid) ) return false;
        //debug($thisuser->permissions,'$thisuser->permissions',1);
        $oldUP = json_decode($thisuser->permissions,true);
        $oldUP = $thisuser->permissions;
        $oldUP[] = $permid;

        $thisuser->permissions = $oldUP;
        //$thisuser->permissions = json_encode($oldUP);
        return $thisuser->save(false);
    }
    public function removeRight( int $permid ) : bool
    {
        $thisuser = $this->applyRightPrepare( $permid );
        if ( $thisuser === false ) return false;

        if ( !User::hasPermission($permid) ) return false;
        
        //$oldUP = json_decode($thisuser->permissions);
        $oldUP = $thisuser->permissions;
        foreach ( $oldUP as $key => $upID )
        {
            if ( $upID === $permid )
                unset($oldUP[$key]);
        }

        //$thisuser->permissions = json_encode($oldUP);
        $thisuser->permissions = $oldUP;
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
        $session = Yii::$app->session;
        $v = new Validator();
        $udata = [];
        $udata['firstName'] = $v->validateString($post['firstName']);
        $udata['lastName']  = $v->validateString($post['lastName']);
        $udata['thirdName'] = $v->validateString($post['thridName']);
        $udata['logname']   = $v->validateString($post['logname']);
        $udata['email']     = $v->validateEmail($post['email']);

        $isAllValid = true;
        foreach( $udata as $field => $value )
        {
            if ( !$value ) {
                $session->setFlash($field, 'Заполнено не верено!');
                $isAllValid = false;
            };
        }
        if ( !$isAllValid ) return false;

        $newUser = new Users();

        $usernote = $v->sanitarizePost('usernote');
        $password = password_hash($post['bypass'], PASSWORD_DEFAULT);

        $newUser->name = $post['firstName']; 
        $newUser->lastname = $post['lastName']; 
        $newUser->thirdname = $post['thridName']; 
        $newUser->fio = $post['firstName'] . " " . $post['lastName']; 
        $newUser->fullFio = $post['firstName']. " " .$post['thridName']. " " .$post['lastName']; 
        $newUser->email = $post['email']; 
        $newUser->about = $usernote;
        $newUser->login = $post['logname']; 
        $newUser->pass = $password;
        $newUser->role = json_encode([]);
        $newUser->clients = json_encode([]);
        $newUser->permissions = [];
        $newUser->location = '';
        $newUser->access = 0;

        $res = $newUser->save(false);
        $this->uid = $newUser->getPrimaryKey();

        return $this->uid;
    }


    public function accessControl() : bool
    {
        if ( User::hasPermission('Users') ) 
            return true;
        return false;
    }

}
