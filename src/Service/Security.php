<?php

namespace AuthenticationServer\Service;

use Psr\Http\Message\ServerRequestInterface;

class Security
{
    /**
     * @param ServerRequestInterface $request
     * @param string | array $expectedScopes
     * @return bool
     */
    public function isGranted(ServerRequestInterface $request, $expectedScopes)
    {
        $scopes = $request->getAttribute('oauth_scopes', []);

        if (is_string($expectedScopes)) {
            $expectedScopes = [$expectedScopes];
        }

        foreach ($expectedScopes as $expected) {
            if ($this->vote($expected, $scopes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $expected
     * @param array $scopes
     * @return bool
     */
    private function vote($expected, array $scopes)
    {
        return in_array($expected, $scopes);
    }
}