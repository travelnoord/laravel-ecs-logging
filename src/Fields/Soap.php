<?php

namespace Travelnoord\Logging\Fields;

use Hamidrezaniazi\Pecs\Fields\AbstractEcsField;
use Illuminate\Support\Collection;

class Soap extends AbstractEcsField
{
    public function __construct(
        public readonly ?string $requestId = null,
        public readonly ?string $requestLocation = null,
        public readonly ?string $requestBodyContent = null,
        public readonly ?string $requestAction = null,
        public readonly ?string $responseBodyContent = null,
        public readonly ?string $version = null,
    ) {
        parent::__construct();
    }

    protected function key(): ?string
    {
        return 'soap';
    }

    /**
     * @return Collection<string, string|null>
     */
    protected function body(): Collection
    {
        return collect([
            'request.body.content' => $this->requestBodyContent,
            'request.id' => $this->requestId,
            'request.location' => $this->requestLocation,
            'response.action' => $this->requestAction,
            'response.body.content' => $this->responseBodyContent,
            'version' => $this->version,
        ]);
    }
}
