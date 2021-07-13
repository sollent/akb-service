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

/**
 * Class AkbParseImagesCommand
 */
class AkbDownloadImagesCommand extends Command
{
    public const BASE_URL = 'https://xn----7sbdfotj5che.xn--90ais/%D0%90%D0%BA%D0%BA%D1%83%D0%BC%D1%83%D0%BB%D1%8F%D1%82%D0%BE%D1%80%D1%8B/%D0%9A%D0%BE%D0%BC%D0%BC%D0%B5%D1%80%D1%87%D0%B5%D1%81%D0%BA%D0%B8%D0%B9-%D0%B0%D0%B2%D1%82%D0%BE%D1%82%D1%80%D0%B0%D0%BD%D1%81%D0%BF%D0%BE%D1%80%D1%82';

    private string $downloadPath;

    /**
     * @var string
     */
    protected static $defaultName = 'app:akb-images-download';

    /**
     * @var string
     */
    protected static $defaultDescription = 'Parser command for akb images';

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
        $this->downloadPath = getcwd() . '/public/products';
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $withoutPhotoAkb = $this->entityManager->getRepository(AkbEntity::class)->findBy([
            'photoPath' => null
        ]);

        $i = 1;
        foreach ($withoutPhotoAkb as $akbEntity) {
            $url = $akbEntity->getImageUrlPath();
            $file = $this->curl_get_contents($url);
            $filename = \uniqid('', true) . '.' . $this->getImageExtension($file);
            file_put_contents(
                $this->downloadPath . '/' . $filename,
                $file
            );
            $akbEntity->setPhotoPath($filename);

            $percent = round($i / \count($withoutPhotoAkb), 3) * 100;

            echo "\n";
            echo "------ $percent% ------";
            echo "\n";
        }

        $this->entityManager->flush();

        $io = new SymfonyStyle($input, $output);
        $io->success('Success');
        return Command::SUCCESS;
    }

    /**
     * @param $url
     *
     * @return bool|string
     */
    private function curl_get_contents($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function getImageExtension(string $file): string
    {
        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        $fileExtension = $fileInfo->buffer($file);

        $ext = \explode('/', $fileExtension);

        return \end($ext);
    }

}
