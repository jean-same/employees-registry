<?php
namespace App\Normalizer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Entity normalizer.
 */
class EntityNormalizer implements DenormalizerInterface
{
    public function __construct(protected EntityManagerInterface $em) { }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return 0 === strpos($type, 'BBOnline\\Entity\\') && is_numeric($data);
    }

    /**
     * {@inheritDoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return $this->em->find($class, $data);
    }
}