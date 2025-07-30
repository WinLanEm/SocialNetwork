<?php

namespace App\Domain\Chat\Repositories;

interface DestroyChatRepositoryInterface
{
    public function exec(string $id):int;
}
