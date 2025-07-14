<?php

namespace App\Application\User\Tasks;

use App\Domain\User\Tasks\PreparePhoneTaskInterface;

class PreparePhoneTask implements PreparePhoneTaskInterface
{
    public function exec(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($cleaned, '7')) {
            $cleaned = '8' . substr($cleaned, 1);
        }

        return $cleaned;
    }
}
