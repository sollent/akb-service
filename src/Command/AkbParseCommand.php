<?php

namespace App\Command;

use App\Entity\AkbCategory;
use App\Entity\AkbEntity;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class AkbParseCommand
 */
class AkbParseCommand extends Command
{
    public const DOMAIN = 'https://xn----7sbdfotj5che.xn--90ais';

    public const BASE_URL = 'https://xn----7sbdfotj5che.xn--90ais/%D0%90%D0%BA%D0%BA%D1%83%D0%BC%D1%83%D0%BB%D1%8F%D1%82%D0%BE%D1%80%D1%8B/%D0%9A%D0%BE%D0%BC%D0%BC%D0%B5%D1%80%D1%87%D0%B5%D1%81%D0%BA%D0%B8%D0%B9-%D0%B0%D0%B2%D1%82%D0%BE%D1%82%D1%80%D0%B0%D0%BD%D1%81%D0%BF%D0%BE%D1%80%D1%82';

    public const BASE_MEDIA_URI = '/uploads/products/thumbnails/large/';

    /**
     * @var string
     */
    protected static $defaultName = 'app:akb-parse';

    /**
     * @var string
     */
    protected static $defaultDescription = 'Parser command for akb service';

    private EntityManagerInterface $entityManager;

    private SerializerInterface $serializer;

    /**
     * AkbCategoryParseCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     */
    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Simple parser realization
        $client = new Client();
        $response = $client->get(self::BASE_URL);

        $crawler = new Crawler($response->getBody()->getContents());

        $productsArray = $crawler->filter('ul.product-list li article a')->each(function (Crawler $crawler) {
            $categoryString = $crawler->filter('h2.product-name span.product-brand')->text() ?? null;
            $existCategory = $this->entityManager->getRepository(AkbCategory::class)->findOneBy([
                'title' => $categoryString
            ]);

            $title = $crawler->filter('h2.product-name')->text();

            $characteristicKeys = $crawler->filter('dl.product-summary dt')->each(function (Crawler $crawler) {
                return $crawler->text();
            });
            $characteristicValues = $crawler->filter('dl.product-summary dd')->each(function (Crawler $crawler) {
                return $crawler->text();
            });

            $imageUri = $crawler->filter('figure.product-thumbnail img')->attr('src');
            $explodedImageUri = \explode('/', $imageUri);
            $imageFilename = $explodedImageUri[\count($explodedImageUri) - 2] . '/' . \end($explodedImageUri);
            $imagePath = \sprintf('%s%s%s', self::DOMAIN, self::BASE_MEDIA_URI, $imageFilename);

            $prices = $crawler->filter('table.price-discount td.prc')->text();
            $explodedPrices = explode('/', $prices);
            $discountPrice = intval($explodedPrices[0]);
            $price = intval($explodedPrices[1]);

            return [
                'category' => $existCategory,
                'title' => $title,
                'shortDescription' => $this->buildShortDescription($characteristicKeys, $characteristicValues),
                'imageUrlPath' => $imagePath,
                'discountPrice' => $discountPrice,
                'price' => $price
            ];
        });

        foreach ($productsArray as $productItem) {
            $akbEntity = new AkbEntity();
            $akbEntity
                ->setTitle($productItem['title'])
                ->setCategory($productItem['category'] ?? null)
                ->setShortDescription($productItem['shortDescription'] ?? null)
                ->setDiscountPrice($productItem['discountPrice'] ?? null)
                ->setPrice($productItem['price'])
                ->setImageUrlPath($productItem['imageUrlPath']);

            $this->entityManager->persist($akbEntity);
        }

        $this->entityManager->flush();

        $io = new SymfonyStyle($input, $output);
        $io->success('Success');
        return Command::SUCCESS;
    }

    /**
     * @param array $charKeys
     * @param array $charValues
     *
     * @return string
     */
    private function buildShortDescription(array $charKeys, array $charValues): string
    {
        $characteristics = \array_combine($charKeys, $charValues);
        $resultCharacteristics = [];
        foreach ($characteristics as $key => $value) {
            $resultCharacteristics[] = \sprintf('%s: %s', $key, $value);
        }

        return \implode(', ', $resultCharacteristics);
    }
}
