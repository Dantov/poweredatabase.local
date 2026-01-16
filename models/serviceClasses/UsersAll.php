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
        $udata['lastName'] = $v->validateString($post['lastName']);
        $udata['thirdName'] = $v->validateString($post['thridName']);
        $udata['logname'] = $v->validateString($post['logname']);
        $udata['email'] = $v->validateEmail($post['email']);

        $usernote = $v->sanitarizePost('usernote');

        $password = password_hash($post['bypass'], PASSWORD_DEFAULT);

        //Role
        switch ( (int)$post['role'] )
        {
            case 2:
            $role = "3D Modeller";
            break;
            case 3:
            break;
            case 4:
            break;
        }

        $thisuser->name = $post['firstName']; 
        $thisuser->lastname = $post['lastName']; 
        $thisuser->thirdname = $post['thridName']; 
        $thisuser->email = $post['email']; 
        $thisuser->about = $post['usernote'];
        $thisuser->role = (int)$post['role'];

        $thisuser->login = $post['logname']; 
        $thisuser->pass = $password; 

        $thisuser->save(false);

        return "right was applied";
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
