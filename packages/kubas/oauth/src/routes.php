<?php

Route::get("/kubas/oauth/callback", function(\Illuminate\Http\Request $request) {
    return \Kubas\Oauth\OauthProvider::Instance()->SetAuth($request);

})->middleware("web");
