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

namespace Drupal\app_attribute\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Exception\UnsupportedEntityTypeDefinitionException;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\user\UserInterface;

/**
 * Defines the attribute entity.
 *
 * @ContentEntityType(
 *     id = "attribute",
 *     label = @Translation("Attribute"),
 *     entity_keys = {
 *         "id" = "id",
 *         "bundle" = "type",
 *         "label" = "name",
 *         "uuid" = "uuid",
 *         "status" = "status",
 *         "langcode" = "langcode",
 *         "published" = "status",
 *         "uid" = "uid",
 *         "owner" = "uid",
 *     },
 *     handlers = {
 *         "storage_schema" = "Drupal\app_attribute\AttributeStorageSchema",
 *         "views_data" = "Drupal\app_attribute\AttributeEntityViewsData",
 *         "translation" = "Drupal\app_attribute\AttributeTranslationHandler",
 *         "form" = {
 *             "default" = "Drupal\app_attribute\Form\AttributeForm",
 *             "default" = "Drupal\app_attribute\Form\AttributeForm",
 *             "default" = "Drupal\app_attribute\Form\AttributeForm",
 *             "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *         },
 *         "route_provider" = {
 *             "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *         },
 *         "list_builder" = "Drupal\app_attribute\AttributeListBuilder",
 *     },
 *     links = {
 *         "canonical" = "/attribute/{attribute}",
 *         "add-page" = "/attribute/add",
 *         "add-form" = "/attribute/add/{attribute_type}",
 *         "edit-form" = "/attribute/{attribute}/edit",
 *         "delete-form" = "/attribute/{attribute}/delete",
 *         "collection" = "/admin/content/attribute",
 *     },
 *     base_table = "attribute",
 *     data_table = "attribute_field_data",
 *     translatable = TRUE,
 *     admin_permission = "administer attributes",
 *     bundle_entity_type = "attribute_type",
 *     field_ui_base_route = "entity.attribute_type.edit_form",
 * )
 */
class Attribute extends ContentEntityBase implements AttributeInterface
{
    use EntityOwnerTrait;
    use EntityPublishedTrait;

    /**
     * {@inheritdoc}
     */
    public function preSave(EntityStorageInterface $storage): void
    {
        parent::preSave($storage);

        foreach (array_keys($this->getTranslationLanguages()) as $langCode) {
            $translation = $this->getTranslation($langCode);

            $translationUser = $translation->getOwner();

            // If no owner has been set explicitly, make the admin user the owner.
            if (!($translationUser instanceof UserInterface)) {
                $translation->setOwnerId(1);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return (string) $this->bundle();
    }

    /**
     * @throws UnsupportedEntityTypeDefinitionException
     *
     * @return FieldDefinitionInterface[]
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {
        $fields = parent::baseFieldDefinitions($entity_type);
        $fields += static::ownerBaseFieldDefinitions($entity_type);
        $fields += static::publishedBaseFieldDefinitions($entity_type);

        $fields['name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Name'))
            ->setDescription(t('Name.'))
            ->setRequired(true)
            ->setTranslatable(true)
            ->setSetting('max_length', 255)
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);

        $fields['weight'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Weight'))
            ->setDescription(t('Weight for sorting.'))
            ->setRequired(true)
            ->setTranslatable(true)
            ->setDefaultValue(0)
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);

        if ($fields['uid'] instanceof BaseFieldDefinition) {
            $fields['uid']
                ->setLabel(t('Authored by'))
                ->setDescription(t('The username of the content author.'))
                ->setDisplayOptions('view', [
                    'label' => 'hidden',
                    'type' => 'author',
                    'weight' => 0,
                ])
                ->setDisplayOptions('form', [
                    'type' => 'entity_reference_autocomplete',
                    'weight' => 5,
                    'settings' => [
                        'match_operator' => 'CONTAINS',
                        'size' => '60',
                        'placeholder' => '',
                    ],
                ])
                ->setDisplayConfigurable('form', true);
        }

        if ($fields['status'] instanceof BaseFieldDefinition) {
            $fields['status']
                ->setDisplayOptions('form', [
                    'type' => 'boolean_checkbox',
                    'settings' => [
                        'display_label' => true,
                    ],
                    'weight' => 120,
                ])
                ->setTranslatable(true)
                ->setDisplayConfigurable('form', true);
        }

        $fields['created'] = BaseFieldDefinition::create('created')
            ->setLabel(t('Authored on'))
            ->setDescription(t('The time that the attribute was created.'))
            ->setDisplayOptions('view', [
                'label' => 'hidden',
                'type' => 'timestamp',
                'weight' => 0,
            ])
            ->setTranslatable(true)
            ->setDisplayOptions('form', [
                'type' => 'datetime_timestamp',
                'weight' => 10,
            ])
            ->setDisplayConfigurable('form', true);

        $fields['changed'] = BaseFieldDefinition::create('changed')
            ->setLabel(t('Changed'))
            ->setTranslatable(true)
            ->setDescription(t('The time that the attribute was last edited.'));

        return $fields;
    }

    public function getName(): ?string
    {
        return $this->get('name')->value;
    }

    public function getCreatedTime(): int
    {
        return (int) $this->get('created')->value;
    }

    public function getChangedTime(): int
    {
        return (int) $this->get('changed')->value;
    }

    public function getChangedTimeAcrossTranslations(): int
    {
        return $this->getChangedTime();
    }

    public function setCreatedTime(int $timestamp): AttributeInterface
    {
        $this->set('created', $timestamp);

        return $this;
    }

    public function setChangedTime($timestamp): AttributeInterface
    {
        $this->set('changed', $timestamp);

        return $this;
    }

    public function isPublished(): bool
    {
        return self::PUBLISHED === $this->get('status')->value;
    }

    public function setPublished(): AttributeInterface
    {
        $this->set('status', self::PUBLISHED);

        return $this;
    }

    public function setUnpublished(): AttributeInterface
    {
        $this->set('status', self::NOT_PUBLISHED);

        return $this;
    }

    public function setName(string $name): AttributeInterface
    {
        $this->set('name', $name);

        return $this;
    }
}
