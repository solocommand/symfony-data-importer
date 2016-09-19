<?php

namespace As3\SymfonyData\Import\Source;

use As3\SymfonyData\Import\Source;
use Cygnus\ApiSuiteBundle\ApiClient\Google\ApiClientYoutube;
use Cygnus\ModlrBundle\Component\Utility;
use Symfony\Component\HttpFoundation\Response;

class Youtube extends Source
{
    /**
     * {@inheritdoc}
     */
    const SOURCE_KEY = 'youtube';

    /**
     * @var ApiClientYoutube
     */
    private $apiClient;

    /**
     *
     */
    private $nextPageToken;

    /**
     * DI Constructor
     *
     * @param ApiClientYoutube    $apiClient
     */
    public function __construct(ApiClientYoutube $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * {@inheritdoc}
     */
    public function count($from, array $criteria = [])
    {
        return $this->handle($this->apiClient->{$this->getApiMethod($from)}($criteria, [], [], 50), true);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieve($from, array $criteria = [], array $fields = [], array $sort = [], $limit = 200, $skip = 0)
    {
        if (null !== $this->nextPageToken) {
            $criteria['pageToken'] = $this->nextPageToken;
        }
        return $this->handle($this->apiClient->{$this->getApiMethod($from)}($criteria, $fields, $sort, $limit, $skip));
    }

    /**
     * Returns the apiClient method for the specified parameters
     *
     * @param   string  $type   count|retrieve
     * @param   string  $from   The endpoint name to attempt to use with the api client
     * @throws  \InvalidArgumentException   if the api client does not support the requested method
     *
     * @return  string  The method name
     */
    private function getApiMethod($from)
    {
        $method = sprintf('retrieve%s', Utility::classify($from));
        if (!method_exists($this->apiClient, $method)) {
            throw new \InvalidArgumentException(sprintf('Method %s does not exist on %s!', $method, get_class($this->apiClient)));
        }
        return $method;
    }

    /**
     * Handles a response from the ApiClient
     */
    private function handle(Response $response, $count = false)
    {
        $responseData = json_decode($response->getContent(), true);

        if (!$response->isSuccessful()) {
            // @todo get better error
            throw new \RuntimeException($response->getContent());
        }

        if (true === $count) {
            switch ($responseData['kind']) {
                case 'youtube#playlistItemListResponse':
                    return (int) $responseData['pageInfo']['totalResults'];
                    break;

                default:
                    throw new \Exception('unsupported count operation');
                    break;
            }
        }

        if (isset($responseData['nextPageToken'])) {
            $this->nextPageToken = $responseData['nextPageToken'];
        } else {
            $this->nextPageToken = null;
        }
        return $responseData['items'];
    }
}
