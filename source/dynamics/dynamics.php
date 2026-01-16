<?php

namespace Cat\Aoc\CarregaCertificats\Core\Ticket\Clients\Dynamics;

require_once 'config.php';

use Cat\Aoc\CarregaCertificats\Core\Ticket\Clients\Dynamics\Model\TicketsResponse;
use Cat\Aoc\CarregaCertificats\Core\Ticket\Clients\Dynamics\Model\WhoAmIResponse;
use Cat\Aoc\CarregaCertificats\Core\Ticket\Clients\Http\HttpClientException;
use Cat\Aoc\CarregaCertificats\Core\Ticket\Clients\Http\Auth\Providers\Microsoft\MicrosoftTokenGenerator;
use Cat\Aoc\CarregaCertificats\Core\Ticket\Clients\Http\Rest\RestApiClient;

class DynamicsRestApiClient extends RestApiClient {

    public function __construct($client, $baseUrl, $userAgent, MicrosoftTokenGenerator $tokenGenerator) {
        parent::__construct(
            $client,
            $baseUrl . "/api/data/v9.2",
            $userAgent,
            $tokenGenerator
        );
    }

    public function whoAmI() {
        return $this->getAsync("/WhoAmI", WhoAmIResponse::class);
    }

    public function searchTickets($filter) {
        $encodedFilter = urlencode($filter);
        $path = "/incidents?\$select=aoc_gestio_certificat_aeat,ticketnumber,aoc_url,aoc_numero_serie&\$expand=aoc_organizacio(\$select=aoc_cif,aoc_ine10)&\$filter=" . $encodedFilter;
        return $this->getAsync($path, TicketsResponse::class);
    }

    protected function log($response) {
        if ($response->statusCode() == 429) {
            throw new DynamicsClientException("API rate limit exceeded. Status code: " . $response->statusCode());
        }
        return parent::log($response);
    }
}
?>