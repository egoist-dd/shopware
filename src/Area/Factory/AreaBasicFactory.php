<?php declare(strict_types=1);

namespace Shopware\Area\Factory;

use Doctrine\DBAL\Connection;
use Shopware\Area\Extension\AreaExtension;
use Shopware\Area\Struct\AreaBasicStruct;
use Shopware\Context\Struct\TranslationContext;
use Shopware\Framework\Factory\ExtensionRegistryInterface;
use Shopware\Framework\Factory\Factory;
use Shopware\Search\QueryBuilder;
use Shopware\Search\QuerySelection;

class AreaBasicFactory extends Factory
{
    const ROOT_NAME = 'area';
    const EXTENSION_NAMESPACE = 'area';

    const FIELDS = [
       'uuid' => 'uuid',
       'active' => 'active',
       'created_at' => 'created_at',
       'updated_at' => 'updated_at',
       'name' => 'translation.name',
    ];

    public function __construct(
        Connection $connection,
        ExtensionRegistryInterface $registry
    ) {
        parent::__construct($connection, $registry);
    }

    public function hydrate(
        array $data,
        AreaBasicStruct $area,
        QuerySelection $selection,
        TranslationContext $context
    ): AreaBasicStruct {
        $area->setUuid((string) $data[$selection->getField('uuid')]);
        $area->setActive((bool) $data[$selection->getField('active')]);
        $area->setCreatedAt(isset($data[$selection->getField('created_at')]) ? new \DateTime($data[$selection->getField('created_at')]) : null);
        $area->setUpdatedAt(isset($data[$selection->getField('updated_at')]) ? new \DateTime($data[$selection->getField('updated_at')]) : null);
        $area->setName((string) $data[$selection->getField('name')]);

        /** @var $extension AreaExtension */
        foreach ($this->getExtensions() as $extension) {
            $extension->hydrate($area, $data, $selection, $context);
        }

        return $area;
    }

    public function getFields(): array
    {
        $fields = array_merge(self::FIELDS, parent::getFields());

        return $fields;
    }

    public function joinDependencies(QuerySelection $selection, QueryBuilder $query, TranslationContext $context): void
    {
        $this->joinTranslation($selection, $query, $context);

        $this->joinExtensionDependencies($selection, $query, $context);
    }

    public function getAllFields(): array
    {
        $fields = array_merge(self::FIELDS, $this->getExtensionFields());

        return $fields;
    }

    protected function getRootName(): string
    {
        return self::ROOT_NAME;
    }

    protected function getExtensionNamespace(): string
    {
        return self::EXTENSION_NAMESPACE;
    }

    private function joinTranslation(
        QuerySelection $selection,
        QueryBuilder $query,
        TranslationContext $context
    ): void {
        if (!($translation = $selection->filter('translation'))) {
            return;
        }
        $query->leftJoin(
            $selection->getRootEscaped(),
            'area_translation',
            $translation->getRootEscaped(),
            sprintf(
                '%s.area_uuid = %s.uuid AND %s.language_uuid = :languageUuid',
                $translation->getRootEscaped(),
                $selection->getRootEscaped(),
                $translation->getRootEscaped()
            )
        );
        $query->setParameter('languageUuid', $context->getShopUuid());
    }
}
