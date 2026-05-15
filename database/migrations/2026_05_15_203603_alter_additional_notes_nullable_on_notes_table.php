use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {

            $table->text('additional_notes')
                ->nullable()
                ->change();

        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {

            $table->text('additional_notes')
                ->nullable(false)
                ->change();

        });
    }
};