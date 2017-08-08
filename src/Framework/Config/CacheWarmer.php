<?php
declare(strict_types=1);
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

namespace Shopware\Framework\Config;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class CacheWarmer implements CacheWarmerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ConfigServiceInterface
     */
    private $configService;

    public function __construct(Connection $connection, ConfigServiceInterface $configService)
    {
        $this->connection = $connection;
        $this->configService = $configService;
    }

    public function isOptional(): bool
    {
        return false;
    }

    /**
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir): void
    {
        $shops = $this->getShops();

        foreach ($shops as $shop) {
            $this->configService->getByShop($shop);
        }
    }

    private function getShops(): array
    {
        $builder = $this->connection->createQueryBuilder();

        return $builder->select(['shop.*'])
                ->from('s_core_shops', 'shop')
                ->execute()
                ->fetchAll();
    }
}
