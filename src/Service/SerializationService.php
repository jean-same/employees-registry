<?php

namespace App\Service;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class SerializationService
{
    public function __construct(
        private SerializerInterface $serializer
        )
    {
    }

    /**
     * Deserialize request content into an object of the specified type.
     *
     * @param mixed $requestContent The content of the request to deserialize.
     * @param string $type The type of object to deserialize into.
     * @param object|null $objectToPopulate (Optional) The object to populate with deserialized data.
     * @return object The deserialized object.
     */
    public function deserializeRequest($requestContent, string $type, $objectToPopulate = null)
    {
        $context = $objectToPopulate ? [AbstractNormalizer::OBJECT_TO_POPULATE => $objectToPopulate] : [];

        return $this->serializer->deserialize($requestContent, $type, 'json', $context);
    }
    
}
