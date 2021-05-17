<?php

namespace Cron\Test\Cron;

class Test
{

    public function execute()
    {

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron88.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('1');

        return $this;

    }
}
