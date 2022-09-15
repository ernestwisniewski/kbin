<?php

namespace App\Command\Update\Async;

class NoteVisibilityMessage
{
    public function __construct(public int $id, public string $class)
    {
    }
}