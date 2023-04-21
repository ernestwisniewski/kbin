<?php

declare(strict_types=1);

namespace App\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    use FactoryTrait;

    protected ArrayCollection $users;
    protected ArrayCollection $magazines;
    protected ArrayCollection $entries;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->users = new ArrayCollection();
        $this->magazines = new ArrayCollection();
        $this->entries = new ArrayCollection();
    }
}
