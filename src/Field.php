<?php

namespace Travelnoord\Logging;

use Hamidrezaniazi\Pecs\Fields\AbstractEcsField;
use Hamidrezaniazi\Pecs\Fields\Error;
use Hamidrezaniazi\Pecs\Fields\Http;
use Hamidrezaniazi\Pecs\Fields\Log;
use Hamidrezaniazi\Pecs\Fields\Source;
use Hamidrezaniazi\Pecs\Fields\Url;
use Hamidrezaniazi\Pecs\Fields\User;
use Hamidrezaniazi\Pecs\Properties\HttpMethod;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Travelnoord\Logging\Fields\Label;
use Travelnoord\Logging\Fields\Tag;
use Travelnoord\Logging\Fields\UserAgent;

class Field
{
    /**
     * @return Tag[]
     */
    public static function tags(string ...$tags): array
    {
        return array_map(static fn(string $tag) => new Tag($tag), $tags);
    }

    /**
     * @param  array<string, scalar>  $labels
     * @return Label[]
     */
    public static function labels(array $labels): array
    {
        return array_map(
            static fn(string $key, $value) => new Label($key, is_bool($value) ? (int)$value : $value),
            array_keys($labels),
            array_values($labels),
        );
    }

    /**
     * @return array<AbstractEcsField>
     */
    public static function psrRequest(RequestInterface $request, bool $withBody = true): array
    {
        $body = null;

        if ($withBody) {
            $body = ((string)$request->getBody()) ?: null;
        }

        return [
            new Url(
                domain: $request->getUri()->getHost(),
                original: (string)$request->getUri(),
                path: $request->getUri()->getPath(),
                port: $request->getUri()->getPort(),
                scheme: $request->getUri()->getScheme(),
            ),
            new Http(
                requestBodyContent: $body,
                requestId: $request->getHeaderLine('X-Request-Id'),
                requestMethod: HttpMethod::tryFrom($request->getMethod()),
                version: $request->getProtocolVersion(),
            ),
            new UserAgent(
                original: $request->getHeaderLine('User-Agent'),
            ),
        ];
    }

    /**
     * @return Http[]
     */
    public static function fromPsrResponse(string $requestId, ResponseInterface $response, bool $withBody = true): array
    {
        $body = $withBody ? (string)$response->getBody() : null;

        return [
            new Http(
                requestId: $requestId,
                responseBodyContent: $body,
                responseStatusCode: $response->getStatusCode(),
                version: $response->getProtocolVersion(),
            ),
        ];
    }

    /**
     * @return array<Error|Log>
     */
    public static function fromThrowable(Throwable $throwable): array
    {
        return [
            new Error(
                code: $throwable->getCode(),
                message: $throwable->getMessage(),
                stackTrace: $throwable->getTraceAsString(),
                type: get_class($throwable),
            ),
            new Log(
                originFileLine: $throwable->getLine(),
                originFileName: $throwable->getFile(),
            ),
        ];
    }

    /**
     * @return User[]
     */
    public static function fromUser(Authenticatable|Model $user): array
    {
        $email = null;
        $fullName = null;
        $id = null;

        if ($user instanceof Authenticatable) {
            $id = $user->getAuthIdentifier();
        }

        if ($user instanceof Model) {
            $id ??= $user->getKey();
            $email = $user->getAttribute('email');
            $fullName = $user->getAttribute('name');
        }

        return [
            new User(
                email: is_scalar($email) ? (string)$email : null,
                fullName: is_scalar($fullName) ? (string)$fullName : null,
                id: is_scalar($id) ? (string)$id : null,
            ),
        ];
    }

    /**
     * @return array<Source|Url|Http>
     */
    public static function fromHttpRequest(Request $request): array
    {
        return [
            new Source(ip: $request->ip()),
            new Url(
                domain: $request->getHost(),
                original: $request->fullUrl(),
                path: $request->path(),
                port: $request->getPort() ? intval($request->getPort()) : null,
                scheme: $request->getScheme(),
            ),
            new Http(
                requestId: $request->headers->get('X-Request-Id'),
                requestMethod: HttpMethod::tryFrom($request->getRealMethod()),
                requestReferer: $request->headers->get('referer'),
                version: str_replace('HTTP/', '', $request->getProtocolVersion() ?: ''),
            ),
        ];
    }
}
