<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Queue\Connectors\ConnectorInterface;
use Illuminate\Queue\SyncQueue;
use Illuminate\Queue\Worker;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Queue;

const LOST_CONNECTION_MESSAGE = 'SQLSTATE[HY000]: General error: 7 no connection to the server';

function pollQueueThatFailsWith(string $message): void
{
    $queue = Mockery::mock(SyncQueue::class)->makePartial();
    $queue->shouldReceive('pop')->andReturnUsing(fn () => throw new PDOException($message));

    Queue::extend('failing', fn () => new class($queue) implements ConnectorInterface
    {
        public function __construct(private QueueContract $queue) {}

        public function connect(array $config): QueueContract
        {
            return $this->queue;
        }
    });

    config(['queue.connections.failing' => ['driver' => 'failing']]);

    $worker = new class(app('queue'), app('events'), app(ExceptionHandler::class), fn (): bool => false) extends Worker
    {
        public function sleep($seconds): void {}
    };

    $worker->runNextJob('failing', 'default', new WorkerOptions(sleep: 0));
}

it('does not report a lost database connection while the queue worker polls for a job', function () {
    Exceptions::fake();

    pollQueueThatFailsWith(LOST_CONNECTION_MESSAGE);

    Exceptions::assertNothingReported();
});

it('reports other database errors while the queue worker polls for a job', function () {
    Exceptions::fake();

    pollQueueThatFailsWith('SQLSTATE[42P01]: Undefined table: 7 ERROR:  relation "jobs" does not exist');

    Exceptions::assertReported(PDOException::class);
});

it('reports a lost database connection outside the queue worker', function () {
    Exceptions::fake();

    report(new PDOException(LOST_CONNECTION_MESSAGE));

    Exceptions::assertReported(PDOException::class);
});
