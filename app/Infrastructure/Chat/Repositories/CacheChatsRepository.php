<?php

namespace App\Infrastructure\Chat\Repositories;

use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\CacheChatsRepositoryInterface;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\GetUserByIdRepositoryInterface;
use App\Domain\User\Repositories\ReadUserRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CacheChatsRepository implements CacheChatsRepositoryInterface
{
    public function putRecipientsDataForPrivate(array $recipientsIds,array $recipientsData):void
    {
        $key = $this->generateRecipientsCacheKey($recipientsIds);
        $serializedData = json_encode($recipientsData);
        Redis::setex($key, 3600, $serializedData);
    }
    public function getRecipientsDataForPrivate(array $recipientsIds):?array
    {
        $key = $this->generateRecipientsCacheKey($recipientsIds);
        $data = Redis::get($key);

        if ($data === null) {
            return null;
        }

        return json_decode($data, true);
    }
    public function invalidateRecipientsCache(array $recipientsIds):void
    {
        $key = $this->generateRecipientsCacheKey($recipientsIds);
        try{
            Redis::del($key);
        }catch (\Exception $e){
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
    private function generateRecipientsCacheKey(array $recipientsIds): string
    {
        sort($recipientsIds);
        $hash = md5(implode(',', $recipientsIds));
        return "chat_recipients:{$hash}";
    }
}
