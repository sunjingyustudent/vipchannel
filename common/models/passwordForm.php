<?php
namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class PasswordForm extends Model
{
    public $password;
    public $token;
    private $user;

    public function getPasswordResetToken()
    {
        $userData = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'id' => Yii::$app->user->id,
        ]);

        if (!$userData) {
            return 0;
        }

        if (!User::isPasswordResetTokenValid($userData->password_reset_token)) {
            $userData->generatePasswordResetToken();
        }

        if (!$userData->save()) {
            return 0;
        }

        return $userData->password_reset_token;
    }

    public function changePassword()
    {
        if (empty($this->token) || !is_string($this->token)) {
            return false;
        }

        $this->user = User::findByPasswordResetToken($this->token);
        if (!$this->user) {
            return false;
        }

        $user = $this->user;
        $user->setPassword($this->password);
        $user->auth_key = Yii::$app->security->generateRandomString(32);
        $user->removePasswordResetToken();

        return $user->save(false);
    }
}
