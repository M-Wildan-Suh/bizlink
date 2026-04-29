<?php

namespace App\Http\Controllers;

use App\Models\ArticleCategory;
use App\Models\ArticleShow;
use App\Models\ArticleTag;
use App\Models\PhoneNumber;
use App\Models\Traffic;
use App\Models\User;
use App\Models\WaTraffic;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class PageController extends Controller
{
    protected function getDefaultPhoneNumber(): ?string
    {
        return PhoneNumber::query()->value('no_tlp');
    }

    public function home(Request $request)
    {
        Paginator::currentPageResolver(function () use ($request) {
            return $request->route('page', 1); // default ke halaman 1
        });

        if ((int) $request->route('page', 1) === 1 && $request->routeIs('pagearticle')) {
            return redirect()->route('home', $request->query(), 301);
        }

        $data = ArticleShow::where('status', 'publish')
            ->whereHas('articles', function ($query) {
                $query->whereNull('guardian_web_id');
            })
            ->latest()->paginate(12);

        $category = ArticleCategory::whereHas('articles', function ($query) {
            $query->whereNull('guardian_web_id');
        })->get();

        $data->transform(function ($data) {
            $data->date = Carbon::parse($data->created_at)->locale('id')->translatedFormat('d F Y');
            $data->articles->articletag;
            $data->articles->user;
            return $data;
        });

        $trend = ArticleShow::orderBy('view', 'desc')
            ->where('status', 'publish')
            ->whereHas('articles', function ($query) {
                $query->whereNull('guardian_web_id');
            })
            ->take(6)->get();

        $data->withPath("/page");

        $hp = $this->getDefaultPhoneNumber();

        return view('guest.home', compact('data', 'trend', 'category', 'hp'));
    }

    public function article(Request $request, $username = null, $category = null, $tag = null)
    {
        Paginator::currentPageResolver(function () use ($request) {
            return $request->route('page', 1);
        });

        $page = $request->route('page') ?? null;

        if ((int) $request->route('page', 1) === 1) {
            if ($request->routeIs('pageallarticle')) {
                return redirect()->route('allarticle', $request->query(), 301);
            }

            if ($request->routeIs('pageauthor')) {
                return redirect()->route('author', ['username' => $username] + $request->query(), 301);
            }

            if ($request->routeIs('pagecategory')) {
                return redirect()->route('category', ['category' => $category] + $request->query(), 301);
            }

            if ($request->routeIs('pagetag')) {
                return redirect()->route('tag', ['tag' => $tag] + $request->query(), 301);
            }
        }

        if ($username) {
            $data = ArticleShow::whereHas('articles.user', function ($query) use ($username) {
                $query->where('slug', $username);
            })
                ->whereHas('articles', function ($query) {
                    $query->whereNull('guardian_web_id');
                })
                ->where('status', 'publish')->latest()->paginate(12);

            $user = User::where('slug', $username)->first();

            $data->withPath("/penulis/{$user->slug}/page");

            $title = 'Penulis : ' . $user->name;
        } elseif ($category) {
            $data = ArticleShow::whereHas('articles.articleCategory', function ($query) use ($category) {
                $query->where('slug', $category);
            })
                ->whereHas('articles', function ($query) {
                    $query->whereNull('guardian_web_id');
                })
                ->where('status', 'publish')->latest()->paginate(12);

            $data->withPath("/kategori/{$category}/page");

            $category = ArticleCategory::where('slug', $category)->first()->category;
            $title = 'Kategori : ' . $category;
        } elseif ($tag) {
            $data = ArticleShow::whereHas('articles.articleTag', function ($query) use ($tag) {
                $query->where('slug', $tag);
            })
                ->whereHas('articles', function ($query) {
                    $query->whereNull('guardian_web_id');
                })
                ->where('status', 'publish')->latest()->paginate(12);

            $data->withPath("/tag/{$tag}/page");

            $tag = ArticleTag::where('slug', $tag)->first()->tag;
            $title = 'Tag : ' . $tag;
        } elseif ($request->search) {
            $data = ArticleShow::where(function ($query) use ($request) {
                $query->where('judul', 'like', '%' . $request->search . '%')
                    ->orWhereHas('articles.articleCategory', function ($q) use ($request) {
                        $q->where('category', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('articles.articleTag', function ($q) use ($request) {
                        $q->where('tag', 'like', '%' . $request->search . '%');
                    });
            })
                ->where('status', 'publish')
                ->whereHas('articles', function ($query) {
                    $query->whereNull('guardian_web_id');
                })->latest()->paginate(12);

            $data->withPath("/artikel/page");
            $title = 'Pecaharian : ' . $request->search;
        } else {
            $data = ArticleShow::where('status', 'publish')
                ->whereHas('articles', function ($query) {
                    $query->whereNull('guardian_web_id');
                })
                ->latest()->paginate(12);

            $data->withPath("/artikel/page");
            $title = 'Artikel Terbaru';
        }

        $data->transform(function ($data) {
            $data->date = Carbon::parse($data->created_at)->locale('id')->translatedFormat('d F Y');
            $data->articles->articletag;
            $data->articles->user;
            return $data;
        });

        $category = ArticleCategory::whereHas('articles', function ($query) {
            $query->whereNull('guardian_web_id');
        })->get();

        $hp = $this->getDefaultPhoneNumber();

        return view('guest.article', compact('data', 'title', 'page', 'category', 'hp'));
    }

    public function business($slug)
    {
        $data = ArticleShow::with(['articles.articlecategory', 'articles.articletag', 'articles.user', 'articleshowgallery', 'phoneNumber'])
            ->where('slug', $slug)
            ->first();

        if (!$data || $data->articles->guardian_web_id) {
            return redirect()->route('not.found');
        }

        // Tambah view di article_show
        $data->increment('view');

        $nowHour = Carbon::now()->format('Y-m-d H:00:00');

        // Cari traffic dalam jam yang sama
        $traffic = Traffic::where('article_show_id', $data->id)
            ->whereBetween('created_at', [
                Carbon::parse($nowHour),
                Carbon::parse($nowHour)->addHour()
            ])
            ->first();

        if ($traffic) {
            // Sudah ada record di jam ini → tambah access
            $traffic->increment('access');
        } else {
            // Belum ada record → buat baru
            Traffic::create([
                'article_show_id' => $data->id,
                'article_id' => $data->article_id,
                'guardian_web_id' => $data->articles->guardian_web_id,
                'access' => 1,
            ]);
        }

        // dd($data->articles->phoneNumber);
        if ($data->phoneNumber) {
            $data->no_tlp = $data->phoneNumber->no_tlp;
        } elseif ($data->articles->articlecategory->first()?->phonenumber) {
            $data->no_tlp = $data->articles->articlecategory->first()->phoneNumber->no_tlp;
        } else {
            $data->no_tlp = $this->getDefaultPhoneNumber();
        }

        $data->date = Carbon::parse($data->created_at)->locale('id')->translatedFormat('d F Y');

        $categoryIds = $data->articles->articlecategory->pluck('id');

        $recommendations = ArticleShow::with(['articles.articlecategory', 'articles.user'])
            ->where('id', '!=', $data->id)
            ->where('status', 'publish')
            ->whereHas('articles', function ($query) use ($categoryIds) {
                $query->whereNull('guardian_web_id');
            })
            ->whereHas('articles.articlecategory', function ($query) use ($categoryIds) {
                $query->whereIn('article_categories.id', $categoryIds);
            })
            ->latest()
            ->take(6)
            ->get();

        $recommendations->transform(function ($item) {
            $item->date = Carbon::parse($item->created_at)->locale('id')->translatedFormat('d F Y');
            return $item;
        });

        $category = ArticleCategory::whereHas('articles', function ($query) {
            $query->whereNull('guardian_web_id');
        })->get();

        $hp = $this->getDefaultPhoneNumber();

        return view('guest.business', compact('data', 'category', 'hp', 'recommendations'));
    }

    public function notFound()
    {
        $category = ArticleCategory::whereHas('articles', function ($query) {
            $query->whereNull('guardian_web_id');
        })->get();

        $hp = $this->getDefaultPhoneNumber();

        return response()->view('guest.pagenotfound', compact('category', 'hp'), 404);
    }

    public function whatsapp(Request $request)
    {
        $article = ArticleShow::find($request->id);

        $nowHour = Carbon::now()->format('Y-m-d H:00:00');

        // Cari traffic dalam jam yang sama
        $watraffic = WaTraffic::where('article_show_id', $article->id)
            ->whereBetween('created_at', [
                Carbon::parse($nowHour),
                Carbon::parse($nowHour)->addHour()
            ])
            ->first();

        if ($watraffic) {
            // Sudah ada record di jam ini → tambah access
            $watraffic->increment('access');
        } else {
            // Belum ada record → buat baru
            WaTraffic::create([
                'article_show_id' => $article->id,
                'guardian_web_id' => $article->articles->guardian_web_id,
                'access' => 1,
            ]);
        }

        $message = 'Halo saya dapat info dari '. $article->slug;

        $url = 'https://wa.me/' . $request->phone. '?text=' . urlencode($message);

        return redirect()->away($url);
    }

    public function test()
    {
        $duplikatJudul = ArticleShow::select('judul')
            ->groupBy('judul')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('judul');

        if ($duplikatJudul->isEmpty()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Tidak ada judul yang duplikat.',
                'data' => []
            ]);
        }

        return response()->json([
            'status' => 'duplikat',
            'message' => 'Ditemukan judul yang duplikat.',
            'data' => $duplikatJudul
        ]);
    }
}
