<?php

namespace App\Domain\User\Tasks;

interface PreparePhoneTaskInterface
{
    public function exec(string $phone):string;
}
