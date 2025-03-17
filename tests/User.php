<?php

namespace Travelnoord\Logging\Tests;

use Illuminate\Contracts\Auth\Authenticatable;

class User implements Authenticatable
{
    protected int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getRememberToken(): string
    {
        return '';
    }

    public function setRememberToken($value) {}

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function getAuthPasswordName()
    {
        return '';
    }
}
