<?php

namespace App\Command;

use App\DTO\EntryDto;
use App\Repository\EntryRepository;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use App\Service\EntryManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use DOMElement;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;

class AwesomeBotFixtures extends Command
{
    protected static $defaultName = 'kbin:awesome-bot:fixtures:create';

    public function __construct(
        private EntryManager $entryManager,
        private UserRepository $userRepository,
        private MagazineRepository $magazineRepository,
        private EntryRepository $entryRepository,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('prepare', InputArgument::OPTIONAL)
            ->setDescription('This command allows you to create awesome-bot entries.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $result = [];

        $entries = [
            [
                'username'       => 'awesome-vue-bot',
                'magazine_name'  => 'vue',
                'magazine_title' => 'Vue',
                'url'            => 'https://github.com/vuejs/awesome-vue',
                'tags'           => 'h3',
            ],
            [
                'username'       => 'awesome-rust-bot',
                'magazine_name'  => 'rust',
                'magazine_title' => 'Rust',
                'url'            => 'https://github.com/rust-unofficial/awesome-rust',
                'tags'           => 'h2,h3',
            ],
            [
                'username'       => 'awesome-cardano-bot',
                'magazine_name'  => 'cardano',
                'magazine_title' => 'Cardano',
                'url'            => 'https://github.com/CardanoUmbrella/awesome-cardano',
                'tags'           => 'h3,h4',
            ],
            [
                'username'       => 'awesome-svelte-bot',
                'magazine_name'  => 'svelte',
                'magazine_title' => 'Svelte',
                'url'            => 'https://github.com/TheComputerM/awesome-svelte',
                'tags'           => 'h2,h3',
            ],
            [
                'username'       => 'awesome-react-bot',
                'magazine_name'  => 'react',
                'magazine_title' => 'React',
                'url'            => 'https://github.com/enaqx/awesome-react',
                'tags'           => 'h4,h5',
            ],
            [
                'username'       => 'awesome-ethereum-bot',
                'magazine_name'  => 'ethereum',
                'magazine_title' => 'Ethereum',
                'url'            => 'https://github.com/bekatom/awesome-ethereum',
                'tags'           => '',
            ],
            [
                'username'       => 'awesome-golang-bot',
                'magazine_name'  => 'golang',
                'magazine_title' => 'Golang',
                'url'            => 'https://github.com/avelino/awesome-go',
                'tags'           => 'h2',
            ],
            [
                'username'       => 'awesome-haskell-bot',
                'magazine_name'  => 'haskell',
                'magazine_title' => 'Haskell',
                'url'            => 'https://github.com/krispo/awesome-haskell',
                'tags'           => 'h2',
            ],
            [
                'username'       => 'awesome-flutter-bot',
                'magazine_name'  => 'flutter',
                'magazine_title' => 'Flutter',
                'url'            => 'https://github.com/Solido/awesome-flutter',
                'tags'           => 'h3, h4',
            ],
            [
                'username'       => 'awesome-erlang-bot',
                'magazine_name'  => 'erlang',
                'magazine_title' => 'Erlang',
                'url'            => 'https://github.com/drobakowski/awesome-erlang',
                'tags'           => 'h2',
            ],
            [
                'username'       => 'awesome-php-bot',
                'magazine_name'  => 'php',
                'magazine_title' => 'PHP',
                'url'            => 'https://github.com/ziadoz/awesome-php',
                'tags'           => 'h3',
            ],
            [
                'username'       => 'awesome-testing-bot',
                'magazine_name'  => 'testing',
                'magazine_title' => 'Testing',
                'url'            => 'https://github.com/TheJambo/awesome-testing',
                'tags'           => 'h3',
            ],
            [
                'username'       => 'awesome-code-review-bot',
                'magazine_name'  => 'codeReview',
                'magazine_title' => 'Code review',
                'url'            => 'https://github.com/joho/awesome-code-review',
                'tags'           => 'h2',
            ],
            [
                'username'       => 'awesome-bitcoin-bot',
                'magazine_name'  => 'bitcoin',
                'magazine_title' => 'Bitcoin',
                'url'            => 'https://github.com/igorbarinov/awesome-bitcoin',
                'tags'           => 'h2',
            ],
            [
                'username'       => 'awesome-fediverse-bot',
                'magazine_name'  => 'fediverse',
                'magazine_title' => 'Fediverse',
                'url'            => 'https://github.com/emilebosch/awesome-fediverse',
                'tags'           => '',
            ],
        ];

        foreach ($entries as $entry) {
            try {
                $command   = $this->getApplication()->find('kbin:user:create');
                $arguments = [
                    'username' => $entry['username'],
                    'email'    => $entry['username'].'@karab.in',
                    'password' => $entry['username'].'!test',
                ];
                $input     = new ArrayInput($arguments);
                $command->run($input, $output);
            } catch (\Exception $e) {
            }

            try {
                $command   = $this->getApplication()->find('kbin:awesome-bot:magazine:create');
                $arguments = [
                    'username'       => 'ernest',
                    'magazine_name'  => $entry['magazine_name'],
                    'magazine_title' => $entry['magazine_title'],
                    'url'            => $entry['url'],
                    'tags'           => $entry['tags'],
                ];
                $input     = new ArrayInput($arguments);
                $command->run($input, $output);
            } catch (\Exception $e) {
            }

            $user     = $this->userRepository->findOneByUsername($entry['username']);
            $magazine = $this->magazineRepository->findOneByName($entry['magazine_name']);

            $tags = $entry['tags'] ? explode(',', $entry['tags']) : [];

            if (!$user) {
                $io->error('User not exist.');

                continue;
            } elseif (!$magazine) {
                $io->error('Magazine not exist.');

                continue;
            }

            $browser = new HttpBrowser(HttpClient::create());
            $crawler = $browser->request('GET', $entry['url']);

            $content = $crawler->filter('.markdown-body')->first()->children();

            $tags = array_flip($tags);
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
                            'magazine' => $magazine,
                            'user'     => $user,
                            'title'    => $li->nodeValue,
                            'url'      => $li->firstChild->getAttribute('href'),
                            'badges'   => new ArrayCollection(array_filter($tags, fn($v) => is_string($v))),
                        ];
                    };
                }
            }
        }

        shuffle($result);
        foreach ($result as $item) {
            if (false === filter_var($item['url'], FILTER_VALIDATE_URL)) {
                continue;
            }

            if ($this->entryRepository->findOneByUrl($item['url'])) {
                continue;
            }

            $this->entryManager->create(
                (new EntryDto())->create(
                    $item['magazine'],
                    $item['user'],
                    substr($item['title'], 0, 255),
                    $item['url'],
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $item['badges']
                ),
                $item['user']
            );
        }

        return Command::SUCCESS;
    }
}
