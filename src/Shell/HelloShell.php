<?php
namespace App\Shell;

use Cake\Console\Shell;

class HelloShell extends Shell
{

    public function main()
    {
        $this->out('Hello world.');
    }

    public function heyYou($name)
    {
        $this->out('Hello ' . $name);
    }

    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Users');
    }

    public function show()
    {
        if (empty($this->args[0])) {
            // Use error() before CakePHP 3.2
            return $this->error('Please enter a username.');
        }
        $user = $this->Users->findByUsername($this->args[0])->first();
        $this->out(print_r($user, true));
    }
}
