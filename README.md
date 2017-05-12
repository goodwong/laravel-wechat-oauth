# Laravel 5 Wechat OAuth

微信网页登录，并且将微信资料存储到数据库

## OAuth 中间件
有两种方式设置中间件：

- 设置 middleware
    在app/Http/Kernel.php里添加：
    ```php
    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [

        // ...

        'wechat_oauth' => \Goodwong\LaravelWechatOAuth\Middleware\OAuthAuthenticate::class,
    ];
    ```

- 直接在web.php的路由规则里添加：
    ```php
    // user auth
    Route::group([
        'middleware' => [
            \Goodwong\LaravelWechatOAuth\Middleware\OAuthAuthenticate::class,
        ],
    ], function () {
        // ...
    });

    ```

# Laravel 5 Wechat OAuth

微信网页登录，并且将微信资料存储到数据库

## OAuth 中间件
有两种方式设置中间件：

- 设置 middleware
    在app/Http/Kernel.php里添加：
    ```php
    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [

        // ...

        'wechat_oauth' => \Goodwong\LaravelWechatOAuth\Middleware\OAuthAuthenticate::class,
    ];
    ```

- 直接在web.php的路由规则里添加：
    ```php
    // user auth
    Route::group([
        'middleware' => [
            \Goodwong\LaravelWechatOAuth\Middleware\OAuthAuthenticate::class,
        ],
    ], function () {
        // ...
    });

    ```

## 配置
在.env文件中，配置以下信息：

- 微信平台
    ```ini
    WECHAT_APPID=
    WECHAT_SECRET=
    ```
    > 这些信息可以在开放平台里注册获取




