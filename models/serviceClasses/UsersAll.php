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
        $this->getAllPermissions();
        

        if ($id > 0 && $id < PHP_INT_MAX)
        {
            $this->uid = $id;
            $this->getUserByID($this->uid);
            
        }

        parent::__construct();
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
        $uPermissions = User::permissions();

        foreach ( $this->permissions as &$perm )
        {
            $perm['applied'] = 0;
            if ( array_key_exists($perm['id'], $uPermissions) )
                $perm['applied'] = 1;
        }

        return $this->permissions;
	}

    public function saveUserData( array $post ) : bool
    {
        //$thisuser = Users::find($this->uid);
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
        $thisuser->about = $post['usernote'];
        $thisuser->role = $this->applyRoles($post['role']);

        $thisuser->login = $post['logname']; 
        $thisuser->pass = $password; 

        $thisuser->save(false);

        return $res;
    }
    
    protected function applyRoles( array $roles ) : string
    {
        $allRoles = Service_data::find()->where(['tab'=>'role'])->asArray()->all();

        $validRoles = [];
        foreach( $roles as $roleID )
        {
            foreach( $allRoles as $singRole )
                if ( (int)$singRole['id'] === (int)$roleID ) 
                    $validRoles[] = $singRole['id'];
        }
        
        return json_encode($validRoles);
    }

    public function applyRight( array $post ) : string //bool
    {
        return "right was applied";
    }
    public function removeRight( array $post ) : string //bool
    {
        return "right was removed";
    }


    public function accessControl() : bool
    {
        if ( User::hasPermission('Users') ) 
            return true;
        return false;
    }

}
