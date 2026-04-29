<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Traffic;
use App\Models\WaTraffic;
use App\Models\ArticleShow;
use App\Models\GuardianWeb;
use Carbon\Carbon;

class TrafficSeeder extends Seeder
{
    public function run(): void
    {
        $articles = ArticleShow::with('articles')->get();
        if ($articles->isEmpty()) {
            $this->command->info('Tidak ada ArticleShow ditemukan. Silakan jalankan seeder artikel terlebih dahulu.');
            return;
        }

        $this->command->info('Membuat data dummy traffic...');

        // 30 hari terakhir
        for ($day = 0; $day < 30; $day++) {
            $date = Carbon::now()->subDays($day);
            
            foreach ($articles as $article) {
                // Randomize if this article gets traffic today
                if (rand(0, 1)) {
                    $access = rand(10, 500);
                    $waAccess = rand(1, 50);
                    
                    $guardianId = $article->articles->guardian_web_id;

                    // Buat Traffic
                    Traffic::create([
                        'article_show_id' => $article->id,
                        'article_id' => $article->article_id,
                        'guardian_web_id' => $guardianId,
                        'access' => $access,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);

                    // Buat WaTraffic
                    WaTraffic::create([
                        'article_show_id' => $article->id,
                        'guardian_web_id' => $guardianId,
                        'access' => $waAccess,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                }
            }
        }

        $this->command->info('Data dummy traffic berhasil dibuat.');
    }
}
