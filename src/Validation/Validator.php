<?php

namespace App\Validation;

class Validator
{
    private $errors;

    public function __construct()
    {
        $this->errors = [];
    }

    public function isValid()
    {
        return empty($this->errors);
    }

    /**
     * Value should not be empty string
     * @param $value
     * @param $message
     */
    public function notEmpty($value, $message)
    {
        if (empty($value)) {
            $this->errors[] = $message;
        }
    }

    public function isEmail($value, $message)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = $message;
        }
    }

    public function minLength($value, $length, $message)
    {
        if (strlen($value) < $length) {
            $this->errors[] = $message;
        }
    }

    public function containsNumbers($value, $message)
    {
        if (!preg_match("#[0-9]+#", $value)) {
            $this->errors[] = $message;
        }
    }

    public function containsCapitalLetters($value, $message)
    {
        if (!preg_match("#[A-Z]+#", $value)) {
            $this->errors[] = $message;
        }
    }

    public function maxLength($value, $length, $message)
    {
        if (strlen($value) > $length) {
            $this->errors[] = $message;
        }
    }

    public function length($value, int $min, $max, $message)
    {
        $len = strlen($value);
        if (!($len >= $min && $len <= $max)) {
            $this->errors[] = $message;
        }
    }

    public function shouldMatch($value, $match, $message)
    {
        if ($value !== $match) {
            $this->errors[] = $message;
        }
    }

    public function min($value, $min, $message) {
        if ($value < $min) {
            $this->errors[] = $message;
        }
    }

    public function max($value, $max, $message) {
        if ($value > $max) {
            $this->errors[] = $message;
        }
    }

    public function customValidation($value, \Closure $validation)
    {

    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function addError($message) {
        $this->errors[] = $message;
    }

    public function isInDatabase($value, $table, $column, $message)
    {

    }
}
