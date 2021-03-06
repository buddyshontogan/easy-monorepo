<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

interface ErrorDetailsResolverInterface
{
    /**
     * @var int
     */
    public const DEFAULT_MAX_DEPTH = 10;

    /**
     * @return mixed[]
     */
    public function resolveExtendedDetails(\Throwable $throwable, ?int $maxDepth = null): array;

    /**
     * @return mixed[]
     */
    public function resolveSimpleDetails(\Throwable $throwable, ?bool $withTrace = null): array;
}
