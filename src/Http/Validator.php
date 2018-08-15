<?php

namespace Roster\Http;

use Roster\Database\DB;
use Roster\Support\Str;
use Roster\Support\Input;
use App\Validation\Rules;
use Roster\Support\Alert;

class Validator
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var array
     */
    protected $customAttributes = [];

    /**
     * @var array
     */
    protected $customMessages = [];

    /**
     * Validator constructor.
     * @param array $data
     * @param array $validation
     * @param array $messages
     * @param array $customAttributes
     */
    public function __construct(array $data, array $validation, array $messages = [], array $customAttributes = [])
    {
        $this->data = $data;
        $this->customMessages = $messages;
        $this->customAttributes = $customAttributes;

        $this->arrange($validation);
    }

    /**
     * Start with validation
     *
     * @param array $data
     * @param array $validation
     * @param array $messages
     * @param array $customAttributes
     * @return Validator
     */
    public static function make(array $data, array $validation, array $messages = [], array $customAttributes = [])
    {
        return new static($data, $validation, $messages, $customAttributes);
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

        foreach($column as $key => $value)
        {
            $rules = $this->getRules($value);
            $value = $this->getValue($key);

            if (!in_array('nullable', $rules) || $this->nullable($value))
            {
                $nullable = array_search('nullable', $rules);

                if (is_int($nullable))
                {
                    unset($rules[$nullable]);
                }

                $results[$key]['rules'] = $rules;
                $results[$key]['value'] = $value;
            }
        }

        $this->collect($results);

        return $this;
    }

    /**
     * @param $rules
     * @return array
     */
    protected function getRules($rules)
    {
        if (is_array($rules))
        {
            return array_values($rules);
        }

        return explode('|', $rules);
    }

    /**
     * @param $value
     * @return bool
     */
    protected function nullable($value)
    {
        if (is_array($value))
        {
           if (isset($value['error']) && $value['error'])
           {
               return false;
           }

           if (empty($value))
           {
               return false;
           }
        }

        return $value;
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

                if (is_string($rule) && strstr($rule, ':'))
                {
                    $options = explode(':', $rule);

                    $rulePicker['type'] =  array_shift($options);
                    $rulePicker['needed'] = $options;
                }

                $this->callRules($rulePicker);
            }
        }

        return $this->setMessages()
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
        if (is_object($rulePicker['type']))
        {
            $rule = $rulePicker['type'];

            if (!$rule->passes($rulePicker['column'], $rulePicker['value']))
            {
                $this->addMessage($rulePicker['column'], $rule->message());
            }
        }
        elseif (method_exists($this, $rulePicker['type']))
        {
            $this->{$rulePicker['type']}((object) $rulePicker);
        }
    }

    /**
     * Set errors
     *
     * @return mixed
     */
    public function setMessages()
    {
        if ($this->fails())
        {
            Alert::error($this->messages);
        }

        return $this;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasValue($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getValue($key)
    {
        if ($this->hasValue($key))
        {
            return $this->data[$key];
        }

        return null;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Has error
     * @param $key
     * @return bool
     */
    public function hasError($key)
    {
        return isset($this->messages[$key]);
    }

    /**
     * Check if validation is valid
     * If errors empty clear inputs
     *
     * @return bool
     */
    public function fails()
    {
        return !empty($this->messages);
    }

    /**
     * Clear errors
     *
     * @return array
     */
    public function clearErrors()
    {
        return $this->messages = [];
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

        foreach ($this->data as $key => $value)
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
     * Set attributes for errors
     * 
     * @param $rule
     * @return array
     */
    protected function setAttributes($rule)
    {
        $options = [
            'field' => $this->getAttribute($rule->column),
            'needed' => !empty($rule->needed) ? is_array($rule->needed) ? implode(', ', $rule->needed) : $rule->needed : ''
        ];

        return $options;
    }

    /**
     * @param $attribute
     * @return bool|mixed
     */
    protected function getAttribute($attribute)
    {
        $attribute = array_key_exists($attribute, $this->customAttributes) ? $this->customAttributes[$attribute] : ucfirst($attribute);

        return Str::replace(['_' => ' '], $attribute);
    }

    /**
     * Set errors
     *
     * @param $rule
     * @return mixed|string
     * @throws \Exception
     */
    protected function message($rule)
    {
        $options = $this->setAttributes($rule);

        $message =  __('roster.form_validation.'. $rule->type, $options);

        return $this->addMessage($rule->column, $message);
    }

    /**
     * Add error
     *
     * @param $field
     * @param $messages
     * @param array $options
     * @return mixed
     */
    public function addMessage($field, $messages, array $options = [])
    {
        foreach ((array) $messages as $message)
        {
            if (!empty($options))
            {
                $message = Str::replace($options, $message);
            }

            $this->messages[$field][] = $message;
        }
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
        if (strlen($rule->value) > current($rule->needed))
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
            return $this->validateDate($rule->value, current($rule->needed))
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
    public function confirmed($rule)
    {
        if ($rule->value != $this->getValue($rule->column.'_confirmation'))
        {
            return $this->message($rule);
        }
    }

    /**
     * @param $rule
     * @return mixed|string
     */
    public function unique($rule)
    {
        if ($rule)
        {
            $db = explode(',', current($rule->needed));

            $table = current($db);
            $field = $rule->column;

            if (count($db) > 1)
            {
                $table = current($db);
                $field = end($db);
            }

            if (DB::table($table)->where($field, $rule->value)->count())
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

        if (in_array($extension, $rule->needed))
        {
            return true;
        }

        return false;
    }
}