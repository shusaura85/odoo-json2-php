<?php
namespace OdooPHP;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use OdooPHP\OdooException;
use Psr\Http\Message\ResponseInterface;

class BaseClient
{
    private GuzzleClient $client;
    private ?ResponseInterface $lastResponse = null;
    private ?string $lastResponseContents = null;

    public function __construct(string $baseUri, protected string $apiKey, protected ?string $database = null, bool $sslVerify = true)
    {
        $this->client = new GuzzleClient([
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json',
                'Connection' => 'close',
            ],
            'base_uri' => $baseUri,
            'verify' => $sslVerify,
        ]);
    }

    public function call(string $model, string $method, array $params = []): mixed
    {
        try {
            $url = sprintf('/json/2/%s/%s', $model, $method);
            
            $headers = [
                'Authorization' => 'Bearer ' . $this->apiKey,
            ];
            
            // Add X-Odoo-Database header if database is provided
            if ($this->database !== null) {
                $headers['X-Odoo-Database'] = $this->database;
            }
			
            $response = $this->client->request('POST', $url, [
                'headers' => $headers,
                'json' => $params,
            ]);

            $this->lastResponse = $response;
            $this->lastResponseContents = null;
			

            return match ($response->getStatusCode()) {
                200 => $this->makeResponse($response),
                default => throw new OdooException($response, "HTTP Error: " . $response->getStatusCode(), $response->getStatusCode())
            };
        } catch (GuzzleException $e) {
            throw new OdooException(null, $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function lastResponse(): ?ResponseInterface
    {
        return $this->lastResponse;
    }

    public function getLastResponseContents(): ?string
    {
        return $this->lastResponseContents;
    }

    private function makeResponse(ResponseInterface $response): mixed
    {
        $body = $response->getBody();
        $contents = $body->getContents();
        $this->lastResponseContents = $contents;
        $body->close();

        if (empty($contents)) {
            throw new OdooException($response, "Received an empty response from Odoo server.", null);
        }

        $json = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new OdooException($response, "Failed to decode JSON response: " . json_last_error_msg(), null);
        }

        // JSON-2 API returns data directly or may have error structure
        if (isset($json['error'])) {
            $message = "Odoo Exception";
            if (isset($json['error']['message'])) {
                $message = $json['error']['message'];
            }
            if (isset($json['error']['data']['message'])) {
                $message .= ': ' . $json['error']['data']['message'];
            }
            throw new OdooException($response, $message, $json['error']['code'] ?? null);
        }

        // Return the result directly (JSON-2 API returns data directly, not wrapped in 'result')
        return $json;
    }
}

