<?php

namespace App\Command;

use App\Entity\AkbBrand;
use App\Entity\AkbCategory;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class AkbBrandParseCommand
 */
class AkbBrandParseCommand extends Command
{
    public const BASE_URL = 'https://xn----7sbdfotj5che.xn--90ais/%D0%90%D0%BA%D0%BA%D1%83%D0%BC%D1%83%D0%BB%D1%8F%D1%82%D0%BE%D1%80%D1%8B/%D0%9C%D0%BE%D1%82%D0%BE%D1%82%D0%B5%D1%85%D0%BD%D0%B8%D0%BA%D0%B0';

    public const CATEGORY_ID = 3;

    /**
     * @var string
     */
    protected static $defaultName = 'app:akb-brand-parse';

    /**
     * @var string
     */
    protected static $defaultDescription = 'Parser command for akb brand';

    private EntityManagerInterface $entityManager;

    /**
     * AkbCategoryParseCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Simple brand parser realization
        $client = new Client();
        $response = $client->get(self::BASE_URL);

        $crawler = new Crawler($response->getBody()->getContents());


        $filters = $crawler->filter('.filter-groups dd.filter-options')->each(function (Crawler $crawler, $i) {
            if ($i === 0) {
                return $crawler->filter('ul li a')->each(function (Crawler $crawler) {
                   return (string) $crawler->text();
                });
            }
        });

        $brandNames = $filters[0];

        array_map(function (string $brandName) {
            $brand = new AkbBrand();
            $brand->setTitle($brandName);
            $brand->setCategory($this->entityManager->getRepository(AkbCategory::class)->find(self::CATEGORY_ID));

            $this->entityManager->persist($brand);
        }, $brandNames);

        $this->entityManager->flush();

        $io = new SymfonyStyle($input, $output);
        $io->success('Success');
        return Command::SUCCESS;
    }
}
