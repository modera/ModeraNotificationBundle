<?php

namespace Modera\NotificationBundle\Model;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2015 Modera Foundation
 */
interface NotificationInterface
{
    /**
     * @return string
     */
    public function getContents();
}