<?php

namespace Drupal\bluehornet;

use Dawehner\Bluehornet\Client;
use Dawehner\Bluehornet\MethodRequests\LegacySendCampaign;
use Drupal\bluehornet\Entity\Campaign;

class BluehornetSender {

  /**
   * @var \Dawehner\Bluehornet\Client
   */
  protected $client;

  /**
   * Creates a new BluehornetSender instance.
   *
   * @param \Dawehner\Bluehornet\Client $client
   *   The bluehornet client.
   */
  public function __construct(Client $client) {
    $this->client = $client;
  }

  public function send(Campaign $campaign) {
    $request = $this->client->createRequest();
    $sendCampaign = new LegacySendCampaign();

    $sendCampaign->setMsubject($campaign->getSubject());
    $sendCampaign->setFromEmail($campaign->getFromEmail());
    $sendCampaign->setReplyEmail($campaign->getReplyEmail());
    $sendCampaign->setFromdesc($campaign->getFromDescription());
    $sendCampaign->setTextMbody($campaign->getFromTextMailBody());
    $sendCampaign->setRichMbody($campaign->getFromRichMailBody());

    if ($campaign->hasScheduleDate() && ($date = $campaign->getScheduleDate())) {
      $sendCampaign->setDate($date->format('YYYY-MM-DD'));
      $sendCampaign->setHour($date->format('G', [
        // "Obviously", hours in bluehornet are meant to be set in pacific
        // timezone.
        'timezone' => 'America/Los_Angeles',
      ]));
    }

    $request->addMethodCall($sendCampaign);
    $response = $this->client->sendRequest($request);
  }

}
