<?php

namespace AuthenticationServer\Hasher;

class PHPHasher implements HasherInterface
{
    /**
     * @param $password
     *
     * @return string
     */
    public function hash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ["cost" => 10]);
    }

    /**
     * @param $password
     * @param $hash
     *
     * @return bool
     */
    public function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }
}