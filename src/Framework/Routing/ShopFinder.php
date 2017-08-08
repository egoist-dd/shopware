<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Framework\Routing;

use Doctrine\DBAL\Connection;
use Symfony\Component\Routing\RequestContext;

class ShopFinder
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function findShopByRequest(RequestContext $requestContext): array
    {
        $query = $this->connection->createQueryBuilder();

        $query->select(['shop.*', 'locale.locale']);
        $query->from('s_core_shops', 'shop');
        $query->innerJoin('shop', 's_core_locales', 'locale', 'locale.id=shop.locale_id');

        $shops = $query->execute()->fetchAll();

        $paths = [];

        foreach ($shops as &$shop) {
            $base = $shop['base_url'] ?? $shop['base_path'];

            $shop['base_url'] = rtrim($shop['base_url'], '/') . '/';
            $shop['base_path'] = rtrim($shop['base_path'], '/') . '/';

            $base = rtrim($base, '/') . '/';
            $paths[$base] = $shop;
        }

        $url = rtrim($requestContext->getPathInfo(), '/') . '/';

        // direct hit
        if (array_key_exists($url, $paths)) {
            return $paths[$url];
        }

        // reduce shops to which base url is the beginning of the request
        $paths = array_filter($paths, function ($baseUrl) use ($url) {
            return strpos($url, $baseUrl) === 0;
        }, ARRAY_FILTER_USE_KEY);

        // determine most matching shop base url
        $lastBaseUrl = '';
        $bestMatch = current($shops);
        foreach ($paths as $baseUrl => $shop) {
            if (strlen($baseUrl) > strlen($lastBaseUrl)) {
                $bestMatch = $shop;
            }

            $lastBaseUrl = $baseUrl;
        }

        return $bestMatch;
    }
}
