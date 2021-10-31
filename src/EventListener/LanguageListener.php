<?php declare(strict_types = 1);

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class LanguageListener
{
    public function __construct(public string $lang)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $request->setLocale($this->lang);

            return;
        }

        $lang       = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        $acceptLang = ['pl'];
        $locale     = in_array($lang, $acceptLang) ? $lang : $this->lang;

        $request->setLocale($locale);
    }
}
