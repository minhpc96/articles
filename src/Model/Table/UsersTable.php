<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Auth\DefaultPasswordHasher;

class UsersTable extends Table
{

    /**
     * validation data when insert into Users Table
     * 
     * @param Validator $validator
     * @return $validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator = new Validator();
        $validator
            //check empty of username, password, role
            ->notEmpty('username', 'A username is required')
            ->notEmpty('password', 'A password is required')
            ->notEmpty('role', 'A role is required')
            //check length of username
            ->add('username', [
                'minLength' => [
                    'rule' => ['minLength', 8],
                    'message' => 'The username need more 8 charater '
                ],
                'maxLength' => [
                    'rule' => ['maxLength', 16],
                    'message' => 'The username is too long (max 16 charater)'
                ]
            ])
            //check unique of username
            ->add('username', ['unique' => [
                    'rule' => 'validateUnique',
                    'provider' => 'table',
                    'message' => 'The user is exists'
                ],
            ])
            //Check length of password and check type pass
            ->add('password', [
                'minLength' => [
                    'rule' => ['minLength', 8],
                    'message' => 'Too short! (Min is 8)'
                ],
                'ruleName' => [
                    'rule' => ['custom', '(^(?=.*?[a-z])(?=.*?[0-9]))'],
                    'message' => 'So simple! Password need both word and number'
                ]
            ])
            //check type of role
            ->add('role', 'inList', [
                'rule' => ['inList', ['admin', 'author']],
                'message' => 'Please enter a valid role'
            ])
        ;

        return $validator;
    }

    /**
     * Validation data when change password
     * 
     * @param $validator Validator
     * @return $validator
     */
    public function validationPassword(Validator $validator)
    {
        $validator
            //Check old password
            ->add('oldpassword', 'custom', [
                'rule' => function($value, $context) {
                    $user = $this->get($context['data']['id']);
                    if ($user) {
                        if ((new DefaultPasswordHasher)->check($value, $user->password) || $user->password == $context['data']['oldpassword']) {
                            return true;
                        }
                    }
                    return false;
                },
                'message' => 'Not correct'
            ])
            ->notEmpty('oldpassword')
            //Check new password
            ->add('newpassword', [
                'minLength' => [
                    'rule' => ['minLength', 8],
                    'message' => 'Too short! (Min is 8)'
                ],
                'ruleName' => [
                    'rule' => ['custom', '(^(?=.*?[a-z])(?=.*?[0-9]))'],
                    'message' => 'So simple! Password need both word and number'
                ]
            ])
            ->notEmpty('newpassword')
        ;
        return $validator;
    }

    /**
     * is owned by method
     * 
     * @param $userId
     * @return bool
     */
    public function isOwnedBy($userId)
    {
        return $this->exists(['id' => $userId]);
    }
}
