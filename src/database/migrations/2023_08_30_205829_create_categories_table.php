    <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NestedSet;

class CreateCategoriesTable extends Migration
{
    protected $tableName;
    
    public function __construct()
    {
        $this->tableName = config('categorist.table', 'categories');
        $this->morphTableName = config('categorist.morph_table', 'categorized');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->defaultTableSchema();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropDefaultTable();
    }

    public function createTableSchema($tableName, $morphTableName)
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('description')->nullable();
            $table->string('type');
            $table->boolean('status')->default(true);
            NestedSet::columns($table);
            $table->timestamps();

            $table->unique(['slug', 'type']);
        });

        Schema::create($morphTableName, function (Blueprint $table) {
            $table->integer('category_id');
            $table->morphs('categorized');
        });
    }

    public function defaultTableSchema()
    {
        $this->createTableSchema($this->tableName, $this->morphTableName);
    }

    public function dropDefaultTable()
    {
        Schema::dropIfExists($this->tableName);
        Schema::dropIfExists($this->morphTableName);
    }
}
