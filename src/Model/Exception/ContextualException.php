<?php
declare(strict_types=1);

namespace Attlaz\AttlazMonolog\Model\Exception;

class ContextualException extends \Exception
{
    private array $context;

    public function __construct(string $message = '', array $context = [], int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }
}
