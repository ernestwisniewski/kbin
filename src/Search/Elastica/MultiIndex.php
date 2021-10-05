<?php

declare(strict_types=1);

/*
 * Created by Exploit.cz <insekticid AT exploit.cz>
 */

namespace App\Search\Elastica;

use Elastica\Exception\InvalidException;
use Elastica\Index;
use Elastica\ResultSet\BuilderInterface;
use Elastica\Search;

class MultiIndex extends Index
{
    /**
     * Array of indices.
     *
     * @var array
     */
    protected $_indices = [];

    /**
     * Adds a index to the list.
     *
     * @param \Elastica\Index|string $index Index object or string
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return $this
     */
    public function addIndex($index)
    {
        if ($index instanceof Index) {
            $index = $index->getName();
        }

        if (!is_scalar($index)) {
            throw new InvalidException('Invalid param type');
        }

        $this->_indices[] = (string) $index;

        return $this;
    }

    /**
     * Add array of indices at once.
     *
     * @param array $indices
     *
     * @return $this
     */
    public function addIndices(array $indices = [])
    {
        foreach ($indices as $index) {
            $this->addIndex($index);
        }

        return $this;
    }

    /**
     * Return array of indices.
     *
     * @return array List of index names
     */
    public function getIndices()
    {
        return $this->_indices;
    }

    /**
     * @param string|array|\Elastica\Query $query
     * @param int|array                    $options
     * @param BuilderInterface             $builder
     *
     * @return Search
     */
    public function createSearch($query = '', $options = null, BuilderInterface $builder = null): \Elastica\Search
    {
        $search = new Search($this->getClient(), $builder);
//        $search->addIndex($this);
        $search->addIndices($this->getIndices());
        $search->setOptionsAndQuery($options, $query);

        return $search;
    }
}
