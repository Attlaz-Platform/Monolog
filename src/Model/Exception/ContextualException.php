<?php
declare(strict_types=1);

namespace Attlaz\AttlazMonolog\Model\Exception;

class ContextualException extends \Exception
{

    public function __construct(string $message = '', private array $context = [], int $code = 0, \Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);

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
