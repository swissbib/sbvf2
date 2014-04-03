<?php

namespace Swissbib\TargetsProxy;

use Swissbib\TargetsProxy\Exception;

/**
 * IpMatcher - detect whether IP address matches to patterns and ranges of IP addresses
 * Using IPv4 notation
 *
 *         Type: single    - regular IP address                  -    ex: '127.0.0.1'
 *      Type: wildcard    - one or more digits use placeholders - ex: '172.0.0.*'
 *      Type: mask        - two IP addresses separated by slash - ex: '126.1.0.0/255.255.0.0'
 *        Type: range     - two IP addresses separated by minus -    ex: '125.0.0.1-125.0.0.9'
 *
 * Usage:
 *     $patterns    = array('172.0.*.*', '126.1.0.0/255.255.0.0')
 *     $matching    = new IpMatcher()->isMatching('126.1.0.2', $patterns);
 *
 *     Result:     true
 */
class IpMatcher
{
    /**
     * @var string
     */
    private static $IP_PATTERN_TYPE_SINGLE = 'single';

    /**
     * @var string
     */
    private static $IP_PATTERN_TYPE_WILDCARD = 'wildcard';
    /**
     * @var string
     */
    private static $IP_PATTERN_TYPE_MASK = 'mask';
    /**
     * @var string
     */
    private static $IP_PATTERN_TYPE_RANGE = 'range';

    /**
     * @var array
     */
    private $allowedIps = array();



    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * Check whether given IP address matches any of the allowed IP address patterns
     *
     * @throws    Exception
     * @param    String    $ipAddress
     * @param    Array    $patterns        Array of allow-patterns, possible types: IP / IP wildcard / IP mask / IP range
     * @return    Boolean
     */
    public function isMatching($ipAddress, array $patterns = array())
    {
        foreach($patterns as $ipPattern)
        {
            $type    = $this -> detectIpPatternType($ipPattern);
            if( !$type ) {
                throw new Exception('Invalid IP Pattern: ' . $ipPattern);

            }
            $subRst = call_user_func(array($this, 'isIpMatching' . ucfirst($type)), $ipPattern, $ipAddress);

            if ($subRst)
            {        // True if the address matches any one pattern
                return true;
            }
        }

        return false;
    }

    /**
     * Detect type of given IP "description" (IP address / IP wildcard / IP mask / IP range)
     *
     * @param    String    $ip
     * @return    Boolean|String
     */
    private function detectIpPatternType($ip)
    {
        if (strpos($ip, '*'))
        {
            return self::$IP_PATTERN_TYPE_WILDCARD;
        }

        if (strpos($ip, '/'))
        {
            return self::$IP_PATTERN_TYPE_MASK;
        }

        if (strpos($ip, '-'))
        {
            return self::$IP_PATTERN_TYPE_RANGE;
        }

        if (ip2long($ip))
        {
            return self::$IP_PATTERN_TYPE_SINGLE;
        }

        return false;
    }

    /**
     * Check whether the given IP address matches the given IP address
     *
     * @param    String    $allowedIp
     * @param    String    $ip
     * @return    Boolean
     */
    private function isIpMatchingSingle($allowedIp, $ip)
    {
        return (ip2long($allowedIp) == ip2long($ip));
    }

    /**
     * Check whether the given IP address wildcard matches the given IP address
     *
     * @param    String    $allowedIp
     * @param    String    $ip
     * @return    Boolean
     */
    private function isIpMatchingWildcard($allowedIp, $ip)
    {
        $allowedIpArr    = explode('.', $allowedIp);
        $ipArr            = explode('.', $ip);

        for($i = 0; $i < count($allowedIpArr); $i++)
        {
            if ($allowedIpArr[$i] == '*')
            {
                return true;
            }
            else
            {
                if (false == ($allowedIpArr[$i] == $ipArr[$i]))
                {
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * Check whether the given IP address mask matches the given IP address
     *
     * @param    String    $allowedIp
     * @param    String    $ip
     * @return    Boolean
     */
    private function isIpMatchingMask($allowedIp, $ip)
    {
        list($allowedIpIp, $allowedIpMask) = explode('/', $allowedIp);

        $begin    = (ip2long($allowedIpIp) &   ip2long($allowedIpMask)) + 1;
        $end    = (ip2long($allowedIpIp) | (~ip2long($allowedIpMask))) + 1;
        $ip     = ip2long($ip);

        return ($ip >= $begin && $ip <= $end);
    }

    /**
     * Check whether the given IP address range includes the given IP address
     *
     * @param    String    $allowedIp
     * @param    String    $ip
     * @return    Boolean
     */
    private function isIpMatchingRange($allowedIp, $ip)
    {
        list($begin, $end) = explode('-', $allowedIp);

        $begin    = ip2long($begin);
        $end    = ip2long($end);
        $ip        = ip2long($ip);

        return ($ip >= $begin && $ip <= $end);
    }

}

?>