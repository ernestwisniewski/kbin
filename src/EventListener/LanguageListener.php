<?php declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class LanguageListener
{
    public function onKernelRequest(RequestEvent $event)
    {
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        $acceptLang = ['pl'];
        $locale = in_array($lang, $acceptLang) ? $lang : 'en';

        $request = $event->getRequest();
        $request->setLocale($locale);
    }
}
