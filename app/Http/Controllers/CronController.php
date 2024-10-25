<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\{DB,
    Artisan,
    File,
    Log
};

class CronController extends Controller
{

    public function resetData()
    {
        ini_set('max_execution_time', 300);
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        $this->copyFiles();
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        DB::beginTransaction();
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $key => $value)
        {
            DB::statement("DROP TABLE IF EXISTS $value");
        }
        DB::commit();
        Artisan::call('migrate');
        DB::unprepared(file_get_contents('db/db.sql'));
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function copyFiles()
    {
        if (
            // File::isWritable(public_path('dist')) &&
            File::isWritable(public_path('uploads')) &&
            File::isWritable(public_path('user_dashboard')) &&
            File::isWritable(public_path('img')) &&
            File::isWritable(public_path('images')) &&
            File::isWritable(public_path('frontend')))
        {
            // File::cleanDirectory(public_path('dist'));
            // File::copyDirectory(public_path('_importForCron/dist'), public_path('dist'));

            File::cleanDirectory(public_path('uploads'));
            File::copyDirectory(public_path('_importForCron/uploads'), public_path('uploads'));

            File::cleanDirectory(public_path('user_dashboard'));
            File::copyDirectory(public_path('_importForCron/user_dashboard'), public_path('user_dashboard'));

            File::cleanDirectory(public_path('img'));
            File::copyDirectory(public_path('_importForCron/img'), public_path('img'));

            File::cleanDirectory(public_path('images'));
            File::copyDirectory(public_path('_importForCron/images'), public_path('images'));

            File::cleanDirectory(public_path('frontend'));
            File::copyDirectory(public_path('_importForCron/frontend'), public_path('frontend'));

        }
        else
        {

            Log::info("Don't have write permission !");

        }
    }

}
