<?php

declare(strict_types=1);

namespace Capsule\Contracts;

interface ResponseInterface
{
    public function getStatus(): int;
    /** @return string|iterable<string> */
    public function getBody(): string|iterable;
    /** @return array<string, list<string>> */
    public function getHeaders(): array;

    public function withStatus(int $status): self;
    public function withBody(string|iterable $body): self;
    public function withHeader(string $name, string $value): self;
    public function withAddedHeader(string $name, string $value): self;
    public function withoutHeader(string $name): self;
}
