<?php declare(strict_types = 1);

namespace App\Command\AwesomeBot;

use App\DTO\EntryDto;
use App\Repository\EntryRepository;
use App\Repository\MagazineRepository;
use App\Repository\UserRepository;
use App\Service\EntryManager;
use Doctrine\Common\Collections\ArrayCollection;
use DOMElement;
use Exception;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->addOption('prepare', null, InputOption::VALUE_OPTIONAL)
            ->setDescription('This command allows you to create awesome-bot entries.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $result = [];

        foreach ($this->getEntries() as $entry) {
            if ($input->getOption('prepare')) {
                $this->preapreMagazines($output, $entry);
                continue;
            }

            $user     = $this->userRepository->findOneByUsername($entry['username']);
            $magazine = $this->magazineRepository->findOneByName($entry['magazine_name']);

            $tags = $entry['tags'] ? explode(',', $entry['tags']) : [];

            if (!$user) {
                $io->error("User {$entry['username']} not exist.");

                return Command::FAILURE;
            } elseif (!$magazine) {
                $io->error("Magazine {$entry['magazine_name']} not exist.");

                return Command::FAILURE;
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

                        if (!$li->firstChild) {
                            var_dump('a');
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
                    }
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

            $dto           = new EntryDto();
            $dto->magazine = $item['magazine'];
            $dto->user     = $item['user'];
            $dto->title    = substr($item['title'], 0, 255);
            $dto->url      = $item['url'];
            $dto->badges   = $item['badges'];

            $entry = $this->entryManager->create($dto, $item['user']);

            $io->info("(m/{$entry->magazine->name}) {$entry->title}");

//            sleep(rand(2,30));
        }

        return Command::SUCCESS;
    }

    private function getEntries(): array
    {
        return [
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
                'username'       => 'awesome-vue-bot',
                'magazine_name'  => 'vue',
                'magazine_title' => 'Vue',
                'url'            => 'https://github.com/vuejs/awesome-vue',
                'tags'           => 'h3',
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
            [
                'username'       => 'awesome-eventstorming-bot',
                'magazine_name'  => 'eventstorming',
                'magazine_title' => 'Eventstorming',
                'url'            => 'https://github.com/mariuszgil/awesome-eventstorming',
                'tags'           => 'h2',
            ],
            [
                'username'       => 'awesome-javascript-bot',
                'magazine_name'  => 'javascript',
                'magazine_title' => 'Javascript',
                'url'            => 'https://github.com/sorrycc/awesome-javascript',
                'tags'           => 'h2,h3',
            ],
            [
                'username'       => 'awesome-unity-bot',
                'magazine_name'  => 'unity',
                'magazine_title' => 'Unity 3D',
                'url'            => 'https://github.com/RyanNielson/awesome-unity',
                'tags'           => 'h2',
            ],
            [
                'username'       => 'awesome-selfhosted-bot',
                'magazine_name'  => 'selfhosted',
                'magazine_title' => 'selfhosted',
                'url'            => 'https://github.com/awesome-selfhosted/awesome-selfhosted',
                'tags'           => 'h3',
            ],
            [
                'username'       => 'awesome-dotnet-bot',
                'magazine_name'  => 'dotnet',
                'magazine_title' => 'dotnet',
                'url'            => 'https://github.com/quozd/awesome-dotnet',
                'tags'           => 'h2',
            ],
            [
                'username'       => 'awesome-java-bot',
                'magazine_name'  => 'java',
                'magazine_title' => 'Java',
                'url'            => 'https://github.com/akullpp/awesome-java',
                'tags'           => 'h3',
            ],
            [
                'username'       => 'awesome-macos-bot',
                'magazine_name'  => 'macOS',
                'magazine_title' => 'macOS',
                'url'            => 'https://github.com/iCHAIT/awesome-macOS',
                'tags'           => 'h3',
            ],
            [
                'username'       => 'awesome-laravel-bot',
                'magazine_name'  => 'laravel',
                'magazine_title' => 'Laravel',
                'url'            => 'https://github.com/chiraggude/awesome-laravel',
                'tags'           => 'h2,h5',
            ],
            [
                'username'       => 'awesome-ux-bot',
                'magazine_name'  => 'ux',
                'magazine_title' => 'UX',
                'url'            => 'https://github.com/netoguimaraes/awesome-ux',
                'tags'           => 'h2,h3',
            ],
            [
                'username'       => 'awesome-symfony-bot',
                'magazine_name'  => 'symfony',
                'magazine_title' => 'Symfony',
                'url'            => 'https://github.com/sitepoint-editors/awesome-symfony',
                'tags'           => 'h2',
            ],
            [
                'username'       => 'awesome-design-bot',
                'magazine_name'  => 'design',
                'magazine_title' => 'Design',
                'url'            => 'https://github.com/gztchan/awesome-design',
                'tags'           => 'h2',
            ],
            [
                'username'       => 'awesome-wordpress-bot',
                'magazine_name'  => 'wordpress',
                'magazine_title' => 'wordpress',
                'url'            => 'https://github.com/miziomon/awesome-wordpress',
                'tags'           => 'h2,h4',
            ],
            [
                'username'       => 'awesome-drupal-bot',
                'magazine_name'  => 'drupal',
                'magazine_title' => 'Drupal',
                'url'            => 'https://github.com/mrsinguyen/awesome-drupal',
                'tags'           => 'h2,h3',
            ],
        ];
    }

    private function preapreMagazines(OutputInterface $output, array $entry)
    {
        try {
            $command   = $this->getApplication()->find('kbin:user:create');
            $arguments = [
                'username' => $entry['username'],
                'email'    => $entry['username'].'@karab.in',
                'password' => md5((string) rand()),
            ];
            $input     = new ArrayInput($arguments);
            $command->run($input, $output);
        } catch (Exception $e) {
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
        } catch (Exception $e) {
        }
    }
}
