<?php

namespace AuthenticationServer\Hasher;

interface HasherInterface {

    /**
     * @param $password
     *
     * @return string
     */
    public function hash($password);

    /**
     * @param $password
     * @param $hash
     *
     * @return bool
     */
    public function verify($password, $hash);
}