<?php

namespace App\Domain\Chat\Repositories;

use App\Domain\Chat\Entities\Chat;
use App\Domain\User\Entities\User;

interface CacheChatsRepositoryInterface
{
    public function putRecipientsDataForPrivate(array $recipientsIds,array $recipientsData):void;
    public function getRecipientsDataForPrivate(array $recipientsIds):?array;
    public function invalidateRecipientsCache(array $recipientsIds):void;
}
