<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetupMongoChatIndex extends Command
{
    protected $signature = 'mongo:indexes';
    protected $description = 'Create MongoDB indexes manually';
    public function handle()
    {
        $this->info('Cleaning existing data...');

        $this->info('Creating indexes...');
        $collection = DB::connection('mongodb')->getCollection('chats');
//        $collection->dropIndex('participants_index');
//        $collection->dropIndex('created_at_desc_index');
//        $collection->dropIndex('unique_chat_index');
        try {
            $collection->createIndex(
                ['participants' => 1],
                ['name' => 'participants_index', 'background' => true]
            );

            $collection->createIndex(
                ['created_at' => -1],
                ['name' => 'created_at_desc_index', 'background' => true]
            );

            $hasDuplicates = $collection->aggregate([
                ['$group' => [
                    '_id' => ['participants' => '$participants', 'type' => '$type'],
                    'count' => ['$sum' => 1]
                ]],
                ['$match' => ['count' => ['$gt' => 1]]],
                ['$limit' => 1]
            ])->toArray();

            if (!empty($hasDuplicates)) {
                $this->error('Duplicate chats found! Clean them first:');
                $this->line(json_encode($hasDuplicates, JSON_PRETTY_PRINT));
                return 1;
            }

            $collection->createIndex(
                [
                    'participants_hashed' => 1,
                    'type' => 1
                ],
                [
                    'name' => 'unique_chat_index',
                    'unique' => true,
                    'partialFilterExpression' => [
                        'type' => ['$in' => ['private', 'group']]
                    ]
                ]
            );

            $this->info('Indexes created successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Error creating indexes: '.$e->getMessage());
            return 1;
        }
    }
}
