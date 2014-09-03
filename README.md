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

modify the url meta
```php
$url = Url::parse("https://github.com/idolo/");
$url->user = "yourname";
$url->query = "branch=master"; // can also asign with: array("branch"=>"master");

// stringify $url, call the build-in method __toString()
// suggest use: $url->url; to get the url string in your code;
echo $url; // >> https://yourname@github.com/idolo/?branch=master

```


Easily Build Your Url
--------
an quick example:
```php
$params = array("id"=>123, "action=get");
$url = Url::join("www.mydomain.com","module","controller","action.php", $params );
// default protocol: http
echo $url; // >> http://www.mydomain.com/module/controller/action.php?id=123&action=get

// unordered and recognizable arguments.
$url = Url::join("https://github.com","action.php", "id=123&flag=1", ":8080", "@yourname", "more=true");
echo $url; // >> https://yourname@github.com:8080/action.php?id=123&flag=1&more=true

```





