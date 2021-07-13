<?php

namespace App\Controller;

use App\Entity\AkbEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use VK\Client\VKApiClient;
use VK\Exceptions\VKClientException;
use VK\Exceptions\VKOAuthException;
use VK\OAuth\Scopes\VKOAuthUserScope;
use VK\OAuth\VKOAuth;
use VK\OAuth\VKOAuthDisplay;
use VK\OAuth\VKOAuthResponseType;

/**
 * Experimental controller for working with VK API
 *
 * Class VkController
 */
class VkController extends AbstractController
{
    protected const CLIENT_ID = 7902250;
    protected const REDIRECT_URI  = 'https://oauth.vk.com/blank.html';
    protected const STATE  = 'secret_state_code';
    protected const CLIENT_SECRET  = '3PwUUfzvq26r5ZHkEqBp';

    protected const AKB_GROUP_ID = 205079234;
    protected const AKB_GROUP_ID_NEGATIVE = -205079234;

    public function index()
    {
        $oauth = new VKOAuth();
        $display = VKOAuthDisplay::POPUP;
        $scope = array(
            VKOAuthUserScope::WALL,
            VKOAuthUserScope::GROUPS,
            VKOAuthUserScope::PHOTOS,
            VKOAuthUserScope::MARKET
        );

        $browserUrl = $oauth->getAuthorizeUrl(
            VKOAuthResponseType::CODE,
            self::CLIENT_ID,
            self::REDIRECT_URI,
            $display,
            $scope,
            self::STATE
        );

        return $this->redirect($browserUrl);
    }

    public function getCode(Request $request)
    {
    }

    public function getToken(Request $request)
    {
        // dynamic value
        $code = '06ece2ad7c390611c6';
        $oauth = new VKOAuth();

        try {
            $response = $oauth->getAccessToken(
                self::CLIENT_ID,
                self::CLIENT_SECRET,
                self::REDIRECT_URI,
                $code
            );
        } catch (VKClientException $e) {
            dump($e);exit();
        } catch (VKOAuthException $e) {
            dump($e->getMessage());exit();
        }

        dump($response['access_token']);exit();
    }

    public function post()
    {
        $vk = new VKApiClient();
        //dynamic value
        $access_token = '8539f972939a07210d460bd88a98dc2881fd7b84f1404b5925faef0641fb3cbcad5b77fd12a20b8b41152';

        $categoryID = 205; // Avto akb
        $albumId = 2;

        /** @var AkbEntity[] $products */
        $products = $this->getDoctrine()->getRepository(AkbEntity::class)->findBy([], [], 2);

        foreach ($products as $product) {
            // upload product photo
            $address = $vk->photos()->getMarketUploadServer($access_token, [
                'group_id' => self::AKB_GROUP_ID,
                'main_photo' => 1
            ]);

            $mainPhoto = $vk->getRequest()->upload($address['upload_url'], 'photo', 'products/' . $product->getPhotoPath());
            $response_save_photo = $vk->photos()->saveMarketPhoto($access_token, array(
                'group_id' => self::AKB_GROUP_ID,
                'server' => $mainPhoto['server'],
                'photo' => $mainPhoto['photo'],
                'hash' => $mainPhoto['hash'],
                'crop_data' => $mainPhoto['crop_data'],
                'crop_hash' => $mainPhoto['crop_hash']
            ));

            $photoID = $response_save_photo[0]['id'];

            $response = $vk->market()->add($access_token, [
                'owner_id' => self::AKB_GROUP_ID_NEGATIVE,
                'name' => $product->getTitle(),
                'description' => \sprintf(
                    "%s\n\nПроизводитель: %s\n\nПри обмене цена - %s BYN",
                    $product->getShortDescription(),
                    $product->getCategory()->getTitle(),
                    $product->getDiscountPrice()
                ),
                'category_id' => $categoryID,
                'price' => $product->getDiscountPrice(),
                'old_price' => $product->getPrice(),
                'main_photo_id' => $photoID
            ]);

            $itemID = $response['market_item_id'];

            $response = $vk->market()->addToAlbum($access_token, [
                'owner_id' => self::AKB_GROUP_ID_NEGATIVE,
                'item_id' => $itemID,
                'album_ids' => [$albumId]
            ]);

            dump($response);
        }

        exit();
    }

}
