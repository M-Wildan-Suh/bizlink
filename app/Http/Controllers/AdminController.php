<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleShow;
use App\Models\GuardianWeb;
use App\Models\SourceCode;
use App\Models\Traffic;
use App\Models\WaTraffic;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    private function formatCount($number)
    {
        if ($number >= 1000) {
            return round($number / 1000, 1) . 'k'; // contoh: 1500 → 1.5k
        }
        return (string) $number;
    }

    public function dashboard(Request $request)
    {
        $data = GuardianWeb::select(['id', 'url', 'code'])
            ->withCount([
                'articles as spintaxcount' => function ($query) {
                    $query->where('article_type', 'spintax');
                },
                'articles as uniquecount' => function ($query) {
                    $query->where('article_type', 'unique');
                },
            ])
            ->get();

        $spinCountByGuardian = ArticleShow::query()
            ->join('articles', 'articles.id', '=', 'article_shows.article_id')
            ->where('articles.article_type', 'spintax')
            ->groupBy('articles.guardian_web_id')
            ->selectRaw('articles.guardian_web_id, COUNT(article_shows.id) as total')
            ->pluck('total', 'articles.guardian_web_id');

        $categoryRows = ArticleCategory::query()
            ->join('pivot_articles_categories as pac', 'pac.category_id', '=', 'article_categories.id')
            ->join('articles', 'articles.id', '=', 'pac.article_id')
            ->select([
                'articles.guardian_web_id',
                'article_categories.category',
                'article_categories.slug',
            ])
            ->distinct()
            ->get();

        $categoriesByGuardian = $categoryRows
            ->groupBy(function ($item) {
                return is_null($item->guardian_web_id) ? 'main' : (string) $item->guardian_web_id;
            })
            ->map(function ($rows) {
                return $rows->map(function ($row) {
                    return (object) [
                        'category' => $row->category,
                        'slug' => $row->slug,
                    ];
                })->values();
            });

        $data->transform(function ($item) use ($spinCountByGuardian, $categoriesByGuardian) {
            $item->spintaxcount = $this->formatCount((int) $item->spintaxcount);
            $item->spincount = $this->formatCount((int) ($spinCountByGuardian[$item->id] ?? 0));
            $item->uniquecount = $this->formatCount((int) $item->uniquecount);
            $item->categories = collect($categoriesByGuardian[(string) $item->id] ?? []);
            return $item;
        });

        // Manual website
        $manual = new \stdClass();
        $manual->id = null;
        $manual->url = 'Main';

        $manual->categories = collect($categoriesByGuardian['main'] ?? []);

        $manual->spintaxcount = $this->formatCount(Article::whereNull('guardian_web_id')->where('article_type', 'spintax')->count());
        $manual->spincount = $this->formatCount(
            ArticleShow::query()
                ->join('articles', 'articles.id', '=', 'article_shows.article_id')
                ->whereNull('articles.guardian_web_id')
                ->where('articles.article_type', 'spintax')
                // ->where('articles.article_type', 'spintax')
                ->count()
        );
        $manual->uniquecount = $this->formatCount(Article::whereNull('guardian_web_id')->where('article_type', 'unique')->count());

        $data->prepend($manual);

        // Traffic charts
        $mode = $request->query('mode', 'day'); // default day

        if ($mode === 'day') {
            $traffic = $this->trafficDay();
        } elseif ($mode === 'week') {
            $traffic = $this->trafficWeek();
        } elseif ($mode === 'month') {
            $traffic = $this->trafficMonth();
        } else {
            $traffic = [
                'labels' => [],
                'values' => [],
                'waValues' => [],
            ];
        }

        // Other dashboard data
        $guardian = $this->formatCount(GuardianWeb::count());
        $sc = $this->formatCount(SourceCode::count());
        $spintax = $this->formatCount(Article::where('article_type', 'spintax')->count());
        $spin = $this->formatCount(
            ArticleShow::query()
                ->join('articles', 'articles.id', '=', 'article_shows.article_id')
                ->where('articles.article_type', 'spintax')
                ->count()
        );
        $unique = $this->formatCount(Article::where('article_type', 'unique')->count());

        return view('dashboard', compact('data', 'sc', 'spintax', 'spin', 'unique', 'guardian', 'traffic', 'mode'));
    }


    private function trafficDay()
    {
        $start = now()->subHours(23)->startOfHour();
        $end   = now();
        return $this->buildTrafficSeries($start, $end, 'hour', 'H:00');
    }

    private function trafficWeek()
    {
        $start = now()->subDays(6)->startOfDay();
        $end   = now()->endOfDay();
        return $this->buildTrafficSeries($start, $end, 'day', 'D d');
    }

    private function trafficMonth()
    {
        $start = now()->subDays(29)->startOfDay();
        $end   = now()->endOfDay();
        return $this->buildTrafficSeries($start, $end, 'day', 'd M');
    }

    private function buildTrafficSeries(Carbon $start, Carbon $end, string $step, string $labelFormat): array
    {
        $labels = [];
        $values = [];
        $waValues = [];
        $articleIds = [];

        $rawRows = Traffic::whereBetween('created_at', [$start, $end])->get(['created_at', 'access', 'article_show_id']);
        $waRows  = WaTraffic::whereBetween('created_at', [$start, $end])->get(['created_at', 'access']);

        $bucketValues = [];
        foreach ($rawRows as $row) {
            $time = Carbon::parse($row->created_at);
            $bucketKey = $step === 'hour'
                ? $time->format('Y-m-d H:00:00')
                : $time->toDateString();

            $bucketValues[$bucketKey] = ($bucketValues[$bucketKey] ?? 0) + (int) $row->access;
            $articleIds[] = $row->article_show_id;
        }

        $waBucketValues = [];
        foreach ($waRows as $row) {
            $time = Carbon::parse($row->created_at);
            $bucketKey = $step === 'hour'
                ? $time->format('Y-m-d H:00:00')
                : $time->toDateString();

            $waBucketValues[$bucketKey] = ($waBucketValues[$bucketKey] ?? 0) + (int) $row->access;
        }

        for ($cursor = $start->copy(); $cursor <= $end; $cursor = $step === 'hour' ? $cursor->addHour() : $cursor->addDay()) {
            $labels[] = $cursor->format($labelFormat);
            $key = $step === 'hour'
                ? $cursor->format('Y-m-d H:00:00')
                : $cursor->toDateString();
            $values[] = (int) ($bucketValues[$key] ?? 0);
            $waValues[] = (int) ($waBucketValues[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'waValues' => $waValues,
            'articleIds' => $articleIds,
        ];
    }
}