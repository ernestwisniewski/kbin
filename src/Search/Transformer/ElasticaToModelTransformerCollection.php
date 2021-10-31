<?php declare(strict_types = 1);

namespace App\Search\Transformer;

use FOS\ElasticaBundle\HybridResult;
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;

/**
 * Holds a collection of transformers for an index wide transformation.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 * @author Insekticid <insekticid+fos@exploit.cz>
 */
class ElasticaToModelTransformerCollection implements ElasticaToModelTransformerInterface
{
    /**
     * @var ElasticaToModelTransformerInterface[]
     */
    protected $transformers = [];

    /**
     * @param ElasticaToModelTransformerInterface[] $transformers
     */
    public function __construct(array $transformers)
    {
        $this->transformers = $transformers;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectClass(): string
    {
        return implode(
            ',',
            array_map(function (ElasticaToModelTransformerInterface $transformer) {
                return $transformer->getObjectClass();
            }, $this->transformers)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function hybridTransform(array $elasticaObjects)
    {
        $objects = $this->transform($elasticaObjects);

        $result = [];
        for ($i = 0, $j = count($elasticaObjects); $i < $j; ++$i) {
            if (!isset($objects[$i])) {
                continue;
            }
            $result[] = new HybridResult($elasticaObjects[$i], $objects[$i]);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(array $elasticaObjects)
    {
        $sorted = [];
        foreach ($elasticaObjects as $object) {
            $sorted[$object->getIndex()][] = $object;
        }

        $transformed = [];
        foreach ($sorted as $type => $objects) {
            $transformedObjects = $this->transformers[$type]->transform($objects);
            $identifierGetter   = 'get'.ucfirst($this->transformers[$type]->getIdentifierField());
            $transformed[$type] = array_combine(
                array_map(
                    function ($o) use ($identifierGetter) {
                        return $o->$identifierGetter();
                    },
                    $transformedObjects
                ),
                $transformedObjects
            );
        }

        $result = [];
        foreach ($elasticaObjects as $object) {
            if (array_key_exists((string) $object->getId(), $transformed[$object->getIndex()])) {
                $result[] = $transformed[$object->getIndex()][(string) $object->getId()];
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierField(): string
    {
        return array_map(function (ElasticaToModelTransformerInterface $transformer) {
            return $transformer->getIdentifierField();
        }, $this->transformers)[0];
    }
}
