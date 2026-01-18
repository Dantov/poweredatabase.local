<?php
namespace app\models;

use app\models\serviceTables\Users;
use app\models\serviceTables\Permissions;
use Yii;

class User
{
    /**
     * true если юзер не залогинился
     * @var bool
     */
    protected static bool $isGuest;

    protected static $userInstance;

    /**
     * ID юзера из таблицы
     * @var integer
     */
    protected static string $userSurname;
    protected static int $userID;
    protected static string $userFIO;
    protected static string $userFullFIO;

    /**
     * ID участков к которым принадлежит пользователь
     * @var array
     */
    protected static array $userLocations;

    /**
     * уровень доступа
     * @var integer
     */
    protected static int $userAccess;

    /**
     * Список разрешений для конкретного пользователя
     * all permission data for this user
     * @var
     */
    protected static array $permissions;

    /**
     * экземпляр General для доступа к не статик методам
     * @var $instance
     */
    protected static $instance;

    /**
     * @return array
     * @throws \Exception
     */
    protected static function userInstance() : array
    {
        if ( isset(self::$userInstance) && is_array(self::$userInstance) )
            if ( !empty(self::$userInstance) ) 
                return self::$userInstance;

        return self::init();
    }

    /**
     * PUBLIC METHODS
     */
    
    public static function getUsernameByID( int $id ) : string
    {
        if ( $id < 0 || $id > PHP_INT_MAX )
            throw new \Exception("We got no user sorry!", 510);
        $u = Users::find()->select(['id','fio'])
            ->where(['id'=>$id])
            ->asArray()
            ->one();
        return $u['fio']??'';
    }
    public static function init( int $userID=null, array $user=[] ) : array
    {
        if ( isset(self::$userInstance) && is_array(self::$userInstance) ) 
            return self::$userInstance;
        
        if ( $user ) {
            return self::$userInstance = $user;
        }
        if ( $userID < 0 || $userID > PHP_INT_MAX )
            throw new \Exception("We got no user sorry!", 510);
        
        if ( !$userID )
        {
            // Try to get user by id from Session
            $user = Yii::$app->session->get('user');
            if ( !isset($user['id']) )
                throw new \Exception("We got no user sorry!", 511);
            
            $userID = $user['id'];
        }
        
        self::$userInstance = Users::find()->where(['id'=>$userID])->asArray()->one();
        
        return self::$userInstance;
    }

    public static function permissions() : array
    {
        if ( isset(self::$permissions) ) return self::$permissions;

        $user = self::userInstance();
        $permissions = Permissions::find()->select(['id','name','description'])->asArray()->all();  
        $userPermissions = json_decode($user['permissions'],true);

        $permittedFieldAll = [];
        foreach ( $permissions as $permission )
        {
            $pID = $permission['id'];
            if ( in_array( $pID, $userPermissions ) )
                $permittedFieldAll[$pID] = $permission;
        }

        return self::$permissions = $permittedFieldAll;
    }

    /**
     * @param string $permission
     * @return bool
     * @throws \Exception
     */
    
    public static function getPermission( mixed $permission ) : array
    {
        $permissions = self::permissions();
        if ( is_string($permission) ) 
        {
            foreach ( $permissions as $sP )
                if ( $sP['name'] === $permission ) return $sP;
        }
        if ( is_int($permission) ) {
            if ( array_key_exists($permission, $permissions) )
                return $permissions[$permission];
        }
        return [];
    }
    
    public static function hasPermission( mixed $permission ) : bool
    {
        if ( is_string($permission) ) 
        {
            foreach ( self::permissions() as $sP )
                if ( $sP['name'] === $permission ) return true;
        }
        if ( is_int($permission) ) {
            return array_key_exists($permission, self::permissions());
        }
        return false;
    }
    public static function getClientsID() : array
    {
        return json_decode(self::$userInstance['clients'],true);
    }
    public static function getClients() : array
    {
        $permissions = self::permissions();
        $uClientsID = json_decode(self::$userInstance['clients'],true);

        $clientPerm = [];
        foreach ( $permissions as $permission ) 
        {
            if ( in_array($permission['id'], $uClientsID) )
            {
                $clientPerm[ $permission['id'] ] = $permission;
            } 
        }
        return $clientPerm;
    }

    /**
     * @throws \Exception
     */
    public static function isGuest() : bool
    {
        if ( isset(self::$isGuest) ) return self::$isGuest;

        return self::$isGuest = !self::getAccess() ? true : false;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public static function getID() : int
    {
        if ( isset( self::$userID ) ) return self::$userID;

        $user = self::userInstance();
        return self::$userID = (int)$user['id'];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function getSurname() : string
    {
        if ( isset( self::$userSurname ) ) return self::$userSurname;

        self::$userSurname = explode(' ', self::getFIO())[0];
        return self::$userSurname;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function getFIO() : string
    {
        if ( isset( self::$userFIO ) ) return self::$userFIO;

        $user = self::userInstance();
        return self::$userFIO = $user['fio'];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function getFullFIO() : string
    {
        if ( isset( self::$userFullFIO ) ) return self::$userFullFIO;

        $user = self::userInstance();
        return self::$userFullFIO = $user['fullFio'];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getLocations() : array
    {
        if ( isset( self::$userLocations ) ) return self::$userLocations;
        $user = self::userInstance();

        //return self::$userLocations = explode(',',$user['location']);
        return self::$userLocations = json_decode($user['location']);
    }

    /**
     * @return int
     * @throws \Exception
     */
    public static function getAccess() : int
    {
        if ( isset( self::$userAccess ) ) 
            return self::$userAccess;

        $user = self::userInstance();
        return self::$userAccess = (int)$user['access'];
    }

    /**
     * @return string
     */
    public static function getIp() : string
    {
        if ( filter_has_var(INPUT_SERVER, 'REMOTE_ADDR') )
        {
            return filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        }
        return '';
    }

}