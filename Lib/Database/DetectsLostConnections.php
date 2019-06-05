<?php

trait Database_DetectsLostConnections
{
    protected function causedByLostConnection(Throwable $e)
    {
        $message = $e->getMessage();

        $needles = [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
            'Transaction() on null',
            'child connection forced to terminate due to client_idle_limit',
            'query_wait_timeout',
            'reset by peer',
            'Physical connection is not usable',
            'TCP Provider: Error code 0x68',
            'Name or service not known',
        ];

        foreach ($needles as $needle) {
            if ($needle !== '' && mb_strpos($message, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}