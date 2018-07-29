<?php

namespace Roster\Sharp;

use Roster\Filesystem\File;
use Roster\Sharp\Concerns\Strings;
use Roster\Sharp\Concerns\Layouts;
use Roster\Sharp\Concerns\Comments;
use Roster\Sharp\Concerns\Statements;
use Roster\Sharp\Concerns\CustomCompiler;
use App\Sharp\Statements as Userstatements;
use Roster\Sharp\Concerns\CreateStatements;


class SharpCompiler
{
    use Strings,
        Layouts,
        Comments,
        Statements,
        CustomCompiler,
        CreateStatements;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var string
     */
    protected $fileHash;

    /**
     * @var array
     */
    protected $compilers = [
        'statements', 'echo', 'htmlentities',
        'htmlspecialchars', 'functions',
        'comments'
    ];

    /**
     * @var array
     */
    protected $statements = [
        'if', 'elseif', 'endif', 'while',
        'endwhile', 'for', 'endfor', 'foreach',
        'endforeach', 'break', 'continue',
        'switch', 'return'
    ];

    /**
     * @var array
     */
    protected $includes = [
        'include', 'include_once',
        'require', 'require_once'
    ];

    /**
     * @var array
     */
    protected $layouts = [
        'extends', 'yield',
        'section', 'endsection'
    ];

    /**
     * @var array
     */
    protected $custom = [
        'php', 'endphp', 'else', 'endgoto',
        'do', 'enddowhile', 'auth', 'endauth',
        'guest', 'endguest', 'class', 'csrf',
        'route', 'url', 'old'
    ];

    /**
     * @var array
     */
    protected $userStatements = [];

    /**
     * SharpCompiler constructor.
     */
    public function __construct()
    {
        // Set user statements
        $this->setUserStatements();
    }

    /**
     * Compile file
     *
     * @param $file
     * @param $fileName
     * @return String
     */
    public function compile($file, $fileName)
    {
        // Set filename and file hashed name
        $this->setFile($file, $fileName);

        // Check if the file content the same
        $cachedFile = $this->checkLastModified();

        if ($cachedFile)
        {
            // If true, return only the path name
            return $cachedFile;
        }

        // else make file
        $this->make();

        // and return compiled file
        return $this->file;
    }

    /**
     * Set file information
     *
     * @param $file
     * @param $fileName
     * @return $this
     */
    public function setFile($file, $fileName)
    {
        // file or path
        $this->file = $file;

        // template
        $this->fileName = $fileName;

        // template is hashed
        $this->fileHash =  md5($fileName);

        return $this;
    }

    /**
     * Compile content
     *
     * @return String
     */
    public function make()
    {
        $content = File::where($this->file)->getContent();

        // search sections
        preg_match_all('/(@section)/s', $content, $this->matchedSections);

        $result = '';

        foreach (token_get_all($content) as $token)
        {
            $result .= is_array($token) ? $this->parse($token) : $token;
        }

        return $this->saveCompiledContent($result);
    }

    /**
     * Get Layout and compile it
     *
     * @param $layout
     * @return mixed
     */
    public function makeLayout($layout)
    {
        $file = File::where(config('disk.view'), $layout, 'sharp.php')->getPath();

        $compiled = $this->compile($file, $layout);

        return include $compiled;
    }

    /**
     * Parse content
     *
     * @param $token
     * @return mixed
     */
    public function parse($token)
    {
        list($id, $content) = $token;

        if ($id == T_INLINE_HTML) {

            foreach ($this->compilers as $type) {

                $content = $this->{$type}($content);
            }
        }

        return $content;
    }

    public function withoutQuote($path)
    {
        return preg_replace("/['\"]/", '', $path);
    }

    /**
     * Save compiled content
     *
     * @param $result
     * @return String
     */
    public function saveCompiledContent($result)
    {
        $directory = config('disk.storage.view');

        $file = File::create($result, $directory, $this->fileHash);

        $this->file = $file;

        return $this->file;
    }

    /**
     * Check if sharp is updated
     *
     * @return String|bool|null
     */
    public function checkLastModified()
    {
        // Get compiled path
        $compiled = File::where(config('disk.storage.view'), $this->fileHash)->getPath();

        if (File::where($this->file)->exist())
        {
            // Check if file already compiled or the orginal file is also exist
            if (File::where($compiled)->exist())
            {
                // Check if orginal file changed
                if (File::where($this->file)->lastModified() > File::where($compiled)->lastModified())
                {
                    // False means file is have been changed
                    return false;
                }

                // Return compiled path
                return $compiled;
            }

            return false;
        }

        // this null means the sharp file is not exist
        return null;
    }

    /**
     * Get all defined statements
     *
     * @return array
     */
    public function getAllStatements()
    {
        return array_merge(
            $this->compilers,
            $this->statements,
            $this->layouts,
            $this->includes,
            $this->custom
        );
    }

    /**
     * Set user statements
     *
     * @return array
     */
    public function setUserStatements()
    {
        if (!class_exists(Userstatements::class))
        {
            return false;
        }

        $userStatements = $this->getUserstatments();

        if($nameExist = $this->statementExist($userStatements))
        {
            throw new \Exception("Statement {$nameExist} allready exist!");

            return false;
        }

        return $this->userStatements = $userStatements;
    }

    /**
     * Get all defined methods from UserStatements
     *
     * @return array
     */
    protected function getUserstatments()
    {
        // Set user statements
        $userStatements = new Userstatements();

        $methods = get_class_methods($userStatements);

        $merge = [];

        foreach ($methods as $method)
        {
            $merge += $userStatements->{$method}();
        }

        return $merge;
    }

    /**
     * Check if user trying to set available statement name
     *
     * @param $statements
     * @return bool|mixed
     * @internal param $statement
     */
    protected function statementExist($statements)
    {
        $names = [];

        foreach ($statements as $statement)
        {
            $names[] = searchByKey('name', $statement);
        }

        foreach ($names as $name)
        {
            if(in_array($name, $this->getAllStatements()))
            {
                return $name;
            }
        }

        return false;
    }

    /**
     * Compile Statements
     *
     * @param $match
     * @return String
     */
    public function compileStatement($match)
    {
        $compiled = '';

        if (isset($match[1], $match[3]))
        {
            if (in_array($match[1], $this->includes) && isset($match[4]))
            {
                $compiled .= $this->render($match[1], $match[4]);
            }
            else
            {
                $compiled .= $this->head($match[1], $match[3]);
            }

        }
        elseif (isset($match[0], $match[1]))
        {
            $compiled .= $this->footer($match[1]);
        }

        return $compiled;
    }

    /**
     * Compile Layouts
     *
     * @param $match
     * @return String
     */
    public function compileLayout($match)
    {
        $compiled = '';

        if (isset($match[1], $match[3]))
        {
            $compiled .= $this->{$match[1]}($match[3]);
        }
        elseif (isset($match[0], $match[1]))
        {
            $compiled .= $this->{$match[1]}($match[1]);
        }

        return $compiled;
    }

    /**
     * Compile custom statement
     *
     * @param $match
     * @return mixed
     */
    public function compileCustom($match)
    {
        return $this->{"customCompiler".ucfirst($match[1])}($match);
    }

    /**
     * Replace statements or layouts with regext
     *
     * @param $content
     * @return String
     *
     */
    public function statements($content)
    {
        return preg_replace_callback(
                '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', function ($match) {
                // Statements
                if (in_array($match[1], array_merge($this->statements, $this->includes)))
                {
                    return $this->compileStatement($match);
                }
                // Layouts
                elseif (in_array($match[1], $this->layouts))
                {
                    return $this->compileLayout($match);
                }
                // Custom
                elseif (in_array($match[1], $this->custom))
                {
                    return $this->compileCustom($match);
                }
                // User
                elseif (in_array($match[1], array_keys($this->userStatements)))
                {
                    return $this->createStatement($match, $this->userStatements[$match[1]]);
                }
            }, $content
        );
    }

    /**
     * Compile Echo, Htmlentites, Htmlspecialchars, Functions and Comments
     *
     * @param $match
     * @param $method
     * @return String
     */

    public function compileStrings($match, $method)
    {
        $compiled = '';

        if (isset($match[0], $match[2]))
        {
            $compiled = $this->{$method}($match[2]);
        }

        return $compiled;
    }

    /**
     * Echo statement
     *
     * @param $content
     * @return String
     *
     */
    public function echo($content)
    {
        return preg_replace_callback(
            '/(@)?{!!\s*(.+?)\s*!!}(\r?\n)?/s', function ($match) {
                return $this->compileStrings($match, 'e');
            }, $content
        );
    }

    /**
     * Htmlspecialchars
     *
     * @param $content
     * @return String
     *
     */
    public function htmlspecialchars($content)
    {
        return preg_replace_callback(
                '/(@)?{{\s*(.+?)\s*}}(\r?\n)?/s', function ($match) {
                return $this->compileStrings($match, 'hsc');
            }, $content
        );
    }

    /**
     * Htmlentites
     *
     * @param $content
     * @return String
     *
     */
    public function htmlentities($content)
    {
        return preg_replace_callback(
            '/(@)?{#\s*(.+?)\s*#}(\r?\n)?/s', function ($match) {
                return $this->compileStrings($match, 'he');
            }, $content
        );
    }

    /**
     * Functions
     *
     * @param $content
     * @return String
     *
     */
    public function functions($content)
    {
        return preg_replace_callback(
                '/(@)?{%\s*(.+?)\s*%}(\r?\n)?/s', function ($match) {
                return $this->compileStrings($match, 'func');
            }, $content
        );
    }

    /**
     * HTML Comments
     *
     * @param $content
     * @return String
     *
     */
    public function comments($content)
    {
        return preg_replace_callback(
            '/(@)?{--\s*(.+?)\s*--}(\r?\n)?/s', function ($match) {
                return $this->compileStrings($match, 'com');
            }, $content
        );
    }

    /**
     * Convert file to string
     *
     * @return mixed
     */
    public function __toString()
    {
        return $this->file;
    }
}