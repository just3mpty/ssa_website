<?php

declare(strict_types=1);

namespace Capsule\Contracts;

use Capsule\Http\Message\Response;
use JsonSerializable;

interface ResponseFactoryInterface
{
    public function createResponse(int $status = 200, string|iterable $body = ''): Response;

    /** @param array<string,mixed>|\JsonSerializable $data */
    public function json(array|\JsonSerializable $data, int $status = 200): Response;

    public function text(string $body, int $status = 200): Response;
    public function html(string $body, int $status = 200): Response;
    public function redirect(string $location, int $status = 302): Response;
    /**
       * @param array<string,mixed> $problem
       */
    public function problem(array $problem, int $status = 400): Response;

    public function download(
        string $filename,
        string $content,
        string $contentType = 'application/octet-stream'
    ): Response;

    /** @param iterable<string> $content */
    public function downloadStream(
        string $filename,
        iterable $content,
        string $contentType = 'application/octet-stream'
    ): Response;


    /** @param iterable<mixed> $items */
    public function jsonStream(iterable $items, ?callable $toRow = null): Response;

    /**
 * @param mixed[]|JsonSerializable|null $body @param array<string,mixed>| \JsonSerializable|null $body */
    public function created(string $location, array|\JsonSerializable|null $body = null): Response;

    public function empty(int $status = 204): Response;

    public function withCookie(Response $r, \Capsule\Http\Support\Cookie $cookie): Response;
    public function noCache(Response $r): Response;
}
