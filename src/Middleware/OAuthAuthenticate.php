<?php

namespace Goodwong\LaravelWechatOAuth\Middleware;

use Log;
use Closure;
use Illuminate\Http\Request;
use Goodwong\LaravelWechat\Handlers\WechatHandler;
use Goodwong\LaravelWechat\Events\WechatUserAuthorized;
use Goodwong\LaravelWechat\Repositories\WechatUserRepository;
use EasyWeChat\Foundation\Application as EasyWechatApplication;

class OAuthAuthenticate
{
    /**
     * construct
     * 
     * @param  WechatHandler  $wechatHandler
     * @param  WechatUserRepository  $wechatUserRepository
     * @return void
     */
    public function __construct(WechatHandler $wechatHandler, WechatUserRepository $wechatUserRepository)
    {
        $this->wechatHandler = $wechatHandler;
        $this->wechatUserRepository = $wechatUserRepository;

        $config = [
            'app_id' => config('wechat.app_id'),
            'secret' => config('wechat.secret'),
        ];
        $this->wechat = new EasyWechatApplication($config);
    }

    /**
     * Handle an incoming request.
     * 
     * @param Request  $request
     * @param \Closure  $next
     * @param string|null  $scopes
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $scopes = null)
    {
        // 已登录用户
        if ($request->user()) {
            return $next($request);
        }

        // 非微信，跳过
        if (!$this->isWeChatBrowser($request)) {
            return $next($request);
        }

        $scopes = $scopes ?: config('wechat.oauth.scopes', 'snsapi_userinfo');
        if (is_string($scopes)) {
            $scopes = array_map('trim', explode(',', $scopes));
        }

        // 有缓存
        $exist = session('wechat.oauth_user');
        if ($exist) {
            $wechatUser = $this->wechatUserRepository->find($exist['id']);
            event(new WechatUserAuthorized($wechatUser));
            return $next($request);
        }

        // 转去授权
        if (!$request->has('code')) {
            return $this->wechat->oauth->scopes($scopes)->redirect($request->fullUrl());
        }

        // 解析 code
        $info = $this->wechat->oauth->user()->getOriginal();
        Log::info('[wechat_login] original info: ', (array)$info);
        $wechatUser = $this->wechatUserRepository
        ->scopeQuery(function ($query) use ($info) {
            return $query->where('openid', $info['openid']);
        })->first();
        if (!$wechatUser) {
            $wechatUser = $this->wechatHandler->create($info);
        }
        event(new WechatUserAuthorized($wechatUser));
        session(['wechat.oauth_user' => $wechatUser]);

        return redirect()->to($this->getTargetUrl($request));
    }

    /**
     * Build the target business url.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function getTargetUrl($request)
    {
        $queries = array_except($request->query(), ['code', 'state']);
        return $request->url().(empty($queries) ? '' : '?'.http_build_query($queries));
    }

    /**
     * Detect current user agent type.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function isWeChatBrowser($request)
    {
        return stripos($request->header('user_agent'), 'MicroMessenger') !== false;
    }
}