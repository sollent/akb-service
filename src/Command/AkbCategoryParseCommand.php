<?php

namespace App\Command;

use App\Entity\AkbCategory;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class AkbCategoryParseCommand
 */
class AkbCategoryParseCommand extends Command
{
    public const BASE_URL = 'https://xn----7sbdfotj5che.xn--90ais/%D0%90%D0%BA%D0%BA%D1%83%D0%BC%D1%83%D0%BB%D1%8F%D1%82%D0%BE%D1%80%D1%8B/%D0%9A%D0%BE%D0%BC%D0%BC%D0%B5%D1%80%D1%87%D0%B5%D1%81%D0%BA%D0%B8%D0%B9-%D0%B0%D0%B2%D1%82%D0%BE%D1%82%D1%80%D0%B0%D0%BD%D1%81%D0%BF%D0%BE%D1%80%D1%82';

    /**
     * @var string
     */
    protected static $defaultName = 'app:akb-category-parse';

    /**
     * @var string
     */
    protected static $defaultDescription = 'Parser command for akb categories';

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
        // Simple category parser realization
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

        $categoryNames = $filters[0];

        array_map(function (string $categoryName) {
            $category = new AkbCategory();
            $category->setTitle($categoryName);

            $this->entityManager->persist($category);
        }, $categoryNames);

        $this->entityManager->flush();

        $io = new SymfonyStyle($input, $output);
        $io->success('Success');
        return Command::SUCCESS;
    }
}
