<?php

namespace App\Console\Commands;

use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Console\Command;

class SetupElasticsearchIndex extends Command
{
    protected $signature = 'elasticsearch:create-index
                            {index-name : Название индекса (например, "users")}
                            {--force : Пересоздать индекс, если он уже существует}';

    protected $description = 'Создаёт индекс в Elasticsearch с настройками для модели User';

    public function handle()
    {
        $indexName = $this->argument('index-name');
        $force = $this->option('force');

        try {
            $client = ClientBuilder::create()
                ->setHosts([env('ELASTIC_HOST', 'http://elasticsearch:9200')])
                ->build();

            // Улучшенная проверка существования индекса
            try {
                $indexExists = $client->indices()->exists(['index' => $indexName])->asBool();
            } catch (\Exception $e) {
                $indexExists = false;
            }

            if ($indexExists && !$force) {
                $this->error("Индекс {$indexName} уже существует! Используйте --force для пересоздания.");
                return 1;
            }

            // Удаляем только если индекс существует ИЛИ пропускаем ошибку 404
            if ($indexExists && $force) {
                try {
                    $client->indices()->delete(['index' => $indexName]);
                    $this->info("Старый индекс {$indexName} удалён.");
                } catch (\Exception $e) {
                    // Игнорируем ошибку "индекс не найден"
                    if (strpos($e->getMessage(), 'index_not_found_exception') === false) {
                        throw $e;
                    }
                }
            }

            $params = [
                'index' => $indexName,
                'body' => [
                    'settings' => [
                        'analysis' => [
                            'analyzer' => [
                                'custom_username_analyzer' => [
                                    'type' => 'custom',
                                    'tokenizer' => 'standard',
                                    'filter' => ['lowercase', 'edge_ngram_filter'],
                                ],
                            ],
                            'filter' => [
                                'edge_ngram_filter' => [
                                    'type' => 'edge_ngram',
                                    'min_gram' => 1,
                                    'max_gram' => 512,
                                ],
                            ],
                        ],
                    ],
                    'mappings' => [
                        'properties' => [
                            'username' => [
                                'type' => 'text',
                                'analyzer' => 'custom_username_analyzer',
                                'fields' => [
                                    'keyword' => [
                                        'type' => 'keyword',
                                        'ignore_above' => 512,
                                    ],
                                ],
                            ],
                            'id' => ['type' => 'integer'],
                        ],
                    ],
                ],
            ];

            $client->indices()->create($params);
            $this->info("Индекс {$indexName} успешно создан!");

        } catch (\Exception $e) {
            $this->error("Ошибка: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
