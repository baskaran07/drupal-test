<?php

namespace Drupal\bluehornet;

use Dawehner\Bluehornet\Client;
use Drupal\Core\Site\Settings;
use GuzzleHttp\ClientInterface;

/**
 * Connects \Dawehner\Bluehornet\Client with Drupal settings.
 */
class ClientFactory {

  /**
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Creates a new ClientFactory instance.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   */
  public function __construct(ClientInterface $httpClient) {
    $this->httpClient = $httpClient;
  }

  /**
   * Creates a Client instance using Drupal configuration.
   *
   * @return \Dawehner\Bluehornet\Client
   *
   * @throws \Exception
   *   Thrown when settings are missing.
   */
  public function create() {
    if (!$api_key = Settings::get('bluehornet_api_key')) {
      throw new \Exception('Missing api key');
    }
    elseif (!$shared_secret = Settings::get('bluehornet_shared_secret')) {
      throw new \Exception('Missing shared secret');
    }

    return new Client($api_key, $shared_secret, $this->httpClient);
  }

}
