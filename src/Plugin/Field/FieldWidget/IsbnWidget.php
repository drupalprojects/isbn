<?php


/**
 * @file
 * Contains \Drupal\isbn\Plugin\Field\FieldWidget\IsbnWidget.
 */

namespace Drupal\isbn\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'isbn' widget.
 *
 * @FieldWidget(
 *   id = "isbn_widget",
 *   module = "isbn",
 *   label = @Translation("Format and validate ISBN fields"),
 *   field_types = {
 *     "isbn"
 *   }
 * )
 */
class IsbnWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = isset($items[$delta]->value) ? $items[$delta]->value : '';
    $element += array(
      '#type' => 'textfield',
      '#default_value' => $value,
      '#size' => 13,
      '#element_validate' => array(
        array($this, 'validate'),
      ),
    );
    return array('value' => $element);
  }

  /**
   * Validate the color text field.
   */
  public function validate($element, FormStateInterface $form_state) {
    $value = $this->clean_format($element['#value']);
    if (strlen($value) == 0) {
      $form_state->setValueForElement($element, '');
      return;
    }
    if (strlen($value) != 10 && strlen($value) != 13) {
      $form_state->setError($element, t('"%isbn" isn\'t a valid ISBN number. A valid ISBN number has 10 or 13 digits.', array('%isbn' => $value)));
    }
    if (strlen($value) == 10 && !$this->isValid10($value)) {
      $form_state->setError($element, t('"%isbn" isn\'t a valid 10 digit ISBN number.', array('%isbn' => $value)));
    }
    if (strlen($value) == 13 && !$this->isValid13($value)) {
      $form_state->setError($element, t('"%isbn" isn\'t a valid 13 digit ISBN number.', array('%isbn' => $value)));
    }
  }

  /**
   * Remove all non-valid characters.
   *
   * @param $isbn
   *   The ISBN number typed by the user.
   *
   * @return
   *   The ISBN number without invalid characters.
   */
  private function clean_format($isbn) {
    return preg_replace('/([^xX0-9]*)/', "", $isbn);
  }

  /**
   * Check if the ISBN number is a valid 10 digit.
   *
   * @param $isbn
   *   The ISBN number with only valid characters.
   *
   * @return
   *   True if it's a valid 10 digit ISBN number, false otherwise.
   */
  function isValid10($isbn) {
    if (strlen($isbn) < 10) {
      return FALSE;
    }
    $check = 0;

    for ($i = 0; $i < 9; $i++) {
      $check += (10 - $i) * $isbn[$i];
    }

    $tenth = $isbn[9]; // tenth digit (aka checksum or check digit)
    $check += (strtoupper($tenth) == 'X') ? 10 : $tenth;
    return $check % 11 == 0;
  }

  /**
   * Check if the ISBN number is a valid 13 digit.
   *
   * @param $isbn
   *   The ISBN number with only valid characters.
   *
   * @return
   *   True if it's a valid 13 digit ISBN number, false otherwise.
   */
  function isValid13($isbn) {
    if (strlen($isbn) < 13) {
      return FALSE;
    }
    $first3digits = substr($isbn,0,3);
    if ($first3digits !== "978" && $first3digits !== "979") {
      return FALSE;
    }

    $check = 0;

    for ($i = 0; $i < 13; $i+=2) {
      $check += $isbn[$i];
    }

    for ($i = 1; $i < 12; $i+=2) {
      $check += 3 * $isbn[$i];
    }

    return ($check % 10) == 0;
  }

}
