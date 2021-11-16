<?php declare(strict_types = 1);

namespace App\Command\AwesomeBot;

use App\DTO\EntryDto;
use App\Repository\EntryRepository;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use App\Service\EntryManager;
use Doctrine\Common\Collections\ArrayCollection;
use DOMElement;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;

class AwesomeBotEntries extends Command
{
    protected static $defaultName = 'kbin:awesome-bot:entries:create';

    // bin/console kbin:user:create awesome-vue-bot awesome-vue-bot@karab.in awesome-vue-bot
    // bin/console kbin:awesome-bot:magazine:create ernest vue Vue https://github.com/vuejs/awesome-vue h3
    // bin/console kbin:awesome-bot:entries:create awesome-vue-bot vue https://github.com/vuejs/awesome-vue h3

    public function __construct(
        private EntryManager $entryManager,
        private UserRepository $userRepository,
        private MagazineRepository $magazineRepository,
        private EntryRepository $entryRepository
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('This command allows you to create awesome-bot entries.')
            ->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('magazine_name', InputArgument::REQUIRED)
            ->addArgument('url', InputArgument::REQUIRED)
            ->addArgument('tags', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user     = $this->userRepository->findOneByUsername($input->getArgument('username'));
        $magazine = $this->magazineRepository->findOneByName($input->getArgument('magazine_name'));

        $tags = $input->getArgument('tags') ? explode(',', $input->getArgument('tags')) : [];

        if (!$user) {
            $io->error('User not exist.');

            return Command::FAILURE;
        } elseif (!$magazine) {
            $io->error('Magazine not exist.');

            return Command::FAILURE;
        }

        $browser = new HttpBrowser(HttpClient::create());
        $crawler = $browser->request('GET', $input->getArgument('url'));

        $content = $crawler->filter('.markdown-body')->first()->children();

        $tags   = array_flip($tags);
        $result = [];
        foreach ($content as $elem) {
            if (array_key_exists($elem->nodeName, $tags)) {
                $tags[$elem->nodeName] = $elem->nodeValue;
            }

            if ($elem->nodeName === 'ul') {
                foreach ($elem->childNodes as $li) {
                    /**
                     * @var $li DOMElement
                     */
                    if ($li->nodeName !== 'li') {
                        continue;
                    }

                    if ($li->firstChild->nodeName !== 'a') {
                        continue;
                    }

                    $result[] = [
                        'title'  => $li->nodeValue,
                        'url'    => $li->firstChild->getAttribute('href'),
                        'badges' => new ArrayCollection(array_filter($tags, fn($v) => is_string($v))),
                    ];
                }
            }
        }

        foreach ($result as $item) {
            if (false === filter_var($item['url'], FILTER_VALIDATE_URL)) {
                continue;
            }

            if ($this->entryRepository->findOneByUrl($item['url'])) {
                continue;
            }

            $dto           = new EntryDto();
            $dto->magazine = $magazine;
            $dto->user     = $user;
            $dto->title    = substr($item['title'], 0, 255);
            $dto->url      = $item['url'];
            $dto->badges   = $item['badges'];

            $this->entryManager->create($dto, $user);
        }

        return Command::SUCCESS;
    }
}
