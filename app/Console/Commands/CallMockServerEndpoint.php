<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CallMockServerEndpoint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:call-mock-server-endpoint';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calling for endpoint mocking receiving current time from server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::post(
            'http://127.0.0.1:8000/api/v1/mock-server',
            //'http://83.217.223.164/api/v1/mock-server',
            [
                'data' => [
                    'timestamp' => now()->toIso8601ZuluString(),
                ],
            ]
        );

        if ($response->successful()) {
            $this->info('Имитация получения запроса с сервера успешна! Текущее время: ' . $response['server_time']);
        } else {
            $this->error('Имитация получения запроса с сервера завершилась ошибкой! Ответ: ' . $response->body());
        }

        return 0;
    }
}
