<?php

declare(strict_types=1);

namespace Capsule\Http;

final class Response
{
    /** @var array<string,list<string>> */
    private array $headers = [];

    public function __construct(
        private int $status = 200,
        private string $body = ''
    ) {
        $this->assertValidStatus($this->status);
    }

    // --- Factories ---
    public static function json(array|\JsonSerializable $data, int $status = 200): self
    {
        try {
            $json = json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            $json   = json_encode(['error' => 'Invalid JSON payload']);
            $status = 500;
        }

        $r = new self($status, (string)$json);
        return $r->withHeader('Content-Type', 'application/json; charset=utf-8');
    }

    // --- Withers immuables ---
    public function withHeader(string $name, string $value): self
    {
        [$n, $v] = [$this->normalizeHeaderName($name), $this->sanitizeHeaderValue($value)];
        $c = clone $this;
        $c->headers[$n] = [$v];
        return $c;
    }

    public function withAddedHeader(string $name, string $value): self
    {
        [$n, $v] = [$this->normalizeHeaderName($name), $this->sanitizeHeaderValue($value)];
        $c = clone $this;
        $c->headers[$n] = ($c->headers[$n] ?? []);
        $c->headers[$n][] = $v;
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

    // --- Output ---
    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);
            if (!$this->hasHeader('Content-Length') && !$this->hasHeader('Transfer-Encoding')) {
                $this->headers['Content-Length'] = [(string) strlen($this->body)];
            }
            foreach ($this->headers as $name => $values) {
                foreach ($values as $v) {
                    header("$name: $v", false);
                }
            }
        }
        echo $this->body;
    }

    // --- Getters utiles ---
    public function getStatus(): int
    {
        return $this->status;
    }
    public function getBody(): string
    {
        return $this->body;
    }
    /** @return array<string,list<string>> */
    public function getHeaders(): array
    {
        return $this->headers;
    }
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$this->normalizeHeaderName($name)]);
    }

    // --- Helpers internes ---
    private function assertValidStatus(int $s): void
    {
        if ($s < 100 || $s > 599) {
            throw new \InvalidArgumentException("Invalid HTTP status: $s");
        }
    }

    private function normalizeHeaderName(string $name): string
    {
        $name = trim($name);
        if ($name === '' || preg_match('/[^\x21-\x7E]/', $name)) {
            throw new \InvalidArgumentException("Invalid header name");
        }
        return str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $name))));
    }

    private function sanitizeHeaderValue(string $v): string
    {
        return str_replace(["\r", "\n", "\0"], '', trim($v));
    }
}
