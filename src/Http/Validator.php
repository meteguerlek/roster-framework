<?php

namespace Roster\Http;

use Roster\Support\Str;
use Roster\Support\Input;
use App\Validation\Rules;
use Roster\Support\Alert;

class Validator
{
    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    protected $alreadySet = [];

    /**
     * @var string
     */
    protected $errorName = null;

    /**
     * @var Request
     */
    protected $request = [];

    /**
     * @var string
     */
    protected $password = null;

    /**
     * Start with validation
     *
     * @param Request $request
     * @param array $validation
     * @return Validator
     */
    public static function make($request, array $validation)
    {
        $static = new static;

        // Set request results
        $static->request = $request;

        // Filter and arrange validation
        $static->arrange($validation);

        return $static;
    }

    /**
     * Arrange the rules and the values
     *
     * @param array $column
     * @return Validator|void
     */
    protected function arrange(array $column)
    {
        $results = [];

        $rules = [];

        foreach($column as $key => $value)
        {
            $results[$key]['value'] = $this->request->{$key};
            $rules[$key]['rules'] = explode('|', $value);
        }

        $results = array_merge_recursive($results, $rules);

        $this->collect($results);

        return $this;
    }

    /**
     * Collect all the information for the validator
     *
     * @param $results
     * @return mixed
     */
    protected function collect($results)
    {
        foreach ($results as $column => $validations)
        {
            foreach ($validations['rules'] as $rule)
            {
                $rulePicker = [
                    'type' => $rule,
                    'value' => $validations['value'],
                    'column' => $column
                ];

                if (strstr($rule, ':'))
                {
                    $options = explode(':', $rule);

                    $rulePicker['needed'] = isset($options[1]) ? $options[1] : $options;
                    $rulePicker['type'] =  isset($options[1]) ? $options[0] : $options;
                }

                $this->callRules($rulePicker);
            }
        }

        return $this->setErrors()
            ->setInputs()
            ->clearInputs();
    }

    /**
     * Call custom or user rules
     *
     * @param $rulePicker
     * @return mixed
     */
    protected function callRules($rulePicker)
    {
        if (method_exists(Rules::class, $rulePicker['type']))
        {
            return (new Rules())->{$rulePicker['type']}((object) $rulePicker, $this);
        }
        elseif (method_exists($this, $rulePicker['type']))
        {
            return $this->{$rulePicker['type']}((object) $rulePicker);
        }
    }

    /**
     * Set errors
     *
     * @return mixed
     */
    public function setErrors()
    {
        Alert::error($this->errors);

        return $this;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Has error
     * @param $key
     * @return bool
     */
    public function hasError($key)
    {
        return isset($this->errors[$key]);
    }

    /**
     * Get error counts
     *
     * @param $key
     * @return int
     */
    public function howManyError($key)
    {
        return isset($this->alreadySet[$key]) ? $this->alreadySet : 0;
    }

    /**
     * Check if validation is valid
     * If errors empty clear inputs
     *
     * @return bool
     */
    public function fails()
    {
        return !empty($this->errors);
    }

    /**
     * Clear errors
     *
     * @return array
     */
    public function clearErrors()
    {
        return $this->errors = [];
    }

    /**
     * Set inputs if errors is not empty
     *
     * @param array ...$with
     * @return $this
     */
    public function setInputs(...$with)
    {
        $inputs = [];

        foreach ($this->request->all() as $key => $value)
        {
            //TODO: check
            if (in_array($key, $with))
            {
                $inputs[$key] = $value;
            }
            elseif (empty($with))
            {
                $inputs[$key] = $value;
            }
        }

        Input::put($inputs);

        return $this;
    }

    /**
     * Clear inputs after success
     *
     * @param array ...$clears
     * @return $this
     */
    public function clearInputs(...$clears)
    {
        if (!$this->fails())
        {
            $inputs = Input::all();

            // Clear all inputs if clears is empty
            if (!empty($clears))
            {
                foreach ($clears as $clear)
                {
                    // Clear specific inputs
                    if(array_key_exists($clear, $inputs))
                    {
                        unset($inputs[$clear]);
                    }
                }

                Input::put($inputs);
            }
            else
            {
                Input::clear();
            }
        }

        return $this;
    }

    /**
     * @return Request
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Set attributes for errors
     * 
     * @param $rule
     * @return array
     */
    protected function setAttributes($rule)
    {
        $options = [
            ':field' => $rule->column,
            ':needed' => (!empty($rule->needed)) ? (is_array($rule->needed)) ? implode(', ', $rule->needed) : $rule->needed : '',
            '_id' => '',
            '_' => ' '
        ];

        return $options;
    }

    /**
     * Set errors
     *
     * @param $rule
     * @return mixed|string
     */
    protected function message($rule)
    {
        $options = $this->setAttributes($rule);

        $message =  __('roster.form_validation.'. $rule->type, $options);

        return $this->error($rule->column, $message);
    }

    /**
     * Add error
     *
     * @param $field
     * @param $message
     * @param array $options
     * @return mixed
     */
    public function error($field, $message, array $options = [])
    {
        if (!empty($options))
        {
            $message = Str::replace($options, $message);
        }

        if (isset($this->errors[$field]))
        {
            $alreadySet = $this->alreadySet[$field]++;

            return $this->errors[$field.'_'.$alreadySet] = $message;
        }

        $this->alreadySet[$field] = 1;

        return $this->errors[$field] = $message;
    }

    /**
     * Date validator
     *
     * @param $date
     * @return bool
     */
    protected function dateChecker($date)
    {
        $date = trim($date);

        if (preg_match('/\./', $date))
        {
            $dateIndex = explode('.', $date);

            $check = checkdate($dateIndex[0], $dateIndex[1], $dateIndex[2]);

            return $check;

        }
        elseif (preg_match('/\-/', $date))
        {
            $dateIndex = explode('-', $date);

            $check = checkdate($dateIndex[1], $dateIndex[2], $dateIndex[0]);

            return $check;
        }

        return false;
    }

    /**
     * @param $rule
     * @return mixed|string
     */
    public function required($rule)
    {
        if (is_array($rule->value))
        {
            return !empty($rule->value) ?: $this->message($rule);
        }
        elseif (is_string($rule->value))
        {
            return trim($rule->value) !== '' ?: $this->message($rule);
        }
        elseif (empty($rule->value))
        {
            return $this->message($rule);
        }
    }

    /**
     * @param $rule
     * @return mixed|string
     */
    public function num($rule)
    {
        if (!is_numeric($rule->value))
        {
            return $this->message($rule);
        }
    }

    /**
     * @param $rule
     * @return mixed|string
     */
    public function email($rule)
    {
        if (!filter_var($rule->value, FILTER_VALIDATE_EMAIL))
        {
            return $this->message($rule);
        }
    }

    /**
     * @param $rule
     * @return mixed|string
     */
    public function max($rule)
    {
        if (strlen($rule->value) > $rule->needed)
        {
            return $this->message($rule);
        }
    }

    /**
     * @param $rule
     * @return mixed|string
     */
    public function min($rule)
    {
        if (strlen($rule->value) < $rule->needed)
        {
            return $this->message($rule);
        }
    }

    /**
     * @param $rule
     * @return mixed|string
     */
    public function string($rule)
    {
        if (!filter_var($rule->value, FILTER_SANITIZE_STRING))
        {
            return $this->message($rule);
        }
    }

    /**
     * @param $rule
     * @return mixed|string
     */
    public function date($rule)
    {
        if (isset($rule->needed))
        {
            return $this->validateDate($rule->value, $rule->needed)
                ? true
                : $this->message($rule);
        }

        foreach (explode(',', $rule->value) as $value)
        {
            $check = $this->dateChecker($value);

            if (!$check)
            {
                return $this->message($rule);
            }
        }

    }

    /**
     * @param $date
     * @param string $format
     * @return bool
     */
    public function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        if (!is_string($date) || !is_string($format) && !$date || !$format)
        {
            return false;
        }

        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }


    /**
     * Validate Time
     *
     * @param $rule
     * @return mixed|string
     */
    public function time($rule)
    {
        if (!strtotime($rule->value))
        {
            return $this->message($rule);
        }
    }

    /**
     * @param $rule
     * @return mixed|string
     */
    public function password($rule)
    {
        if (empty($this->password))
        {
            $this->password = $rule->value;
        }
        else
        {
            if ($this->password !== $rule->value)
            {
                return $this->message($rule);
            }
        }
    }

    /**
     * Validate File
     *
     * @param $rule
     * @return mixed|string
     */
    public function file($rule)
    {
        if (array_key_exists($rule->column, $_FILES))
        {
            if ($_FILES[$rule->column]['error'])
            {
                return $this->message($rule);
            }

            if (isset($rule->needed))
            {
                if (!$this->checkExtension($rule))
                {
                    $rule->type = 'file_extension';

                    return $this->message($rule);
                }
            }

        }
    }

    /**
     * Check extension
     *
     * @param $rule
     * @return bool
     */
    protected function checkExtension($rule)
    {
        $extension = pathinfo($rule->value['name'], PATHINFO_EXTENSION);

        if (is_array($rule->needed))
        {
            return in_array($extension, $rule->needed)
                ? true
                : false;
        }
        elseif ($rule->needed == $extension)
        {
            return true;
        }

        return false;
    }
}