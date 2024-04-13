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

    public function deserializeRequest($requestContent, string $type, $objectToPopulate = null)
    {
        $context = $objectToPopulate ? [AbstractNormalizer::OBJECT_TO_POPULATE => $objectToPopulate] : [];

        return $this->serializer->deserialize($requestContent, $type, 'json', $context);
    }
    
}
