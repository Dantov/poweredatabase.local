<?php
/**
 * Date: 05.01.2021
 * Time: 22:22
 */
namespace app\models;


class Validator
{

    protected static $lastError = '';
    protected static $errors = [];

    protected $fieldRules = [];

    protected $badChars = ['\'', '.', ',', '\\', '/', '"', '%','&','?','*','|','^', '<', '>', ':',';','`','+',' '];

    /**
     * Table name bad chars
     * @var array
     */
    private $tnbc = ['\'','"', ',', '\\','/', '|', '<', '>','+','?','&','*','(',')','{','}',':',';','^','`'];

    protected string $pattern = "/^[_a-z0-9-+]+(\.[_a-z0-9-+]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

    /**
     * Validator constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        
    }

    public function getAllErrors()
    {
        return self::$errors;
    }
    public function getLastError()
    {
        return self::$lastError;
    }
    /**
     * Удаляем всю инф. об ошибках
     * что бы валидировать новые поля
     * @return bool
     */
    public function reset() : bool
    {
        self::$lastError = '';
        self::$errors = [];
        if ( empty(self::$lastError) && empty(self::$errors) )
            return true;

        return false;
    }
    /**
     * Описаны возможные правила
     * @param string $ruleName
     * @param string $field
     * @param string $value
     * @return array
     */
    protected function rulesErrorText(  string $ruleName, string $field, string $value )
    {
        $rules = [
            'min' => "Значение поля '" . $field . "' не должно быть меньше " . $value . ". ",
            'max' => "Значение поля '" . $field . "' не должно быть больше " . $value . ". ",
            'minLength' => "Кол-во символов в поле '" . $field . "' не должно быть меньше " . $value . ". ",
            'maxLength' => "Кол-во символов в поле '" . $field . "' не должно быть больше " . $value . ". ",
            'required' => "Поле '" . $field . "' обязательно к заполнению. ",
            'readonly' => "Поле '" . $field . "' нельзя изменять. ",
            'int' => "Значение поля '" . $field . "' должно быть целое число. ",
            'unsigned' => "Значение поля '" . $field . "' не может быть отрицательным. ",
            'double' => "Значение поля '" . $field . "' должно быть дробное число. ",
            'forbiddenChars' => "Значение поля '" . $field . "' содержит не допустимые символы. ",
            'acceptedChars' => "Значение поля '" . $field . "' содержит не . ",
        ];

        if ( array_key_exists($ruleName, $rules) )
            return $rules[$ruleName];

        return $rules;
    }


    protected function baseValidate( string $str ) : string
    {
        $str = trim($str);
        $str = strip_tags($str);
        return $str;
    }

    public function validate( $fieldValue, array $rules ) : bool
    {
        foreach( $rules as $rule )
        {

        }
        switch ($rule)
        {
            case "required":
                {
                    if ( empty($fieldValue) )
                    $this->setErrorText($rule, $rules['name'], $value);
                } break;
            case "double":
                {
                    $fieldValue = (double)$fieldValue;
                } break;
            case "int":
                {
                    $fieldValue = (int)$fieldValue;
                } break;
            case "unsigned":
                {
                    if ( $fieldValue < 0 )
                        $this->setErrorText($rule, $rules['name'], $value);
                } break;
            case "min":
                {
                    if ( $fieldValue < $value )
                        $this->setErrorText($rule, $rules['name'], $value);
                } break;
            case "max":
                {
                    if ( $fieldValue > $value )
                        $this->setErrorText($rule, $rules['name'], $value);
                } break;
            case "minLength":
                {
                    if ( mb_strlen($fieldValue) < $value )
                        $this->setErrorText($rule, $rules['name'], $value);
                } break;
            case "maxLength":
                {
                    if ( mb_strlen($fieldValue) > $value )
                        $this->setErrorText($rule, $rules['name'], $value);
                } break;
            case "forbiddenChars":
                {
                    // проверить каждый символ поля
                    $symbols = $arrChars = preg_split('//u',$fieldValue,-1,PREG_SPLIT_NO_EMPTY);
                    foreach ( $symbols as $symbol )
                    {
                        if ( in_array($symbol, $this->badChars) )
                        {
                            $this->setErrorText($rule, $rules['name'], $value);
                            break;
                        }
                    }
                } break;
        }

        return true;
    }
    public function sanitarizePost( string $postname) : string
    {
        return filter_input(INPUT_POST, $postname, FILTER_SANITIZE_SPECIAL_CHARS);
    }
    public function validateEmail( string $string) : bool
    {
        //$string = filter_input(INPUT_POST, $string, FILTER_SANITIZE_SPECIAL_CHARS);
        
        if ( !preg_match($this->pattern, $string) )
            return false;
        
        return true;
    }
    public function validateString( string $string) : bool
    {
        //[-a-zA-Z0-9_
        //$string = filter_input(INPUT_POST, $string, FILTER_SANITIZE_SPECIAL_CHARS);
        $symbols = preg_split('//u',$string,-1,PREG_SPLIT_NO_EMPTY);
        foreach ( $symbols as $key => $symbol )
        {
            if ( in_array($symbol, $this->badChars) )
                return false;
        }
        return true;
    }
    public function validateLogin( string $loginName) : string
    {
        $login = filter_input(INPUT_POST, $loginName, FILTER_SANITIZE_SPECIAL_CHARS);
        //$userData['login'] = trim( htmlentities($userData['login']) );
        return $login;
    }
    public function validatePassword( string $passwordName) : string
    {
        return filter_input(INPUT_POST, $passwordName, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    public function validateFileName($name)
    {
        $name = $this->baseValidate($name);
        $symbols = preg_split('//u',$name,-1,PREG_SPLIT_NO_EMPTY);

        foreach ( $symbols as $key => $symbol )
        {
            if ( in_array($symbol, $this->badChars) )
            {
                unset($symbols[$key]);
            }
        }

        $str = implode('',$symbols);
        if (mb_strlen($str) > 25) {
            $str = mb_substr($str,0,24);
        }
        return $str;
    }

    private function setErrorText( string $rule, string $ruleName, $value )
    {
        self::$lastError = $this->rulesErrorText($rule, $ruleName, $value);
        self::$errors[] = self::$lastError;
    }

    public function validateTableName( string $tName ) : bool
    {
        $tName = $this->baseValidate($tName);

        // проверить каждый символ поля
        $symbols = preg_split('//u',$tName,-1,PREG_SPLIT_NO_EMPTY);

        foreach ( $symbols as $symbol )
        {
            if ( in_array($symbol, $this->tnbc) )
                return false;
        }

        return true;
    }

    /** Search Input Trusted Chars */
    protected array $sitc = [];

    public function searchInputTrustedChars() : array
    {
        
        $res[] = chr(45);
        for ( $i = 48; $i <= 122; $i++ )
        {
            if ( $i === 58 ) $i = 65;  
            if ( $i === 91 ) $i = 97;
            $res[] = chr($i);
        }
        return $res;
        debug($res,'res',1);
        //if ( !empty(self::$sitc) ) return self::$sitc;

        for ( $i = 1025; $i <= 1111; $i++ )
        {
            $charsMB[$i] = mb_chr($i,'utf-8');
        }
        debug($charsMB,'charsMB',1);
        for ( $i = 48; $i <= 122; $i++ )
        {
            if ( $i === 58 ) $i = 65;
            if ( $i === 91 ) $i = 97;
            $charASCII[] = chr($i);
        }
        debug($charASCII,'charsASCII',1);
        $needs = [33,35,36,38,40,41,42,43,45,58,59,64,124];
        foreach( $needs as $need )
        {
            $restChars[] = chr($need);
        }
        //debug($restChars,'restChars',1);
        return $this->sitc = [...$restChars, ...$charASCII, ...$charsMB];
    }
    public function filterSearchInput( string $sinput ) : string
    {
        $sitc = $this->searchInputTrustedChars();
        $sinput_chars = mb_str_split($sinput, 1,"utf-8");
        
        //debug($sinput_chars,'$sinput_chars',1);
        
        foreach($sinput_chars as $k => $char)
        {
            if ( !in_array($char, $sitc) )
                unset($sinput_chars[$k]);
        }
        return implode('',$sinput_chars);
    }
    

}