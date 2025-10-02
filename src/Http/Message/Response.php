<?php

declare(strict_types=1);

namespace Capsule\Http\Message;

use Capsule\Contracts\ResponseInterface;

final class Response implements ResponseInterface
{
    private HeaderBag $headers;

    /** @param string|iterable<string> $body */
    public function __construct(
        private int $status = 200,
        private string|iterable $body = '',
    ) {
        if ($status < 100 || $status > 599) {
            throw new \InvalidArgumentException("Invalid HTTP status: $status");
        }
        $this->headers = new HeaderBag();
    }

    // --- NEW: presence checks / accessors ---
    public function hasHeader(string $name): bool
    {
        // Case-insensitive per RFC
        foreach ($this->headers->all() as $n => $_) {
            if (strcasecmp($n, $name) === 0) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    public function getHeader(string $name): array
    {
        foreach ($this->headers->all() as $n => $values) {
            if (strcasecmp($n, $name) === 0) {
                return $values;
            }
        }

        return [];
    }

    public function getHeaderLine(string $name): string
    {
        $values = $this->getHeader($name);

        return $values ? implode(', ', $values) : '';
    }
    // --- end NEW ---

    public function withHeader(string $name, string $value): self
    {
        $c = clone $this;
        $c->headers = clone $this->headers;
        $c->headers->set($name, $value);

        return $c;
    }

    public function withAddedHeader(string $name, string $value): self
    {
        $c = clone $this;
        $c->headers = clone $this->headers;
        $c->headers->add($name, $value);

        return $c;
    }

    public function withoutHeader(string $name): self
    {
        $c = clone $this;
        $c->headers = clone $this->headers;
        $c->headers->remove($name);

        return $c;
    }

    public function withStatus(int $status): self
    {
        if ($status < 100 || $status > 599) {
            throw new \InvalidArgumentException("Invalid HTTP status: $status");
        }
        $c = clone $this;
        $c->status = $status;

        return $c;
    }

    /** @param string|iterable<string> $body */
    public function withBody(string|iterable $body): self
    {
        $c = clone $this;
        $c->body = $body;

        return $c;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
    /** @return string|iterable<string> */ public function getBody(): string|iterable
    {
        return $this->body;
    }
    /** @return array<string,list<string>> */ public function getHeaders(): array
    {
        return $this->headers->all();
    }
}
