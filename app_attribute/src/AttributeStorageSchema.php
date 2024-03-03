<?php

declare(strict_types=1);

/**
 * This file is a part of Drupal Headless App.
 *
 * @author Muhammed Naushad <muhammed.naushad@ekino.com>
 * @copyright Copyright (c) Ekino. All rights reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Drupal\app_attribute;

use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the attribute schema handler.
 */
class AttributeStorageSchema extends SqlContentEntityStorageSchema
{
    /**
     * {@inheritdoc}
     *
     * @param array<mixed> $column_mapping
     *
     * @return array<mixed>
     */
    protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, $table_name, array $column_mapping): array
    {
        $schema = parent::getSharedTableFieldSchema($storage_definition, $table_name, $column_mapping);
        $fieldName = $storage_definition->getName();

        if ('attribute' === $table_name) {
            switch ($fieldName) {
                case 'name':
                    $schema['fields'][$fieldName]['not null'] = true;
                    break;

                case 'created':
                    $this->addSharedTableFieldIndex($storage_definition, $schema, true);
                    break;
            }
        }

        return $schema;
    }
}
