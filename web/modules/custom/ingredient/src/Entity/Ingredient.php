<?php

namespace Drupal\ingredient\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Ingredient entity.
 *
 * @ingroup ingredient
 *
 * @ContentEntityType(
 *   id = "ingredient",
 *   label = @Translation("Ingredient"),
 *   handlers = {
 *     "storage" = "Drupal\ingredient\IngredientStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\ingredient\IngredientListBuilder",
 *     "views_data" = "Drupal\ingredient\Entity\IngredientViewsData",
 *     "translation" = "Drupal\ingredient\IngredientTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\ingredient\Form\IngredientForm",
 *       "add" = "Drupal\ingredient\Form\IngredientForm",
 *       "edit" = "Drupal\ingredient\Form\IngredientForm",
 *       "delete" = "Drupal\ingredient\Form\IngredientDeleteForm",
 *     },
 *     "access" = "Drupal\ingredient\IngredientAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\ingredient\IngredientHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "ingredient",
 *   data_table = "ingredient_field_data",
 *   revision_table = "ingredient_revision",
 *   revision_data_table = "ingredient_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer ingredient entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/ingredient/{ingredient}",
 *     "add-form" = "/admin/structure/ingredient/add",
 *     "edit-form" = "/admin/structure/ingredient/{ingredient}/edit",
 *     "delete-form" = "/admin/structure/ingredient/{ingredient}/delete",
 *     "version-history" = "/admin/structure/ingredient/{ingredient}/revisions",
 *     "revision" = "/admin/structure/ingredient/{ingredient}/revisions/{ingredient_revision}/view",
 *     "revision_revert" = "/admin/structure/ingredient/{ingredient}/revisions/{ingredient_revision}/revert",
 *     "translation_revert" = "/admin/structure/ingredient/{ingredient}/revisions/{ingredient_revision}/revert/{langcode}",
 *     "revision_delete" = "/admin/structure/ingredient/{ingredient}/revisions/{ingredient_revision}/delete",
 *     "collection" = "/admin/structure/ingredient",
 *   },
 *   field_ui_base_route = "ingredient.settings"
 * )
 */
class Ingredient extends RevisionableContentEntityBase implements IngredientInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the ingredient owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Ingredient entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
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
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Ingredient entity.'))
      ->setRevisionable(TRUE)
      ->setRequired(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['imgage'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Image'))
      ->setRevisionable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_with_summary')
      ->setLabel(t('Description'))
      ->setDescription(t('The description of the Ingredient.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue('')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['validation'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Validation status'))
      ->setDescription(t('A boolean indicating whether the Ingredient was validated by a moderator.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Ingredient is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
