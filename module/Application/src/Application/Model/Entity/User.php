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

    const USER_SUPER_ADMIN  = 'super-admin';
    const USER_ADMIN        = 'admin';
    const USER_USER         = 'user';
    const USER_GUEST        = 'guest';
    /**
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @Column(type="string")
     */
    protected $uname;

    /**
     * @Column(type="string")
     */
    protected $upass;

    /**
     * @Column(type="string")
     */
    protected $email;

    /**
     * @Column(type="string")
     */
    protected $role = 'guest';

    /**
     * @Column(type="datetime", name="reg_date")
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

    /**
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->role = $role;
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
    public function generateRandomPassword()
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
        $this->setUpass($generatedPassword);
        return $generatedPassword;

    }

    public function getRoleOptions()
    {
        return [
            self::USER_SUPER_ADMIN => self::USER_SUPER_ADMIN,
            self::USER_ADMIN => self::USER_ADMIN,
            self::USER_USER => self::USER_USER,
            self::USER_GUEST => self::USER_GUEST
        ];
    }
}
