<?php

declare(strict_types=1);

namespace Capsule\Http\Middleware;

use Capsule\Contracts\HandlerInterface;
use Capsule\Contracts\MiddlewareInterface;
use Capsule\Contracts\ResponseFactoryInterface;
use Capsule\Http\Exception\HttpException;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response; // ton implémentation concrète
use Capsule\Routing\Exception\MethodNotAllowed;
use Capsule\Routing\Exception\NotFound;

final class ErrorBoundary implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $res,
        private readonly bool $debug = false,
        private readonly ?string $appName = null,
    ) {
    }

    public function process(Request $request, HandlerInterface $next): Response
    {
        $reqId = self::requestId();

        try {
            $resp = $next->handle($request);

            return $resp->withHeader('X-Request-Id', $reqId);
        } catch (MethodNotAllowed $e) {
            $payload = $this->basePayload($request, $reqId, 405, 'Method Not Allowed');
            if ($this->debug) {
                $payload['details'] = ['allowed' => $e->allowed];
            }
            $resp = $this->res->json($payload, 405)
                ->withHeader('X-Request-Id', $reqId)
                ->withHeader('Allow', implode(', ', $e->allowed));

            return $resp;
        } catch (NotFound $e) {
            $payload = $this->basePayload($request, $reqId, 404, 'Not Found');

            return $this->res->json($payload, 404)
                ->withHeader('X-Request-Id', $reqId);
        } catch (HttpException $e) {
            $status = $e->status;
            $message = $e->getMessage() !== '' ? $e->getMessage() : ($status >= 500 ? 'Server Error' : 'HTTP Error');

            $payload = $this->basePayload($request, $reqId, $status, $message);
            if ($this->debug) {
                $payload['debug'] = $this->debugBlock($e);
            }

            $resp = $this->res->json($payload, $status)->withHeader('X-Request-Id', $reqId);

            return $this->applyHeaders($resp, $e->headers);
        } catch (\Throwable $e) {
            $payload = $this->basePayload($request, $reqId, 500, 'Server Error');
            if ($this->debug) {
                $payload['debug'] = $this->debugBlock($e);
            }

            return $this->res->json($payload, 500)
                ->withHeader('X-Request-Id', $reqId);
        }
    }

    /**
     * @return array{
     *   app?:string,
     *   requestId:string,
     *   status:int,
     *   error:array{type:string,message:string},
     *   request:array{method:string,path:string}
     * }
     */
    private function basePayload(Request $r, string $reqId, int $status, string $message): array
    {
        $base = [
            'requestId' => $reqId,
            'status' => $status,
            'error' => [
                'type' => $this->statusToType($status),
                'message' => $message,
            ],
            'request' => [
                'method' => $r->method,
                'path' => $r->path,
            ],
        ];
        if ($this->appName) {
            $base['app'] = $this->appName;
        }

        return $base;
    }

    /** @return array<string,mixed> */
    private function debugBlock(\Throwable $e): array
    {
        return [
            'class' => $e::class,
            'message' => $e->getMessage(),
            'file' => $e->getFile() . ':' . $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString()),
            'causes' => $this->flattenCauses($e->getPrevious()),
        ];
    }

    /** @return list<array{class:string,message:string,file:string}> */
    private function flattenCauses(?\Throwable $e): array
    {
        $out = [];
        while ($e) {
            $out[] = [
                'class' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
            ];
            $e = $e->getPrevious();
        }

        return $out;
    }

    private static function statusToType(int $s): string
    {
        return match (true) {
            $s === 400 => 'bad_request',
            $s === 401 => 'unauthorized',
            $s === 403 => 'forbidden',
            $s === 404 => 'not_found',
            $s === 405 => 'method_not_allowed',
            $s === 429 => 'too_many_requests',
            $s >= 500 => 'server_error',
            default => 'http_error',
        };
    }

    /**
     * Applique array<string, list<string>> sur Response (withHeader + withAddedHeader)
     * @param array<string, list<string>> $headers
     */
    private function applyHeaders(Response $resp, array $headers): Response
    {
        foreach ($headers as $name => $values) {
            $first = true;
            foreach ($values as $v) {
                $resp = $first
                    ? $resp->withHeader($name, $v)
                    : $resp->withAddedHeader($name, $v);
                $first = false;
            }
        }

        return $resp;
    }

    private static function requestId(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
