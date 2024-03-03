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

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Attribute Type.
 *
 * @ConfigEntityType(
 *     id = "attribute_type",
 *     label = @Translation("Attribute Type"),
 *     bundle_of = "attribute",
 *     entity_keys = {
 *         "id" = "id",
 *         "label" = "label",
 *         "uuid" = "uuid",
 *     },
 *     config_prefix = "attribute_type",
 *     config_export = {
 *         "id",
 *         "label",
 *         "description",
 *     },
 *     handlers = {
 *         "form" = {
 *             "default" = "Drupal\app_attribute\Form\AttributeTypeEntityForm",
 *             "add" = "Drupal\app_attribute\Form\AttributeTypeEntityForm",
 *             "edit" = "Drupal\app_attribute\Form\AttributeTypeEntityForm",
 *             "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *         },
 *         "route_provider" = {
 *             "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *         },
 *         "list_builder" = "Drupal\app_attribute\AttributeTypeListBuilder",
 *     },
 *     admin_permission = "administer site configuration",
 *     links = {
 *         "canonical" = "/admin/structure/attribute_type/{attribute_type}",
 *         "add-form" = "/admin/structure/attribute_type/add",
 *         "edit-form" = "/admin/structure/attribute_type/{attribute_type}/edit",
 *         "delete-form" = "/admin/structure/attribute_type/{attribute_type}/delete",
 *         "collection" = "/admin/structure/attribute_type",
 *     }
 * )
 */
class AttributeType extends ConfigEntityBundleBase implements AttributeTypeInterface
{
    protected ?string $id = null;

    protected ?string $description = null;

    /**
     * {@inheritdoc}
     */
    public function id(): ?string
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldCreateNewRevision(): bool
    {
        return false;
    }
}
