<?php

namespace Drupal\isbn\Plugin\Validation\Constraint;

use Nicebooks\Isbn\IsbnTools as Isbn;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsbnConstraintValidator extends ConstraintValidator {

  public function validate($value, Constraint $constraint) {

    if (gettype($value) == 'string') {
      $isbn = new Isbn();
      if (!$isbn->isValidIsbn($value)) {
        $this->context->addViolation(t('"%isbn" isn\'t a valid ISBN number.', ['%isbn' => $value]));
      }
    }
  }

}
