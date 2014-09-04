phpUrlLib
=========

A PHP url utility, build-in url parser and  builder			
PHP Url 分析和拼装组件

解析URL
-------
示例:
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

修改Url数据元
```php
$url = Url::parse("https://github.com/idolo/");
$url->user = "yourname";
$url->query = "branch=master"; // 也可以用数组赋值: array("branch"=>"master");

// $url尝试转化为字符串, 会调用方法 __toString(), 转化为字符串格式
// 建议在代码中使用: $url->url; 来获取生成的url地址
echo $url; // >> https://yourname@github.com/idolo/?branch=master

```


快速拼装你的Url
--------
示例:
```php
$params = array("id"=>123, "action=get");
$url = Url::join("www.mydomain.com","module","controller","action.php", $params );
// 默认使用http协议
echo $url; // >> http://www.mydomain.com/module/controller/action.php?id=123&action=get

// 可识别的、无序的元参数拼装 url.
$url = Url::join("https://github.com","action.php", "id=123&flag=1", ":8080", "@yourname", "more=true");
echo $url; // >> https://yourname@github.com:8080/action.php?id=123&flag=1&more=true

```





