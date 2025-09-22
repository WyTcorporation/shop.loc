<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class CartCookieTest extends TestCase
{
    use RefreshDatabase;

    private function getCartCookie(array $cookies)
    {
        return Arr::first($cookies, fn ($cookie) => $cookie->getName() === 'cart_id');
    }

    public function test_cart_cookie_respects_session_secure_config(): void
    {
        config()->set('session.secure', true);

        $response = $this->getJson('/api/cart');

        $cartCookie = $this->getCartCookie($response->headers->getCookies());

        $this->assertNotNull($cartCookie);
        $this->assertTrue($cartCookie->isSecure());
        $this->assertTrue($cartCookie->isHttpOnly());
        $this->assertSame('lax', strtolower($cartCookie->getSameSite()));
    }

    public function test_cart_cookie_falls_back_to_environment(): void
    {
        config()->set('session.secure', null);

        $response = $this->getJson('/api/cart');

        $cartCookie = $this->getCartCookie($response->headers->getCookies());

        $this->assertNotNull($cartCookie);
        $this->assertSame(app()->environment('production'), $cartCookie->isSecure());
        $this->assertTrue($cartCookie->isHttpOnly());
        $this->assertSame('lax', strtolower($cartCookie->getSameSite()));
    }
}
