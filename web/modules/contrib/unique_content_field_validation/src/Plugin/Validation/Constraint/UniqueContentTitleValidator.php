<?php

namespace Drupal\unique_content_field_validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the UniqueEntityTitle constraint.
 */
class UniqueContentTitleValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($item, Constraint $constraint) {
    $unique_validation_enabled = FALSE;
    $custom_message = NULL;
    $value = $item->getValue()[0]['value'];
    $entity = $item->getEntity();
    $entity_type = $entity->getEntityTypeId();
    switch ($entity_type) {
      case 'node':
        $entity_bundle = $entity->getType();
        /** @var \Drupal\node\Entity\NodeType $node_type */
        $node_type = $entity->type->entity;
        $unique_validation_enabled = $node_type->getThirdPartySetting('unique_content_field_validation', 'unique', FALSE);
        $custom_message = $node_type->getThirdPartySetting('unique_content_field_validation', 'unique_text', NULL);
        $unique_entity_title_label = \Drupal::config('core.base_field_override.node.' . $entity_bundle . '.title')->get('label') ?: 'Title';
        $unique_field_name = 'title';
        $bundle_field = 'type';
        $id_field = 'nid';
        break;

      case 'taxonomy_term':
        /** @var \Drupal\taxonomy\Entity\Vocabulary $vocabulary */
        $vocabulary = $entity->vid->entity;
        $entity_bundle = $entity->bundle();
        $unique_validation_enabled = $vocabulary->getThirdPartySetting('unique_content_field_validation', 'unique', FALSE);
        $custom_message = $vocabulary->getThirdPartySetting('unique_content_field_validation', 'unique_text', NULL);
        $unique_entity_title_label = 'Name';
        $unique_field_name = 'name';
        $bundle_field = 'vid';
        $id_field = 'tid';
        break;
    }
    if ($unique_validation_enabled && $this->uniqueValidation($unique_field_name, $value, $entity_type, $bundle_field, $entity_bundle, $id_field)) {
      $message = $custom_message ?: $constraint->message;
      $this->context->addViolation($message, ['%label' => $unique_entity_title_label, '%value' => $value]);
    }
  }

  /**
   * Unique validation.
   *
   * @param string $field_name
   *   The name of the field.
   * @param string $value
   *   Value of the field to check for uniqueness.
   * @param string $entity_type
   *   Id of the Entity Type.
   * @param string $bundle_field
   *   Field of the Entity type.
   * @param string $entity_bundle
   *   Bundle of the entity.
   * @param string $id_field
   *   Id field of the entity.
   *
   * @return bool
   *   Whether the entity is unique or not
   */
  private function uniqueValidation($field_name, $value, $entity_type, $bundle_field, $entity_bundle, $id_field) {
    if ($entity_type && $value && $field_name && $bundle_field && $entity_bundle) {
      $query = \Drupal::entityQuery($entity_type)
        ->condition($field_name, $value)
        ->condition($bundle_field, $entity_bundle)
        ->range(0, 1);
      // Exclude the current entity.
      if (!empty($id = $this->context->getRoot()->getEntity()->id())) {
        $query->condition($id_field, $id, '!=');
      }
      $entities = $query->execute();
      if (!empty($entities)) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
