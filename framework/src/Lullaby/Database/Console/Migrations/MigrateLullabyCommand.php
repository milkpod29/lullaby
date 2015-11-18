<?php

namespace Lullaby\Database\Console\Migrations;

use Illuminate\Support\Str;
use Illuminate\Foundation\Composer;
use Illuminate\Database\Console\Migrations\BaseCommand;

use Lullaby\Database\Migrations\MigrationCreator;
use Lullaby\Database\Migrations\MigrationDefinition;

class MigrateLullabyCommand extends BaseCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'lullaby:migration
        {--create= : The table to be created.}
        {--table= : The table to migrate.}
        {--path= : The location where the migration file should be created.}
        {--definition= : The table definition file.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration file';

    /**
     * The migration creator instance.
     *
     * @var \Lullaby\Database\Migrations\MigrationCreator
     */
    protected $creator;

    /**
     * The Composer instance.
     *
     * @var \Illuminate\Foundation\Composer
     */
    protected $composer;

    /**
     * Create a new migration install command instance.
     *
     * @param \Lullaby\Database\Migrations\MigrationCreator $creator
     * @param \Illuminate\Foundation\Composer  $composer
     */
    public function __construct(MigrationCreator $creator, Composer $composer)
    {
        parent::__construct();

        $this->creator = $creator;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        // It's possible for the developer to specify the tables to modify in this
        // schema operation. The developer may also specify if this table needs
        // to be freshly created so we can create the appropriate migrations.
//        $name = $this->input->getArgument('name');

        $definition = $this->input->getOption('definition');

        $table = $this->input->getOption('table');

        $create = $this->input->getOption('create');

        if (!$table && is_string($create)) {
            $table = $create;
        }

        // Now we are ready to write the migration out to disk. Once we've written
        // the migration out, we will dump-autoload for the entire framework to
        // make sure that the migrations are registered by the class loaders.
        $this->writeMigrationAll($table, $create, $definition);

        $this->composer->dumpAutoloads();
    }

    /**
     * Write the migration file to disk.
     *
     * @param  string  $table
     * @param  string  $create
     * @param  string  $definition
     *
     * @return string
     */
    protected function writeMigrationAll($table, $create, $definition)
    {
        $excel = new MigrationDefinition($definition);
        // get table count.
        $count = $excel->count();

        for ($index = 0; $index < $count; $index++) {

            // get table name.
            $name = $excel->getCellValue($index, MigrationDefinition::MODEL_NAME);
            // change table name in plural form.
            $name = Str::plural($name);
            // set up content.
            $this->creator->content['up'] = $excel->getContent($index);

            $this->writeMigration($name, $table, $create);
        }
    }


    /**
     * Write the migration file to disk.
     *
     * @param  string  $name
     * @param  string  $table
     * @param  bool    $create
     *
     * @return string
     */
    protected function writeMigration($name, $table, $create)
    {
        $path = $this->getMigrationPath();

        $file = pathinfo($this->creator->create($name, $path, $table, $create), PATHINFO_FILENAME);

        $this->line("<info>Created Migration:</info> $file");
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        if (!is_null($targetPath = $this->input->getOption('path'))) {
            return $this->laravel->basePath().'/'.$targetPath;
        }

        return parent::getMigrationPath();
    }
}
