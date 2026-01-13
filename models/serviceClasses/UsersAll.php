<?php
namespace app\models\serviceClasses;

use app\models\serviceTables\{Service_data,Users,Permissions};
use app\models\{User,Common};

use Yii;

class UsersAll extends Common
{
    protected array $all; 
    public array $user; 
    protected array $permissions;

    protected array $userfields;

	function __construct( int $id = null)
    {
        $this->userfields = [
            'id','name','lastname','thirdname','fio','fullFio','role','permissions',
            'location','about','email','access'];
        $this->getAllUsers();
        $this->getAllPermissions();

        if ($id > 0 && $id < PHP_INT_MAX)
            $this->getUserByID($id);

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
