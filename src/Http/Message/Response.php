<?php

declare(strict_types=1);

namespace Capsule\Http\Message;

final class Response
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

    // Getters
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

    // Helpers
    public static function text(string $s, int $status = 200): self
    {
        return (new self($status, $s))->withHeader('Content-Type', 'text/plain; charset=utf-8');
    }

    public static function json(mixed $data, int $status = 200): self
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return (new self($status, $json))->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}
