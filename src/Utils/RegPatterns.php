<?php declare(strict_types=1);

namespace App\Utils;

class RegPatterns
{
    public const MAGAZINE_NAME = '/^[a-zA-Z0-9_]{2,25}$/';
    public const USERNAME = '/^[a-zA-Z0-9_-]{1,30}$/';
    public const LOCAL_MAGAZINE = '/^@\w{2,25}\b/';
    public const LOCAL_USER = '/^@[a-zA-Z0-9_-]{1,30}\b/';
    public const AP_MAGAZINE = '/^(!\w{2,25})(@)(([a-z0-9|-]+\.)*[a-z0-9|-]+\.[a-z]+)/';
    public const AP_USER = '/^(@\w{1,30})(@)(([a-z0-9|-]+\.)*[a-z0-9|-]+\.[a-z]+)/';
    public const LOCAL_TAG = '/\B#([a-zA-ZżźćńółęąśŻŹĆĄŚĘŁÓŃ0-9_]{2,45})/';
}
