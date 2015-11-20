<?php

namespace Application\Model\Entity;
use Application\Model\PasswordAwareInterface;
use Zend\Crypt\Password\PasswordInterface;

/**
 * Class User
 * @Entity(repositoryClass="\Application\Model\UserRepository") @Table(name="users")
 */
class User implements PasswordAwareInterface
{
    const PASS_MIN_LENGTH = 8;
    const PASS_MAX_LENGTH = 255;

    const USER_SUPER_ADMIN  = 1;//'super-admin'
    const USER_ADMIN        = 2;//'admin'
    const USER_USER         = 3;//'user'
    const USER_GUEST        = 4;//'guest'
    /**
     * @Id @GeneratedValue @Column(type="integer", options={"unsigned": true})
     */
    protected $id;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $uname;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $upass;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $email;

    /**
     * @Column(type="integer", options={"default": 4})
     */
    protected $role = 4;

    /**
     * @Column(type="datetime", name="reg_date", nullable=true)
     */
    protected $regDate;

    /**
     * @var PasswordInterface
     */
    protected $passwordAdapter;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUname()
    {
        return $this->uname;
    }

    /**
     * @param mixed $uname
     */
    public function setUname($uname)
    {
        $this->uname = $uname;
    }

    /**
     * @return mixed
     */
    public function getUpass()
    {
        return $this->upass;
    }

    /**
     * @param mixed $upass
     */
    public function setUpass($upass)
    {
        $this->upass = $this->hashPassword($upass);
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    public function getRoleName()
    {
        if($this->role)
            return $this->getRoleOptions()[$this->role];
        else
            return null;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    public function setRoleFromName($roleName)
    {
        $this->role = array_flip($this->getRoleOptions())[$roleName];
    }

    /**
     * @param bool|string $format Values:
     *      false - return the raw date from the database;
     *      true - return the date formatted as d-m-Y
     *      string - return the date formatted using the string assigned
     * @return null|string
     */
    public function getRegDate($format = false)
    {
        if($format && ($this->regDate instanceof \DateTime) && ($format === true || is_string($format))){
            if($format === true)
                $format = 'd-m-Y';

            $date = $this->regDate->format($format);

        }else{
            $date = $this->regDate;
        }
        return $date;
    }

    /**
     * @param mixed $regDate
     */
    public function setRegDate($regDate = null)
    {
        if(empty($regDate)){
            $regDate = new \DateTime();
        }
        $this->regDate = $regDate;
    }

    public function checkPassword($password)
    {
        return $this->getPasswordAdapter()->verify($password, $this->getUpass());
    }

    public function setPasswordAdapter(PasswordInterface $adapter)
    {
        $this->passwordAdapter = $adapter;
    }

    public function getPasswordAdapter()
    {
        return $this->passwordAdapter;
    }

    public function hashPassword($password)
    {
        return $this->getPasswordAdapter()->create($password);
    }

    /**
     * Generate random password.
     * Specifications: 8 chars; 1 Upper case letter + 6 letters + 1 number
     * v_todo - keep this updated when creating/updating user password
     */
    public static function generateRandomPassword()
    {
        $letters = 'abcdefghijklmnopqrstuvwxyz';
        $min = 0; $max = strlen($letters)-1;

        //generate one random uppercase letter
        $upper = strtoupper($letters[mt_rand($min, $max)]);

        //generate 6 random letters
        $lower = '';
        for($i=1; $i<=(self::PASS_MIN_LENGTH-2); $i++){
            $lower .= $letters[mt_rand($min, $max)];
        }

        //generate one random short number
        $number = mt_rand(1, 9);
        $generatedPassword = $upper.$lower.$number;
        return $generatedPassword;

    }

    public static function getRoleOptions()
    {
        return [
            self::USER_SUPER_ADMIN => 'super-admin',//v_todo - create a separate Database table for user role names
            self::USER_ADMIN => 'admin',
            self::USER_USER => 'user',
            self::USER_GUEST => 'guest'
        ];
    }

    //returns only options that are allowed to be edited by the user with the current role
    public function getAllowedRoleOptions()
    {
        $options = [];
        if($this->getRole() !== null){//if we have an assigned user
            foreach(self::getRoleOptions() as $k => $option){
                if($k >= $this->getRole()){
                    $options[$k] = $option;
                }
            }
        }
        return $options;
    }

    /**
     * Check if the editor user has editing rights on to the edited user
     * @param int|null|bool $editedRole
     * @return bool
     */
    public function canEdit($editedRole)
    {
        if(!is_numeric($editedRole) && !is_null($editedRole) && !is_bool($editedRole))
            throw new \InvalidArgumentException('Wrong argument type');

        $editorRole = $this->getRole();

        $editorRole = (int) $editorRole;
        $editedRole = (int) $editedRole;

        if(!$editorRole) return false;
        if(!$editedRole) return true;

        return $editorRole <= $editedRole;
    }
}
