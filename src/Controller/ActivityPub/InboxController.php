<?php declare(strict_types=1);

namespace App\Controller\ActivityPub;

use App\Message\ActivityPub\ActivityMessage;
use App\Repository\ApActivityRepository;
use App\Service\ActivityPub\MarkdownConverter;
use App\Service\MentionManager;
use League\HTMLToMarkdown\HtmlConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

class InboxController
{
    public function __construct(
        private MessageBusInterface $bus,
        private MentionManager $mentionManager,
        private ApActivityRepository $repository,
        private MarkdownConverter $markdownConverter
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
//        $headers = [
//            'cdn-loop'          => [0 => 'cloudflare',],
//            'cf-ray'            => [0 => '735f30da48c79b39-FRA',],
//            'cf-ipcountry'      => [0 => 'DE',],
//            'content-length'    => [0 => '243',],
//            'content-type'      => [0 => 'application/ld+json; profile="https://www.w3.org/ns/activitystreams"',],
//            'user-agent'        => [0 => '(Pixelfed/0.11.3; +https://pixelfed.krbn.pl)',],
//            'digest'            => [0 => 'SHA-256=tSbQexNXVn9NtYcHxGI6DAXewWMeS+PupreH6ZfFOVY=',],
//            'cf-connecting-ip'  => [0 => '2a01:4f8:1c0c:71a5::1',],
//            'x-forwarded-proto' => [0 => 'https',],
//            'date'              => [0 => 'Fri, 05 Aug 2022 11:28:42 GMT',],
//            'host'              => [0 => 'dev.karab.in',],
//            'x-forwarded-host'  => [0 => 'dev.karab.in',],
//            'accept'            => [0 => 'application/activity+json, application/json',],
//            'accept-encoding'   => [0 => 'gzip',],
//            'signature'         => [0 => 'keyId="https://pixelfed.krbn.pl/users/admin#main-key",headers="(request-target) date host accept digest content-type user-agent",algorithm="rsa-sha256",signature="PsicH5fDtEzZu+vegSfkaBynlgIGTey8cEYiZFtCyjZ5TwbpjlFPukIf48a+ms0UlHAmjTqC+XTYvbyf6/W3xW+pJ5dIXSlc/fYyRo6F8oQfcSdFH9az/zvyDbjfAly90nauucYEBrHXf382VwYZZa4MLICYcyy4CkPUwdY1VlX1Y2+oLb4sy9wGx7gXAb9gzwp1oAA80wJPOV59QE1Y5yw+nEkL8OQqquL2VRp+BQyNEFnwVmlpfP0Q8sqrc2fDGV9wKLBmHL5jH+X/IvkUfx12ZF7CDojgutp3QzgXYckrB6o/aIC2h6Zybq2E9c/LJeBRc13oIi/nU4vHcY4cBw=="',],
//            'cf-visitor'        => [0 => '{"scheme":"https"}',],
//            'x-forwarded-for'   => [0 => '162.158.91.106',],
//            'x-php-ob-level'    => [0 => '1',],
//        ];
//
//$body = '{"@context":["https://www.w3.org/ns/activitystreams",{"ostatus":"http://ostatus.org#","atomUri":"ostatus:atomUri","inReplyToAtomUri":"ostatus:inReplyToAtomUri","conversation":"ostatus:conversation","sensitive":"as:sensitive","toot":"http://joinmastodon.org/ns#","votersCount":"toot:votersCount","blurhash":"toot:blurhash","focalPoint":{"@container":"@list","@id":"toot:focalPoint"}}],"id":"https://101010.pl/users/ernest/statuses/108771776341780181","type":"Note","summary":null,"inReplyTo":"https://mastodon.internet-czas-dzialac.pl/users/arek/statuses/108770638597320024","published":"2022-08-05T18:53:11Z","url":"https://101010.pl/@ernest/108771776341780181","attributedTo":"https://101010.pl/users/ernest","to":["https://www.w3.org/ns/activitystreams#Public"],"cc":["https://101010.pl/users/ernest/followers","https://mastodon.internet-czas-dzialac.pl/users/arek"],"sensitive":false,"atomUri":"https://101010.pl/users/ernest/statuses/108771776341780181","inReplyToAtomUri":"https://mastodon.internet-czas-dzialac.pl/users/arek/statuses/108770638597320024","conversation":"tag:mastodon.internet-czas-dzialac.pl,2022-08-05:objectId=3746:objectType=Conversation","content":"\u003cp\u003e\u003cspan class=\"h-card\"\u003e\u003ca href=\"https://mastodon.internet-czas-dzialac.pl/@arek\" class=\"u-url mention\"\u003e@\u003cspan\u003earek\u003c/span\u003e\u003c/a\u003e\u003c/span\u003e właśnie kilka dni temu wspominałeś tu o YunoHost, sprawdziłem, totalny gamechanger. popchnął moją prace nad federacją karabina o tygodnie. doskonale sprawdza się jako mały domowy lab. skrypt instalacyjny yuno również dodany do todo-listy z wysokim priorytetem, dzięki.\u003c/p\u003e","contentMap":{"pl":"\u003cp\u003e\u003cspan class=\"h-card\"\u003e\u003ca href=\"https://mastodon.internet-czas-dzialac.pl/@arek\" class=\"u-url mention\"\u003e@\u003cspan\u003earek\u003c/span\u003e\u003c/a\u003e\u003c/span\u003e właśnie kilka dni temu wspominałeś tu o YunoHost, sprawdziłem, totalny gamechanger. popchnął moją prace nad federacją karabina o tygodnie. doskonale sprawdza się jako mały domowy lab. skrypt instalacyjny yuno również dodany do todo-listy z wysokim priorytetem, dzięki.\u003c/p\u003e"},"attachment":[{"type":"Document","mediaType":"image/jpeg","url":"https://storage.waw.cloud.ovh.net/v1/AUTH_74714a37e6e24c7fb695d79be309da62/101010public/media_attachments/files/108/771/775/326/424/520/original/35dab55585c7fd71.jpeg","name":null,"blurhash":"UNEoc0mltQS6~WMxtQxuD%ozM{RjIUtRo3WA","width":2005,"height":1034}],"tag":[{"type":"Mention","href":"https://mastodon.internet-czas-dzialac.pl/users/arek","name":"@arek@mastodon.internet-czas-dzialac.pl"}],"replies":{"id":"https://101010.pl/users/ernest/statuses/108771776341780181/replies","type":"Collection","first":{"type":"CollectionPage","next":"https://101010.pl/users/ernest/statuses/108771776341780181/replies?only_other_accounts=true\u0026page=true","partOf":"https://101010.pl/users/ernest/statuses/108771776341780181/replies","items":[]}}}';

        $test = '@mrk@101010.pl @jaczad@tfl.net.pl eh, cieszę się, że nie zajmuję @ernest się frontendem. Jeżeli w tym całym galimatiasie html/php/js/css... musiałbym jeszcze pamiętać o aria to faktycznie witki opadają :D';

        dd($this->mentionManager->extract($test));

        $this->bus->dispatch(new ActivityMessage($body, $headers));

//        $this->bus->dispatch(new ActivityMessage($request->getContent(), $request->headers->all()));

        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
