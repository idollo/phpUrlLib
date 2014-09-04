phpUrlLib
=========

A PHP url utility, build-in url parser and  builder				
[查看中文 Readme.md](https://github.com/idolo/phpUrlLib/blob/master/README.CN.md)

Parse An Url
-------
example:
```php
$urlmeta = Url::parse("http://username:password@code.google.com:8080/script.php?id=123&action=save#hash");

echo $urlmeta->protocol;	// >> http
echo $urlmeta->auth;		// >> user:password
echo $urlmeta->user;		// >> username
echo $urlmeta->password;	// >> password
echo $urlmeta->hostname;	// >> code.google.com
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
$params = array("id"=>123, "action"=>"get");
$url = Url::join("www.mydomain.com","module","controller","action.php", $params );
// default protocol: http
echo $url; // >> http://www.mydomain.com/module/controller/action.php?id=123&action=get

// unordered and recognizable arguments.
$url = Url::join("https://github.com","action.php", "id=123&flag=1", ":8080", "@yourname", "more=true");
echo $url; // >> https://yourname@github.com:8080/action.php?id=123&flag=1&more=true

```

Build-in URI Decoder for Query Access
--------
urldecode:
```php
$url = Url::parse("/ddd.php?ids=3%2C4%2C5");
// urldecode %2C to comma(,) 
echo $url->query->ids; // >> 3,4,5  

```
urlencode:
```php
// as browser current url is: http://www.base.com/view.php

$url = Url::base("/action.php", "a=share"); // see Relative Urls
$url = Url::parse($url);
// append query
$url->query->refer = "http://a.com/";

echo $url; // >> http://www.base.com/action.php?a=share&refer=http%3A%2F%2Fa.com%2F

```

Relative & Based Urls
--------
### Url::base($path [, $metas]); 
return based path url base on current hostname;
### Url::abs($path [, $metas]);
convert relative path to absolute path;

example base on: http://www.base.com/module1/action1/ 
```php
echo Url::base("module2","action2","id=3"); 
// >> http://www.base.com/module2/action2?id=3

echo Url::abs("action3.php");
// >> http://www.base.com/module1/action3.php

echo Url::abs("../module3/action3.php");
// >> http://www.base.com/module3/action.php

```


