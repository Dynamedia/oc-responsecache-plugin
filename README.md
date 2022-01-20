# OctoberCMS lightning fast static files cache system

** WORK IN PROGRESS - NO NOT USE **

This plugin generates static files from PHP responses. These files can be served directly
by your web server without the need to invoke PHP for some requests.

This is PHP done efficiently - We don't want to waste resources re-generating the same
content over and over again. It's both computationally and environmentally expensive.

Be fast, be efficient.

The plugin is a derivative work of https://github.com/Biz-mark/Quicksilver but has been adapted
to ensure suitability with current and future Dynamedia plugins along with adding support for
more filetypes and query strings (for API caching).

### Notes

For this plugin to work, you will need to make some minor alterations to your web server configuration so
that the generated static files can be served.

By default, ResponseCache plugin will cache all responses with a 200 (success) response code
on all routes except the application backend.

You should carefully consider which of your routes are suitable for caching as this will depend on
your theme.

Avoid caching any page which contains data that is only intended for an individual or specific
group of users.

Remember, you can cache a container page and fetch tailored content in a separate request.

## Configuration
### Apache

1. Open `.htaccess` and add the following before `Standard routes` section

    ```apacheconfig
    ##
    ## Serve Cached Page If Available
    ##
    RewriteCond %{REQUEST_URI} ^/?$
    RewriteCond %{DOCUMENT_ROOT}/storage/page-cache/pc__index__pc.html -f
    RewriteRule .? /storage/page-cache/pc__index__pc.html [L]
    RewriteCond %{DOCUMENT_ROOT}/storage/page-cache%{REQUEST_URI}.html -f
    RewriteRule . /storage/page-cache%{REQUEST_URI}.html [L]
    RewriteCond %{HTTP:X-Requested-With} XMLHttpRequest
    RewriteRule !^index.php index.php [L,NC]
    ```

2. Comment out following line in `White listed folders` section.
    ```
    RewriteRule !^index.php index.php [L,NC]
    ```

3. **Be sure that plugin can create/write/read "page-cache" folder in your storage path.**

### Nginx

```nginx
location = / {
    try_files /storage/page-cache/pc__index__pc.html /index.php?$query_string;
}

location / {
    try_files $uri $uri/ /storage/page-cache/$uri.html /storage/page-cache/$uri.json /index.php?$query_string;
}
```

If you need to send ajax requests to cached url, you should use this construction

```nginx
location / {
    if ($request_method = POST ) {
        rewrite ^/.*$ /index.php last;
    }

    try_files $uri $uri/ /storage/page-cache/$uri.html /storage/page-cache/$uri.json /index.php?$query_string;
}
```


### Ignoring the cached files

To make sure you don't commit your locally cached files to your git repository, add this line to your `.gitignore` file:

```
/storage/page-cache
```

## Clearing the cache

Since the responses are cached to disk as static files, any updates to those pages in your app will not be reflected on your site. To update pages on your site, you should clear the cache with the following command:

```
php artisan page-cache:clear
```

As a rule of thumb, it's good practice to add this to your deployment script. That way, whenever you push an update to your site the page cache will automatically be cleared.

If you're using [Forge](https://forge.laravel.com)'s Quick Deploy feature, you should add this line to the end of your Deploy Script. This'll ensure that the cache is cleared whenever you push an update to your site.

You may optionally pass a URL slug to the command, to only delete the cache for a specific page:

```
php artisan page-cache:clear {slug}
```

## Customizing what to cache

By default, all GET requests with a 200 HTTP response code are cached. If you want to change that, create your own middleware that extends the package's base middleware, and override the `shouldCache` method with your own logic.

Example:
```php
<?php namespace Acme\Plugin\Middleware;

use Request;
use Response;

use Dynamedia\ResponseCache\Classes\Middleware\CacheResponse as BaseCacheResponse;

class CacheResponse extends BaseCacheResponse
{
    protected function shouldCache(Request $request, Response $response)
    {
        // In this example, we don't ever want to cache pages if the
        // URL contains a query string. So we first check for it,
        // then defer back up to the parent's default checks.
        if ($request->getQueryString()) {
            return false;
        }

        return parent::shouldCache($request, $response);
    }
}
```

Update the `Plugin.php` of `Dynamedia\ResponseCache` and pass your new `CacheResponse` class to `pushMiddleware()` method.

Don't forget to freeze all updates of Quicksilver plugin at settings of your OctoberCMS website. Otherwise all your changes in `Plugin.php` file will be overwritten by next update from marketplace.

---
© 2019, [Biz-Mark](https://biz-mark.ru/) under the [MIT license](https://opensource.org/licenses/MIT).

Adaptation for OctoberCMS by Nick Khaetsky at Biz-Mark.
