<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Magazine;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('magazine_online')]
class MagazineOnlineComponent
{
    public Magazine $magazine;

    public function __construct(private string $mercurePublishUrl, private HttpClientInterface $httpClient,)
    {
    }

    public function getOnline(): int
    {
        try {
//            $resp = $this->httpClient->request('GET', "http://localhost:3000/.well-known/mercure/subscriptions", [
//                'auth_bearer' => '',
//            ]);
        } catch (\Exception $e) {
            return 0;
        }

        return 0;
    }
}
