<?php
namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $type;
    public $rememberMe = true;

    private $user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $userData = $this->getUser();
            $pos = strpos($userData->type, $this->type);
            if ($pos !== false) { //验证是否用户和账号是匹配的
                $this->saveAuthKey($userData->auth_key);
                return Yii::$app->user->login($userData, $this->rememberMe ? 3600 * 24 * 30 : 0);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->user === null) {
            $this->user = User::findByUsername($this->username);
        }
        return $this->user;
    }

    public function saveAuthKey($authKey = '')
    {
        $session = Yii::$app->session;
        $session->set('auth_key', $authKey);
    }
}
