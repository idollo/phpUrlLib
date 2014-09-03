phpUrlLib
=========

A PHP url utility, build-in url parser and  builder

Parse An Url
-------
example:
```php
$urlmeta = Url::parse("http://username:password@code.google.com:8080/script.php?id=123&action=save#hash");

echo $urlmeta->protocol;	// >> http
echo $urlmeta->auth;		// >> user:password
echo $urlmeta->user;		// >> username
echo $urlmeta->password;	// >> password
echo $urlmeta->hostnam;		// >> code.google.com
echo $urlmeta->port;		// >> 8080
echo $urlmeta->path;		// >> /script.php
echo $urlmeta->query;		// >> id=123&action=save (stringify query)
echo $urlmeta->hash;		// >> #hash

echo json_encode($urlmeta->query->params());
// query dictionary, iterateable
// >> {"id":"123", "action":"save"}

```
