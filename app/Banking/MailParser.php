<?php

namespace App\Banking;

use BeyondCode\Mailbox\InboundEmail;

interface MailParser
{
    /**
     * Parse inbound email as bank transaction.
     */
    public function parse(InboundEmail $email): ?PendingTransaction;
}
