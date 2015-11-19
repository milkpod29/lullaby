<?php

namespace Lullaby\Database\Migrations;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class MigrationCreator
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The registered post create hooks.
     *
     * @var array
     */
    protected $postCreate = [];

    /**
     * The content.
     *
     * @var array
     */
    public $content = [];

    /**
     * Create a new migration creator instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Create a new migration at the given path.
     *
     * @param  string  $content
     * @param  string  $name
     * @param  string  $path
     * @param  string  $table
     * @param  bool    $create
     * @param  object  $definition
     * @return string
     */
    public function create($content, $name, $path, $table = null, $create = false, $definition = null)
    {
        $path = $this->getPath($name, $path);

        // First we will get the stub file for the migration, which serves as a type
        // of template for the migration. Once we have those we will populate the
        // various place-holders, save the file, and run the post create event.
        $stub = $this->getStub($content, $table, $create, $definition);

        $this->files->put($path, $this->populateStub($name, $stub, $table));

        $this->firePostCreateHooks();

        return $path;
    }

    /**
     * Get the migration stub file.
     *
     * @param  string  $content
     * @param  string  $table
     * @param  bool    $create
     * @param  object  $definition
     * @return string
     */
    protected function getStub($content, $table, $create, $definition)
    {
        switch($content) {
            case 'field':
                $file = $this->files->get($this->getStubPath().'/create.stub');
                break;
            case 'index':
            case 'foreignkey':
                $file = $this->files->get($this->getStubPath().'/update.stub');
                break;
            default:
                $file = $this->files->get($this->getStubPath().'/blank.stub');
        }
        return $file;
    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  string  $name
     * @param  string  $stub
     * @param  string  $table
     * @return string
     */
    protected function populateStub($name, $stub, $table)
    {
        $stub = str_replace('DummyClass', $this->getClassName($name), $stub);

        $stub = str_replace('DummyTable', $name, $stub);

        $stub = str_replace('DummyUp', $this->content['up'], $stub);

        // Here we will replace the table place-holders with the table specified by
        // the developer, which is useful for quickly creating a tables creation
        // or update migration from the console instead of typing it manually.
//        if (!is_null($table)) {
//            $stub = str_replace('DummyTable', $table, $stub);
//        }

        return $stub;
    }

    /**
     * Get the class name of a migration name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getClassName($name)
    {
        return Str::studly("create_{$name}_table");
    }

    /**
     * Fire the registered post create hooks.
     *
     * @return void
     */
    protected function firePostCreateHooks()
    {
        foreach ($this->postCreate as $callback) {
            call_user_func($callback);
        }
    }

    /**
     * Register a post migration create hook.
     *
     * @param  \Closure  $callback
     *
     * @return void
     */
    public function afterCreate(Closure $callback)
    {
        $this->postCreate[] = $callback;
    }

    /**
     * Get the full path name to the migration.
     *
     * @param  string  $name
     * @param  string  $path
     *
     * @return string
     */
    protected function getPath($name, $path)
    {
//        return $path.'/'.$this->getDatePrefix().'_'.$name.'.php';
        return $path.'/'.$this->getDatePrefix().'_create_'.$name.'_table.php';
    }

    /**
     * Get the date prefix for the migration.
     *
     * @return string
     */
    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }

    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function getStubPath()
    {
        return __DIR__.'/stubs';
    }

    /**
     * Get the filesystem instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }
}
