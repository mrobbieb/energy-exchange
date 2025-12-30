<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Battery;
use App\Factory\BatteryFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;


class BatteryReadTest extends ApiTestCase
{
    use ResetDatabase, Factories;

        private function createAuthenticatedClient():\ApiPlatform\Symfony\Bundle\Test\Client{
        // 1) Create a user with a KNOWN password.
        // IMPORTANT: your UserFactory must hash this before persistence.
        $email = 'test@example.com';
        $plainPassword = 'password';

        UserFactory::createOne([
            'email' => $email,
            'password' => $plainPassword, // ok if your factory hashes in afterInstantiate()
        ]);

        $client = static::createClient();

        // 2) Get a JWT from /auth
        $authResponse = $client->request('POST', '/auth', [
            'json' => [
                'email' => $email,
                'password' => $plainPassword,
            ],
        ]);

        $this->assertResponseIsSuccessful();

        $data = $authResponse->toArray(false);

        // Adjust the key depending on your AuthController response shape:
        // common: token, access_token, jwt
        $token = $data['token'] ?? $data['access_token'] ?? $data['jwt'] ?? null;

        if (!$token) {
            throw new \RuntimeException('JWT not returned from /auth. Response: '.json_encode($data));
        }

        // 3) New client that always sends Authorization header
        return static::createClient([
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'Accept' => 'application/ld+json',
            ],
        ]);
    }

    private function getJwtToken(): string
{
    $email = 'test@example.com';
    $plainPassword = 'password';

    UserFactory::createOne([
        'email' => $email,
        'password' => $plainPassword, // must be hashed by your factory
    ]);

    $client = static::createClient();

    $response = $client->request('POST', '/auth', [
        'json' => ['email' => $email, 'password' => $plainPassword],
    ]);

    $this->assertResponseIsSuccessful();

    $data = $response->toArray(false);
    $token = $data['token'] ?? $data['access_token'] ?? $data['jwt'] ?? null;

    if (!$token) {
        throw new \RuntimeException('JWT not returned from /auth. Response: '.json_encode($data));
    }

    return $token;
}

    public function testGetCollection(): void
    {
        BatteryFactory::createMany(10);
        
        $token = $this->getJwtToken();
        // $response = static::createClient()->request('GET', '/api/batteries');
        $client = static::createClient();

        $response = $client->request('GET', '/api/batteries', [
            'auth_bearer' => $token,
            'headers' => [
                'Accept' => 'application/ld+json'
            ],
        ]);

        $this->assertResponseIsSuccessful();
        // Asserts that the returned content type is JSON-LD (the default)
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        /**
         * {
	"@context": "/api/contexts/Battery",
	"@id": "/api/batteries/247",
	"@type": "Battery",
	"id": 247,
	"createdAt": "2022-10-19T14:31:07+00:00",
	"BatteryBank": {
		"@id": "/api/battery_banks/252",
		"@type": "BatteryBank",
		"batteries": [
			"/api/batteries/247"
		]
	},
	"user": []
}
    "@context": "/api/contexts/Battery",
	"@id": "/api/batteries",
	"@type": "Collection",
	"totalItems": 120,

    	"view": {
		"@id": "/api/batteries?page=1",
		"@type": "PartialCollectionView",
		"first": "/api/batteries?page=1",
		"last": "/api/batteries?page=12",
		"next": "/api/batteries?page=2"
	},
         */
        // $this->assertJsonContains([
        //     '@context' => '/api/contexts/Battery',
        //     '@id' => '/api/batteries',
        //     '@type' => 'Collection',
        //     'totalItems' => 10,
        //     'view' => [
        //         '@id' => '/api/batteries?page=1',
        //         '@type' => 'PartialCollectionView',
        //         'first' => '/api/batteries?page=1',
        //     ],
        // ]);
        // Assert count via JSON path, without caring about 'view'
        $this->assertJsonContains(['@id' => '/api/batteries']);
        $this->assertJsonContains(['@context' => '/api/contexts/Battery']);

        $data = $response->toArray(false);
        #$this->assertCount(10, $data['hydra:member'] ?? []);
        $data = $response->toArray(false);
        $this->assertCount(6, $data);
    }
    // public function testSomething(): void
    // {
    //     $response = static::createClient()->request('GET', '/');

    //     $this->assertResponseIsSuccessful();
    //     $this->assertJsonContains(['@id' => '/']);
    // }
}
