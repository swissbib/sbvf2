<?php

namespace Swissbib\Db\Table;

use VuFind\Db\Table\Gateway;
use Zend\Db\Sql\Expression;

/**
 * Table Definition for user_localdata
 *
 * @category Swissbib
 * @package  Db_Table
 */
class UserLocalData extends Gateway
{
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct('user_localdata', 'Swissbib\Db\Row\UserLocalData');
    }



    /**
     * @param   String  $language
     * @param   Integer $user_id
     */
    public function createOrUpdateLanguage($language, $user_id) {
        $this->createOrUpdateValue('language', $language, $user_id);
    }



    /**
     * @param   String  $maxHits
     * @param   Integer $user_id
     */
    public function createOrUpdateMaxHits($maxHits, $user_id) {
        $maxHits    = intval($maxHits);

        $this->createOrUpdateValue('max_hits', $maxHits, $user_id);
    }



    /**
     * @param   string    $column
     * @param   string    $value
     * @param   string    $user_id     ID of user creating link
     */
    public function createOrUpdateValue($column, $value, $user_id) {
        $params = array(
            'user_id'   => $user_id,
            $column     => $value
        );
        $result = $this->select($params)->current();

            // Only create row if it does not already exist:
        if (empty($result)) {
            $result = $this->createRow();

            $result->user_id    = $user_id;
            $result->$column    = $value;
        }

        $result->save();
    }

}
