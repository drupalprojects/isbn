<?php

/**
 * @file
 * Provides integration with Feeds module (http://drupal.org/project/feeds).
 */

/**
 * Implements hook_feeds_processor_targets().
 */
function isbn_feeds_processor_targets($entity_type, $bundle) {
    $targets = array();
    
    foreach (field_info_instances($entity_type, $bundle) as $name => $instance) {
        $info = field_info_field($name);
        
        if ($info['type'] == 'isbn') {
            $targets[$name] = array(
                'name' => check_plain($instance['label']),
                'callback' => 'isbn_feeds_set_target',
                'description' => t('The @label field of the node.', array('@label' => $instance['label'])),
            );
        }
    }
    
    return $targets;
}

/**
 * Callback to set target value.
 */
function isbn_feeds_set_target(FeedsSource $source, $entity, $target, array $values, array $mapping) {
    $language = $mapping['language'];

    $field = isset($entity->$target) ? $entity->$target : array($language => array());
    
    // Iterate over all values.
    foreach ($values as $value) {
        if (is_object($value) && ($value instanceof FeedsElement)) {
            $value = $value->getValue();
        }
        $field[$language][] = array('isbn' => preg_replace('/([^xX0-9]*)/', "", $value));
    }
    
    $entity->$target = $field;
}
