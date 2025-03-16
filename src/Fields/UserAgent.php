<?php

namespace Travelnoord\Logging\Fields;

use Hamidrezaniazi\Pecs\Fields\AbstractEcsField;
use Hamidrezaniazi\Pecs\Fields\Os;
use Illuminate\Support\Collection;

class UserAgent extends AbstractEcsField
{
    public function __construct(
        public readonly ?string $deviceName = null,
        public readonly ?string $name = null,
        public readonly ?string $original = null,
        public readonly ?string $version = null,
        public readonly ?Os $os = null,
    ) {
        parent::__construct();
    }

    protected function key(): ?string
    {
        return 'user_agent';
    }

    /**
     * @return Collection<string, Collection<string, float|int|string|null>|string|null>
     */
    protected function body(): Collection
    {
        return new Collection([
            'device.name' => $this->deviceName,
            'name' => $this->name,
            'original' => $this->original,
            'version' => $this->version,
            'os' => $this->os?->getBody(),
        ]);
    }
}
