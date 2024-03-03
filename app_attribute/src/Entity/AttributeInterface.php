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

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Defines the interface for Attribute.
 */
interface AttributeInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface, EntityPublishedInterface
{
    public const PUBLISHED = 1;

    public const NOT_PUBLISHED = 0;

    /**
     * get attribute name.
     */
    public function getName(): ?string;

    /**
     * set attribute name.
     */
    public function setName(string $name): self;

    public function getCreatedTime(): int;

    public function getType(): string;
}
