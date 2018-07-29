<?php
namespace Roster\Sharp\Tinker;

class Sharp
{
    /**
     * Save all user statements here
     *
     * @var array
     */
    public $statements = [];

    /**
     * @var String
     */
    protected $statement;

    /**
     * Current statement
     *
     * @var String
     */
    protected $set;

    /**
     * @var String
     */
    protected $expression;
    /**
     * Push statements
     *
     * @param $statement
     * @return static
     */

    public static function make($statement)
    {
        $static = new static;

        $static->set = $statement;

        return $static;
    }

    /**
     * Get statements
     *
     * @return array
     */
    public function get()
    {
        return $this->statements;
    }

    /**
     * If statement
     *
     * @param $expression
     * @return $this
     */
    public function if($expression)
    {
        $this->statements[$this->set]['if']['expression'] = $expression;

        $this->statement = 'if';

        return $this;
    }

    /**
     * Elseif statement
     *
     * @param $expression
     * @return $this
     */
    public function elseif($expression)
    {
        $this->statements[$this->set]['elseif']['expression'] = $expression;

        $this->statement = 'elseif';

        return $this;
    }

    /**
     * Set statement name
     *
     * @param $name
     * @return Sharp
     */
    public function name($name)
    {
        $this->statements[$this->set][$this->statement]['name'] = $name;

        return $this;
    }

    /**
     * Create statement
     *
     * @return array
     */
    public function create()
    {
        $compile = [];

        foreach ($this->statements as $key => $statement)
        {
            if (array_key_exists('if', $statement))
            {
                $sharpName = $statement['if']['name'];
                $statement['if']['statement'] = 'if';
                $compile[$sharpName] = $statement['if'];
                $compile['end'.$sharpName]['statement'] = 'endif';
            }

            if (array_key_exists('elseif', $statement))
            {
                $statement['elseif']['statement'] = 'elseif';
                $compile[$statement['elseif']['name']] = $statement['elseif'];
            }
        }

        return $compile;
    }

}
