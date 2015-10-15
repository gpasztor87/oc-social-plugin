<?php namespace Autumn\Social\Updates;

use Str;
use Schema;
use October\Rain\Database\Updates\Migration;
use RainLab\User\Models\User;

class UsersAddSlugColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function($table)
        {
            $table->string('slug')->nullable()->index();
        });

        /*
         * Set slug for existing users
         */
        $users = User::all();
        foreach ($users as $user) {
            $user->slug = Str::slug($user->username);
            $user->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->dropColumn('slug');
        });
    }


}