<?php
/**
 * SubscriptionStatus
 *
 * PHP version 5
 *
 * @category Class
 * @package  OpenAPI\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * MONEI API v1
 *
 * <p>The MONEI API is organized around <a href=\"https://en.wikipedia.org/wiki/Representational_State_Transfer\">REST</a>. Our API has predictable resource-oriented URLs, accepts JSON-encoded request bodies, returns JSON-encoded responses, and uses standard HTTP response codes, authentication, and verbs.</p> <h4 id=\"base-url\">Base URL:</h4> <p><a href=\"https://api.monei.com/v1\">https://api.monei.com/v1</a></p> <h4 id=\"client-libraries\">Client libraries:</h4> <ul> <li><a href=\"https://github.com/MONEI/monei-php-sdk\">PHP SDK</a></li> <li><a href=\"https://github.com/MONEI/monei-python-sdk\">Python SDK</a></li> <li><a href=\"https://github.com/MONEI/monei-node-sdk\">Node.js SDK</a></li> <li><a href=\"https://postman.monei.com/\">Postman Collection</a></li> </ul>
 *
 * The version of the OpenAPI document: 1.1.0
 * 
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 4.3.1
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace OpenAPI\Client\Model;
use \OpenAPI\Client\ObjectSerializer;

/**
 * SubscriptionStatus Class Doc Comment
 *
 * @category Class
 * @description The status of the subscription.
 * @package  OpenAPI\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class SubscriptionStatus
{
    /**
     * Possible values of this enum
     */
    const PENDING = 'PENDING';
    const EXPIRED = 'EXPIRED';
    const TRIALING = 'TRIALING';
    const ACTIVE = 'ACTIVE';
    const PAST_DUE = 'PAST_DUE';
    const PAUSED = 'PAUSED';
    const CANCELED = 'CANCELED';
    
    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::PENDING,
            self::EXPIRED,
            self::TRIALING,
            self::ACTIVE,
            self::PAST_DUE,
            self::PAUSED,
            self::CANCELED,
        ];
    }
}


