<?php

declare(strict_types=1);

namespace SolarInvestments\Tests\Middleware;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use SolarInvestments\Middleware\RedirectConsumerValidationRequests;
use SolarInvestments\Tests\TestCase;

class RedirectConsumerValidationRequestsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(RedirectConsumerValidationRequests::class)->get(
            uri: '/consumer-validation/v2/{id}',
            action: fn () => abort(Response::HTTP_NOT_FOUND)
        );
    }

    public function test_it_can_redirect_matching_paths(): void
    {
        $response = $this->get('/consumer-validation/v2/abc123?campaign=xyz');

        $response->assertRedirect('https://www.fixr.com/consumer-validation/v2/abc123?campaign=xyz');
    }

    public function test_it_does_not_redirect_non_matching_paths(): void
    {
        $response = $this->get('/unmatched-path');

        $response->assertNotFound();
    }
}
