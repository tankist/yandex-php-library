<?php

namespace Yandex\Market\Content;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Yandex\Common\AbstractServiceClient;
use Yandex\Common\Exception\ForbiddenException;
use Yandex\Market\Exception\MarketRequestException;

/**
 * Class ContentClient
 *
 * @package  Yandex\Market\Content
 *
 * @author   Victor Gryshko <victor.gryshko@gmail.com>
 * @created  03.08.14 17:14
 */
class ContentClient extends AbstractServiceClient
{

    /**
     * API domain
     *
     * @var string
     */
    protected $serviceDomain = 'api.content.market.yandex.ru';

    /**
     * Requested version of API
     * @var string
     */
    private $version = 'v2';

    /**
     * Region ID
     *
     * @see http://api.yandex.com/market/content/doc/dg/concepts/geo-resources.xml
     * @var int
     */
    protected $region = 0;

    /**
     * @param string $token access token
     */
    public function __construct($token = '')
    {
        $this->setAccessToken($token);
    }

    /**
     * Get url to service resource with parameters
     *
     * @param string $resource
     * @see http://api.yandex.com/market/content/doc/dg/concepts/method-call.xml
     * @return string
     */
    public function getServiceUrl($resource = '')
    {
        return $this->serviceScheme . '://' . $this->serviceDomain . '/'
        . $this->version . '/' . $resource;
    }

    /**
     * @return int
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param int $region
     * @return $this
     */
    public function setRegion($region)
    {
        $this->region = $region;
        return $this;
    }

    /**
     * Sends a request
     *
     * @param RequestInterface $request
     * @return Response
     * @throws ForbiddenException
     * @throws MarketRequestException
     */
    protected function sendRequest(RequestInterface $request)
    {
        try {

            $request = $this->prepareRequest($request);
            $response = $request->send();

        } catch (ClientErrorResponseException $ex) {

            $result = $request->getResponse();
            $code = $result->getStatusCode();
            $message = $result->getReasonPhrase();

            $body = $result->getBody(true);
            if ($body) {
                $jsonBody = json_decode($body);
                if ($jsonBody && isset($jsonBody->error) && isset($jsonBody->error->message)) {
                    $message = $jsonBody->error->message;
                }
            }

            if ($code === 403) {
                throw new ForbiddenException($message);
            }

            throw new MarketRequestException(
                'Service responded with error code: "' . $code . '" and message: "' . $message . '"'
            );
        }

        return $response;
    }

} 