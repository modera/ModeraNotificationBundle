<?php

namespace Modera\NotificationBundle\Transport;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class InvalidUIDFormatException extends \RuntimeException
{
    /**
     * @var string
     */
    private $uid;

    /**
     * @param string $uid
     * @param string $message
     *
     * @return InvalidUIDFormatException
     */
    public static function create($uid, $message)
    {
        $me = new static($message);
        $me->uid = $uid;

        return $me;
    }

    /**
     * @return string
     */
    public function getUID()
    {
        return $this->uid;
    }
}
