<?php

namespace App\Providers;

use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;

class ElasticsearchServiceProvider extends ServiceProvider
{
    public function register()
    {
//        $this->app->singleton('elasticsearch',function (){
//            return ClientBuilder::create()
//                ->setHosts([env('ELASTIC_HOST')])
//                ->build();
//        });
    }
    public function boot()
    {
//        if(app()->environment('local')){
//            $this->createElasticsearchIndex();
//        }
    }
    private function createElasticsearchIndex()
    {
//        $client = app('elasticsearch');
//        $indexParams = [
//            'index' => 'users',
//            'body' => [
//                'settings' => [
//                    'analysis' => [
//                        'custom_username_analyzer' => [
//                            'type' => 'custom',
//                            'tokenizer' => 'standard',
//                            'filter' => ['lowercase']
//                        ],
//                    ],
//                ],
//            ],
//            'mappings' => [
//                'properties' => [
//                    'username' => [
//                        'type' => 'text',
//                        'analyzer' => 'custom_username_analyzer',
//                        'fields' => [
//                            'keyword' => [
//                                'type' => 'keyword',
//                                'ignore_above' => 512,
//                            ],
//                        ],
//                    ],
//                ],
//            ],
//        ];
//        try {
//            if (!$client->indices()->exists(['index' => 'users'])) {
//                $client->indices()->create($indexParams);
//            }
//        } catch (\Exception $e) {
//            logger()->error('Ошибка Elasticsearch: ' . $e->getMessage());
//        }
    }
}
