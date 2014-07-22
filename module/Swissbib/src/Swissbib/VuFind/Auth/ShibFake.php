<?php




namespace Swissbib\VuFind\Auth;


use VuFind\Auth\AbstractBase;
use VuFind\Exception\Auth as AuthException;
use Zend\Crypt\Password\Bcrypt;



class ShibFake extends  AbstractBase {

    /**
     * Username
     *
     * @var string
     */
    protected $username;

    /**
     * Password
     *
     * @var string
     */
    protected $password;




    /**
     * Attempt to authenticate the current user.  Throws exception if login fails.
     *
     * @param \Zend\Http\PhpEnvironment\Request $request Request object containing
     * account credentials.
     *
     * @throws AuthException
     * @return \VuFind\Db\Row\User Object representing logged-in user.
     */
    public function authenticate($request)
    {
        // Make sure the credentials are non-blank:
        $this->username = trim($request->getPost()->get('username'));
        $this->password = trim($request->getPost()->get('password'));
        if ($this->username == '' || $this->password == '') {
            throw new AuthException('authentication_error_blank');
        }

        // Validate the credentials:
        $user = $this->getUserTable()->getByUsername($this->username, false);
        if (!is_object($user) || !$this->checkPassword($this->password, $user)) {
            throw new AuthException('authentication_error_invalid');
        }

        // If we got this far, the login was successful:
        return $user;
    }



    /**
     * Check that the user's password matches the provided value.
     *
     * @param string $password Password to check.
     * @param object $userRow  The user row.  We pass this instead of the password
     * because we may need to check different values depending on the password
     * hashing configuration.
     *
     * @return bool
     */
    protected function checkPassword($password, $userRow)
    {
        // Special case: hashing enabled:
        if ($this->passwordHashingEnabled()) {
            if ($userRow->password) {
                throw new \VuFind\Exception\PasswordSecurity(
                    'Unexpected unencrypted password found in database'
                );
            }

            $bcrypt = new Bcrypt();
            return $bcrypt->verify($password, $userRow->pass_hash);
        }

        // Default case: unencrypted passwords:
        return $password == $userRow->password;
    }

    /**
     * Is password hashing enabled?
     *
     * @return bool
     */
    protected function passwordHashingEnabled()
    {
        $config = $this->getConfig();
        return isset($config->Authentication->hash_passwords)
            ? $config->Authentication->hash_passwords : false;
    }


}