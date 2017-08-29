<?php declare(strict_types=1);

namespace Shopware\Framework\Api2\Resource;

use Shopware\Framework\Api2\ApiFlag\Required;
use Shopware\Framework\Api2\Field\FkField;
use Shopware\Framework\Api2\Field\IntField;
use Shopware\Framework\Api2\Field\ReferenceField;
use Shopware\Framework\Api2\Field\StringField;
use Shopware\Framework\Api2\Field\BoolField;
use Shopware\Framework\Api2\Field\DateField;
use Shopware\Framework\Api2\Field\SubresourceField;
use Shopware\Framework\Api2\Field\LongTextField;
use Shopware\Framework\Api2\Field\LongTextWithHtmlField;
use Shopware\Framework\Api2\Field\FloatField;
use Shopware\Framework\Api2\Field\TranslatedField;
use Shopware\Framework\Api2\Field\UuidField;
use Shopware\Framework\Api2\Resource\ApiResource;

class FilterRelationResource extends ApiResource
{
    public function __construct()
    {
        parent::__construct('filter_relation');
        
        $this->primaryKeyFields['uuid'] = (new UuidField('uuid'))->setFlags(new Required());
        $this->fields['groupId'] = (new IntField('group_id'))->setFlags(new Required());
        $this->fields['filterGroupUuid'] = (new StringField('filter_group_uuid'))->setFlags(new Required());
        $this->fields['optionId'] = (new IntField('option_id'))->setFlags(new Required());
        $this->fields['filterOptionUuid'] = (new StringField('filter_option_uuid'))->setFlags(new Required());
        $this->fields['position'] = (new IntField('position'))->setFlags(new Required());
    }
    
    public function getWriteOrder(): array
    {
        return [
            \Shopware\Framework\Api2\Resource\FilterRelationResource::class
        ];
    }
}