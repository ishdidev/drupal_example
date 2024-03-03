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

use Drupal\app_attribute\Entity\Attribute;
use Drupal\app_attribute\Entity\AttributeInterface;
use Drupal\content_translation\ContentTranslationHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/*
 * Defines the translation handler for attribute.
 */
class AttributeTranslationHandler extends ContentTranslationHandler
{
    /**
     * {@inheritdoc}
     *
     * @param array<mixed> $form
     */
    public function entityFormAlter(array &$form, FormStateInterface $form_state, EntityInterface $entity): void
    {
        parent::entityFormAlter($form, $form_state, $entity);

        if (isset($form['content_translation'])) {
            $form['content_translation']['status']['#access'] = false;
            $form['content_translation']['name']['#access'] = false;
            $form['content_translation']['created']['#access'] = false;
        }

        $formObject = $form_state->getFormObject();
        $formLangcode = $formObject->getFormLangcode($form_state); /** @phpstan-ignore-line */
        $translations = $entity->getTranslationLanguages(); /** @phpstan-ignore-line */
        $statusTranslatable = null;

        // Change the submit button labels
        if (!$entity->isNew() && (!isset($translations[$formLangcode]) || \count($translations) > 1)) {
            foreach ($entity->getFieldDefinitions() as $propertyName => $definition) { /* @phpstan-ignore-line */
                if ('status' === $propertyName) {
                    $statusTranslatable = $definition->isTranslatable();
                }
            }
            if (isset($statusTranslatable)) {
                if (isset($form['actions']['submit'])) {
                    $form['actions']['submit']['#value'] .= ' '.($statusTranslatable ? t('(this translation)') : t('(all translations)'));
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function entityFormTitle(EntityInterface $entity): TranslatableMarkup|null|string
    {
        $type_name = $entity->bundle();

        return t('<em>Edit @type</em> @title', ['@type' => $type_name, '@title' => $entity->label()]);
    }

    /**
     * {@inheritdoc}
     *
     * @param array<mixed> $form
     */
    public function entityFormEntityBuild($entity_type, EntityInterface $entity, array $form, FormStateInterface $form_state): void
    {
        if ($form_state->hasValue('content_translation') && $entity instanceof AttributeInterface) {
            $translation = &$form_state->getValue('content_translation');
            $translation['status'] = $entity->isPublished();
            $account = $entity->getOwner();
            $translation['uid'] = $account->id() ?: 0;
            $translation['created'] = $this->dateFormatter->format($entity->getCreatedTime(), 'custom', 'Y-m-d H:i:s O');
        }

        parent::entityFormEntityBuild($entity_type, $entity, $form, $form_state);
    }
}
