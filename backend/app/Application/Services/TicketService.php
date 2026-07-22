<?php

namespace App\Application\Services;

use App\Domains\Ticket\Ticket;

class TicketService extends BaseService
{
    protected string $modelClass = Ticket::class;
}
