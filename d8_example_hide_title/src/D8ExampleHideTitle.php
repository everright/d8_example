<?php
/**
 * @file
 * Contains \Drupal\d8_example_hide_title\D8ExampleHideTitle.
 */
namespace Drupal\d8_example_hide_title;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\NodeType;
/**
 * Provides the title generation functionality.
 */
class D8ExampleHideTitle {

  const DEFAULT_PATTERN = 'Auto title of [node:content-type:name] on [current-date:short]';

  /**
   * Sets the automatically generated title for the node.
   */
  public static function autoTitle(EntityInterface &$entity) {
    if (!self::isTitleHidden($entity->bundle())) {
      return;
    }

    $typeEntity = NodeType::load($entity->bundle());
    $pattern = $typeEntity->getThirdPartySetting('d8_example_hide_title', 'd8_example_hide_title_pattern', self::DEFAULT_PATTERN);

    $title = self::patternProcessor($pattern, $entity);
    // Ensure the generated title isn't too long.
    $title = Unicode::substr($title, 0, 255);
    $entity->set('title', $title);
    return $title;
  }

  /**
   * Sets title component to be hidden.
   */
  public static function hiddenTitle($bundle, $form_mode = 'default') {
    if (self::isTitleHidden($bundle, $form_mode)) {
      return;
    }
    entity_get_form_display('node', $bundle, $form_mode)
      ->removeComponent('title')
      ->save();
  }

  /**
   * Helper function to check title field hidden or not.
   *
   * @return a boolean value.
   */
  public static function isTitleHidden($bundle, $form_mode = 'default') {
    $form_display = entity_get_form_display('node', $bundle, $form_mode);
    return !$form_display->getComponent('title');
  }

  /**
   * Helper function to generate the title according to the settings.
   *
   * @return a title string.
   */
  protected static function patternProcessor($pattern, EntityInterface &$entity) {
    // Replace tokens.
    $token = \Drupal::token();
    $output = $token->replace($pattern, array('node' => $entity), array(
      'sanitize' => FALSE,
      'clear' => TRUE,
    ));
    // Strip tags.
    $output = preg_replace('/[\t\n\r\0\x0B]/', '', strip_tags($output));
    return $output;
  }

}
