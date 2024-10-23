<?php
namespace V3;

class RequestModel
{
    public function __construct(
        public string $login,
        public string $password,
        public string $email,
    )
    {
    }
}
