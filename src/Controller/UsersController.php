<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Mailer\Email;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{

    /**
     * beforeFilter method
     * 
     * @param Event $event
     * @return Auth
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        //Cho phép thêm, đăng xuất và xem trang check
        $this->Auth->allow(['add', 'logout', 'check',]);
    }

    /**
     * Index method
     * 
     * @return null
     */
    public function index()
    {
        $this->set('users', $this->Users->find('all'));
    }

    /**
     * View method
     * 
     * @param $id Users id
     * @return null
     */
    public function view($id)
    {
        $user = $this->Users->get($id);
        $this->set(compact('user'));
    }

    /**
     * Check method to do verify account
     * 
     * @return null
     */
    public function check()
    {
        $this->set(compact('user'));
    }

    /**
     * Send email method
     * 
     * @return $email
     */
    public function sendEmail()
    {
        $email = new Email();
        $email
            ->from('minhphamcong2510@gmail.com')
            ->to('hvu41111@gmail.com')
            ->subject('Verify your account')
            ->message();
        $content = "Your account has been registered, Click link to verify: ";
        $content .= "http://192.168.56.56:8080/cakephp/blog/user/login";
        $content .= "\n Username: " . $this->request->data['username'];
        $content .= "\n Password: " . $this->request->data['password'];
        return $email->send($content);
    }

    /**
     * Add method
     * 
     * @return Redirect on successful
     */
    public function add()
    {
        $user = $this->Users->newEntity();
        //Check request
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->data);
            //Check validation
            if (!$user->errors()) {
                /* @var $sendEmail type */

                //Check save user: redirect check on successful
                if ($this->Users->save($user)) {
                    $this->sendEmail();
                    $this->Flash->success(__('Registered!'));
                    return $this->redirect(['action' => 'check']);
                }
                $this->Flash->error(__('Unable to add the user.'));
            }
        }
        $this->set('user', $user);
    }

    /**
     * Change password method
     * 
     * @return Redirect on successful
     */
    public function change()
    {
        $user = $this->Users->get($this->Auth->user('id'));
        //Check old password and method request
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, [
                'oldpassword' => $this->request->data['oldpassword'],
                'password' => $this->request->data['newpassword'],
                'newpassword' => $this->request->data['newpassword']
                ], ['validate' => 'password']
            );
            if ($this->Users->save($user)) {
                $this->Flash->success(__('New password has been saved.'));
                return $this->redirect(['controller' => 'Articles', 'action' => 'index']);
            } else {
                $this->Flash->error(__('The password could not be saved. Please, try again.'));
            }
        }
    }

    /**
     * Delete method
     * 
     * @param $id Users id
     * @return null, redirect on successful
     */
    public function delete($id = null)
    {
        $this->request->allowMethod('post', 'delete');
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('User has been deleted'));
        } else {
            $this->Flash->error(__('Could not deleted user'));
        }
        return $this->redirect(['action' => 'index']);
    }

    /**
     * Login method
     * 
     * @return Redirect on successful
     */
    public function login()
    {
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->setUser($user);
                return $this->redirect(['controller' => 'Articles', 'action' => 'index']);
            }
            $this->Flash->error(__('Invalid username or password, try again'));
        }
    }

    /**
     * Logout method
     * 
     * @return Redirect on successful
     */
    public function logout()
    {
        $this->Flash->success('You has been logged out.');
        return $this->redirect($this->Auth->logout());
    }

    /**
     * IsAuthorized method
     * 
     * @param $user Users user
     * @return boolean
     */
    public function isAuthorized($user)
    {
        // user login can view and change user
        if (in_array($this->request->action, ['view', 'change', 'delete'])) {
            if ($this->Users->isOwnedBy($user['id'])) {
                return true;
            }
        }

        return parent::isAuthorized($user);
    }
}
