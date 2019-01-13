<?php

namespace NationBuilderClient;

use GuzzleHttp\Client;

/**
 * NationBuilder Client.
 */
class NationBuilderClient {

  /**
   * @var string
   */
  private $apiVersion;

  /**
   * @var \GuzzleHttp\Client
   */
  private $guzzle;

  /**
   * @var array
   */
  private $options;

  /**
   * @var array
   */
  private $response;

  /**
   * @var string
   */
  private $slug;

  /**
   * @var string
   */
  private $token;

  /**
   * NationBuilderClient constructor.
   *
   * @param string $slug
   *   The nation slug.
   * @param string $token
   *   API token.
   * @param string $apiVersion
   *   API version. Defaults to 'v1'.
   */
  public function __construct(string $slug, string $token, string $apiVersion = 'v1') {

    $this->apiVersion = $apiVersion;
    $this->slug = $slug;
    $this->token = $token;
    $this->response = [];

    $this->guzzle = new Client([
      'base_uri' => "https://$slug.nationbuilder.com/api/$apiVersion/",
    ]);

    $this->options = [
      'headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
      ],
      'query' => [
        'access_token' => $token,
        'fire_webhooks' => 'false',
      ],
    ];
  }

  /**
   * Make request to NationBuilder.
   *
   * @param string $method
   *   The request method. Defaults to 'GET'.
   * @param string $uri
   *   The request endpoint. Defaults to 'people/count'.
   * @param string $body
   *   The request body. Defaults to NULL.
   *
   * @return array
   *   Response body contents.
   */
  public function request(string $method = 'GET', $uri = 'people/count', string $body = NULL) {

    try {

      $options = $this->options;

      if (!empty($body)) {
        $options['body'] = $body;
      }

      /* @var \GuzzleHttp\Psr7\Response $response */
      $response = $this->guzzle->request($method, $uri, $options);

      $contents = $response->getBody()->getContents();
      $this->response = json_decode($contents, TRUE);
    }
    catch (\Exception $exception) {
      $this->response = ['error' => $exception->getMessage()];
    }

    return $this->response;
  }

}
