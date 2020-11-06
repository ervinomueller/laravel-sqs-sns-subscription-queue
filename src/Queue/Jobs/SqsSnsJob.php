<?php

namespace Joblocal\LaravelSqsSnsSubscriptionQueue\Queue\Jobs;

use Aws\Sqs\SqsClient;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Container\Container;
use Illuminate\Queue\CallQueuedHandler;
use Illuminate\Support\Arr;

class SqsSnsJob extends SqsJob
{
    /**
     * Create a new job instance.
     *
     * @param Container $container
     * @param SqsClient $sqs
     * @param string $queue
     * @param array $job
     * @param string $connectionName
     * @param array $routes
     * @return void
     * @throws BindingResolutionException
     */
    public function __construct(
        Container $container,
        SqsClient $sqs,
        array $job,
        string $connectionName,
        string $queue,
        array $routes
    )
    {
        parent::__construct($container, $sqs, $job, $connectionName, $queue);

        $this->job = $this->resolveSnsSubscription($this->job, $routes);
    }

    /**
     * Resolves SNS queue messages
     *
     * @param array $job
     * @param array $routes
     * @return array
     * @throws BindingResolutionException
     */
    protected function resolveSnsSubscription(array $job, array $routes)
    {
        $body = json_decode(Arr::get($job, 'Body'), true);

        $commandName = $this->getDefaultJob();

        $possibleRouteParams = ['Subject', 'TopicArn'];

        foreach ($possibleRouteParams as $param) {
            if (isset($body[$param]) && array_key_exists($body[$param], $routes)) {
                $commandName = $routes[$body[$param]];
                break;
            }
        }

        $command = $this->makeCommand($commandName, $body);

        $job['Body'] = json_encode([
            'displayName' => $commandName,
            'job'         => CallQueuedHandler::class . '@call',
            'data'        => compact('commandName', 'command'),
        ]);

        return $job;
    }

    /**
     * Make the serialized command.
     *
     * @param string $commandName
     * @param array $body
     * @return string
     * @throws BindingResolutionException
     */
    protected function makeCommand(string $commandName, array $body)
    {
        $payload = json_decode(Arr::get($body, 'Message'), true);

        $data = [
            'subject' => Arr::get($body, 'Subject'),
            'payload' => $payload,
        ];

        $instance = $this->container->make($commandName, $data);

        return serialize($instance);
    }

    public function getSqsSnsJob()
    {
        return $this->job;
    }

    private function getDefaultJob()
    {
        return config("queue.connections.{$this->connectionName}.default-job", DefaultJob::class);
    }
}
