<?php

namespace Modera\NotificationBundle\Model;

/**
 * By relying on this interface when working with notifications you are leaving yourself a possibility later
 * if it is needed to switch to another persistence layer without modifying your code, which is highly recommended.
 *
 * Usually you won't need to modify state of notification directly but instead rely on NotificationService for that.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2015 Modera Foundation
 */
interface NotificationInterface
{
    const STATUS_NOT_READ = 0;
    const STATUS_READ = 1;

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @return int
     */
    public function getStatus();

    /**
     * Optional technical details associated with notification.
     *
     * @return array
     */
    public function getMeta();
}
