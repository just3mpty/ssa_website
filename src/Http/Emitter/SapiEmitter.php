<?php

namespace Capsule\Http\Emitter;

use Capsule\Http\Message\Response;

final class SapiEmitter
{
    public function emit(Response $r): void
    {
        // Défauts sûrs (à défaut d’autres middlewares)
        $headers = $r->getHeaders();
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = ['text/plain; charset=utf-8'];
            $headers['X-Content-Type-Options'] = ['nosniff'];
        }

        $this->assertSafeHeaders($headers);

        if (!headers_sent()) {
            http_response_code($r->getStatus());

            $compressionOn = (bool) ini_get('zlib.output_compression');
            $hasOB = ob_get_level() > 0;
            $isIterable = is_iterable($r->getBody());
            $hasTE = isset($headers['Transfer-Encoding']);

            $canLen = !$compressionOn && !$hasOB && !$hasTE && !$isIterable;
            if ($canLen && !isset($headers['Content-Length'])) {
                $len = strlen((string) $r->getBody());
                $headers['Content-Length'] = [(string)$len];
            }

            foreach ($headers as $name => $values) {
                foreach ($values as $v) {
                    header("$name: $v", false);
                }
            }
        }

        $body = $r->getBody();
        if (is_iterable($body)) {
            foreach ($body as $chunk) {
                echo $chunk;
                flush();
            }

            return;
        }
        echo (string)$body;
    }

    /** @param array<string,list<string>> $headers */
    private function assertSafeHeaders(array $headers): void
    {
        foreach ($headers as $name => $values) {
            if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9\-]*$/', $name)) {
                throw new \InvalidArgumentException("Invalid header name: $name");
            }
            foreach ($values as $v) {
                if (str_contains($v, "\r") || str_contains($v, "\n")) {
                    throw new \InvalidArgumentException("Invalid header value for $name");
                }
            }
        }
    }
}
