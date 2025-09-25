<?php

declare(strict_types=1);

namespace Capsule\Http;

final class Response
{
    private HeaderBag $headers;

    public function __construct(
        private int $status = 200,
        private string $body = '',
        private string $protocol = '1.1'
    ) {
        $this->assertValidStatus($status);
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
        $this->assertValidStatus($status);
        $c = clone $this;
        $c->status = $status;
        return $c;
    }

    public function withBody(string $body): self
    {
        $c = clone $this;
        $c->body = $body;
        return $c;
    }

    public function withProtocol(string $version): self
    {
        if (!in_array($version, ['1.0','1.1','2','3'], true)) {
            throw new \InvalidArgumentException('Invalid HTTP version');
        }
        $c = clone $this;
        $c->protocol = $version;
        return $c;
    }

    public function send(): void
    {
        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', 'text/plain; charset=utf-8');
            $this->headers->set('X-Content-Type-Options', 'nosniff');
        }

        if (!headers_sent()) {
            // Status line — PHP gère via http_response_code
            http_response_code($this->status);

            $compressionOn = (bool) ini_get('zlib.output_compression');
            $hasOB = ob_get_level() > 0;
            $canSetLength = !$compressionOn && !$hasOB && !$this->headers->has('Transfer-Encoding');

            if ($canSetLength && !$this->headers->has('Content-Length')) {
                $this->headers->set('Content-Length', (string) strlen($this->body));
            }

            foreach ($this->headers->all() as $name => $values) {
                foreach ($values as $v) {
                    header("$name: $v", false);
                }
            }
        }

        echo $this->body;
    }

    // Getters
    public function getStatus(): int
    {
        return $this->status;
    }
    public function getBody(): string
    {
        return $this->body;
    }
    public function getProtocol(): string
    {
        return $this->protocol;
    }
    /** @return array<string,list<string>> */
    public function getHeaders(): array
    {
        return $this->headers->all();
    }

    // Internals
    private function assertValidStatus(int $s): void
    {
        if ($s < 100 || $s > 599) {
            throw new \InvalidArgumentException("Invalid HTTP status: $s");
        }
    }
}
