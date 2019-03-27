# Authentication server

An authentication server uses OAuth 2 protocol

## Dev installation (with Docker)

```
$ make start
```

## Production

```
$ make install
$ composer install
$ bin/console server:init
$ bin/console server:home
```

## Documentation

Terminology

* `Authorization server` : A server which issues access tokens after successfully authenticating a client and resource owner, and authorizing the request.
* `Client` : An application which accesses protected resources on behalf of the resource owner (such as a user). The client could be hosted on a server, desktop, mobile or other device.`
* `Resource owner` : The user who authorizes an application to access their account. The application’s access to the user’s account is limited to the “scope” of the authorization granted (e.g. read or write access).
* `Scope` : A permission.
* `Access token` : A token used to access protected resources.
* `Refresh token` : A token used to get a new `Access token`.
* `Grant` : A grant is a method of acquiring an access token.
