<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\DataCollector;

use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

final class WebhookDataCollector extends DataCollector
{
    /**
     * @var string
     */
    public const NAME = 'easy_webhook.data_collector';

    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookClientInterface
     */
    private $webhookClient;

    public function __construct(WebhookClientInterface $webhookClient)
    {
        $this->webhookClient = $webhookClient;
    }

    public function collect(Request $request, Response $response, ?\Throwable $throwable = null): void
    {
        $this->setMiddleware();
        $this->setResults();
    }

    /**
     * @return mixed[]
     */
    public function getMiddleware(): array
    {
        return $this->data['webhook_middleware'] ?? [];
    }

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return \EonX\EasyWebhook\Interfaces\WebhookResultInterface[]
     */
    public function getResults(): array
    {
        return $this->data['webhook_results'] ?? [];
    }

    public function reset(): void
    {
        $this->data = [];
    }

    private function setMiddleware(): void
    {
        $this->data['webhook_configurators'] = [];

        if (($this->webhookClient instanceof TraceableWebhookClient) === false) {
            return;
        }

        foreach ($this->webhookClient->getMiddleware() as $middleware) {
            $reflection = new \ReflectionClass($middleware);

            $this->data['webhook_middleware'][] = [
                'class' => $reflection->getName(),
                'filename' => $reflection->getFileName(),
                'priority' => $middleware->getPriority(),
            ];
        }
    }

    private function setResults(): void
    {
        $this->data['webhook_results'] = [];

        if (($this->webhookClient instanceof TraceableWebhookClient) === false) {
            return;
        }

        $map = static function (WebhookResultInterface $result): array {
            return [
                'webhook' => $result->getWebhook(),
                'response' => $result->getResponse() !== null ? $result->getResponse()
                    ->getInfo() : null,
                'throwable' => $result->getThrowable(),
            ];
        };

        $this->data['webhook_results'] = \array_map($map, $this->webhookClient->getResults());
    }
}
