<?php
namespace Swissbib\Libadmin;

/**
 * Synchronization result with messages and status flag
 *
 */
class Result
{

    /**
     * Result types
     */
    const SUCCESS = 1;

    const INFO = 2;

    const ERROR = 3;

    /**
     * @var    Array    Type labels
     */
    protected $labels = array(
        1 => 'Success',
        2 => 'Info',
        3 => 'Error'
    );

    /**
     * @var    Bool    Was sync successful?
     */
    protected $success = true;

    /**
     * @var    Array    Messages
     */
    protected $messages = array();



    /**
     * Reset result
     */
    public function reset()
    {
        $this->messages = array();
        $this->success  = true;
    }



    /**
     * Add a new message
     *
     * @param    Integer        $type
     * @param    String         $message
     */
    public function addMessage($type, $message)
    {
        $this->messages[] = array(
            'type'    => (int)$type,
            'message' => $message
        );
    }



    /**
     * Add an error
     *
     * @param    String        $message
     * @return    Result        $this
     */
    public function addError($message)
    {
        $this->addMessage(self::ERROR, $message);

        $this->success = false;

        return $this;
    }



    /**
     * Add an info
     *
     * @param    String        $message
     * @return    Result        $this
     */
    public function addInfo($message)
    {
        $this->addMessage(self::INFO, $message);

        return $this;
    }



    /**
     * Add a success
     *
     * @param    String        $message
     * @return    Result        $this
     */
    public function addSuccess($message)
    {
        $this->addMessage(self::SUCCESS, $message);

        return $this;
    }



    /**
     * Check whether import was successful
     *
     * @return    Boolean
     */
    public function isSuccess()
    {
        return $this->success;
    }



    /**
     * Check whether import had errors
     *
     * @return    Boolean
     */
    public function hasErrors()
    {
        return !$this->success;
    }



    /**
     * Get all plain messages
     *
     * @return    Array
     */
    public function getMessages()
    {
        return $this->messages;
    }



    /**
     * Get list of formatted (prefixed with status) messages
     *
     * @return    String[]
     */
    public function getFormattedMessages()
    {
        $messages = array();

        foreach ($this->messages as $message) {
            $messages[] = $this->labels[$message['type']] . ': ' . $message['message'];
        }

        return $messages;
    }
}
