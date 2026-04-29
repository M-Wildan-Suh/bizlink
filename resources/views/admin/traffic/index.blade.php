<x-app-layout head="Traffic" title="Admin - Traffic">
    <div class="sm:pl-12 sm:pr-12 lg:pr-32 duration-300 pt-8 pb-20 sm:pb-8 px-4 space-y-6">
        <div class="w-full p-4 sm:p-8 bg-white rounded-md shadow-md shadow-black/20">
            <div class="space-y-4 sm:space-y-6">
                <div class=" w-full flex sm:items-center flex-col sm:flex-row justify-between gap-4">
                    <div class=" flex flex-col sm:flex-row gap-2">
                        <div class=" font-semibold px-4 py-1.5 rounded-md bg-byolink-1 text-white capitalize">Access This
                            {{ $mode }} : {{ number_format($totalaccess, 0, ',', '.') }}</div>
                        <div class=" font-semibold px-4 py-1.5 rounded-md bg-green-500 text-white capitalize">WhatsApp
                            This
                            {{ $mode }} : {{ number_format($totalwaaccess, 0, ',', '.') }}</div>
                    </div>
                    <div class=" w-auto grid grid-cols-3 gap-3 text-sm">
                        <a href="{{ route('traffic.index', ['mode' => 'day', 'list' => $list]) }}"
                            class="{{ $mode == 'day' ? 'bg-byolink-1 text-white' : ' text-black hover:text-white bg-neutral-200 hover:bg-byolink-1' }} text-nowrap w-full text-center px-2 py-1.5 font-semibold rounded-md duration-300">
                            Day
                        </a>
                        <a href="{{ route('traffic.index', ['mode' => 'week', 'list' => $list]) }}"
                            class="{{ $mode == 'week' ? 'bg-byolink-1 text-white' : ' text-black hover:text-white bg-neutral-200 hover:bg-byolink-1' }} text-nowrap w-full text-center px-2 py-1.5 font-semibold rounded-md duration-300">
                            Week
                        </a>
                        <a href="{{ route('traffic.index', ['mode' => 'month', 'list' => $list]) }}"
                            class="{{ $mode == 'month' ? 'bg-byolink-1 text-white' : ' text-black hover:text-white bg-neutral-200 hover:bg-byolink-1' }} text-nowrap w-full text-center px-2 py-1.5 font-semibold rounded-md duration-300">
                            Month
                        </a>
                    </div>
                </div>

                <div class="w-full text-sm sm:text-base">
                    <div x-data="lineChart()" x-init="init()" class="w-full bg-white rounded-xl">
                        <div id="chart"></div>
                    </div>

                    <script>
                        function lineChart() {
                            return {
                                chart: null,

                                init() {
                                    const options = {
                                        chart: {
                                            type: "line",
                                            height: 300,
                                            toolbar: {
                                                show: false
                                            }
                                        },

                                        series: [{
                                            name: "Article Access",
                                            data: @json($traffic['values'])
                                        }, {
                                            name: "WhatsApp Access",
                                            data: @json($traffic['waValues'])
                                        }],

                                        xaxis: {
                                            categories: @json($traffic['labels']),
                                            labels: {
                                                style: {
                                                    rotate: 0,
                                                    rotateAlways: true,
                                                    fontSize: "13px"
                                                }
                                            },
                                            tickAmount: 4,
                                        },

                                        yaxis: {
                                            labels: {
                                                style: {
                                                    fontSize: "13px"
                                                }
                                            }
                                        },

                                        responsive: [{
                                            breakpoint: 640,
                                            options: {
                                                xaxis: {
                                                    tickAmount: 3
                                                }
                                            }
                                        }],

                                        stroke: {
                                            width: 2,
                                            curve: "straight"
                                        },

                                        colors: ["#3b82f6", "#22c55e"],
                                        legend: {
                                            show: true,
                                            position: "bottom"
                                        },
                                        grid: {
                                            borderColor: "#E5E7EB"
                                        },

                                        plugins: {
                                            zoom: {
                                                zoom: {
                                                    wheel: {
                                                        enabled: false
                                                    },
                                                    pinch: {
                                                        enabled: false
                                                    },
                                                    mode: 'xy'
                                                },
                                                pan: {
                                                    enabled: false
                                                }
                                            }
                                        }
                                    };

                                    if (this.chart) {
                                        this.chart.destroy();
                                    }

                                    this.chart = new ApexCharts(document.querySelector("#chart"), options);
                                    this.chart.render();
                                }
                            }
                        }
                    </script>

                </div>
            </div>
        </div>
        <div class="w-full p-4 sm:p-8 bg-white rounded-md shadow-md shadow-black/20">
            <div x-data="{ tab: 'guardian' }" class="space-y-8">
                <div class=" w-full flex flex-col md:flex-row justify-between gap-4">
                    <div class=" grid grid-cols-3 gap-2 sm:gap-4 w-full md:w-auto">
                        <a href="{{ route('traffic.index', ['mode' => $mode, 'list' => 'guardian', 'sort' => $sort, 'direction' => $direction]) }}"
                            class=" {{ $list === 'guardian' ? 'bg-byolink-1 text-white' : 'text-black rounded-md hover:text-white bg-neutral-200 hover:bg-byolink-1' }} text-nowrap w-full text-center text-sm sm:text-base px-4 py-2 font-semibold rounded-md duration-300">
                            Guardian
                        </a>
                        <a href="{{ route('traffic.index', ['mode' => $mode, 'list' => 'category', 'sort' => $sort, 'direction' => $direction]) }}"
                            class=" {{ $list === 'category' ? 'bg-byolink-1 text-white' : 'text-black rounded-md hover:text-white bg-neutral-200 hover:bg-byolink-1' }} text-nowrap w-full text-center text-sm sm:text-base px-4 py-2 font-semibold rounded-md duration-300">
                            Category
                        </a>
                        <a href="{{ route('traffic.index', ['mode' => $mode, 'list' => 'article', 'sort' => $sort, 'direction' => $direction]) }}"
                            class=" {{ $list === 'article' ? 'bg-byolink-1 text-white' : 'text-black rounded-md hover:text-white bg-neutral-200 hover:bg-byolink-1' }} text-nowrap w-full text-center text-sm sm:text-base px-4 py-2 font-semibold rounded-md duration-300">
                            Article
                        </a>
                    </div>

                    <div class="flex items-center gap-2">
                        <label for="sort_filter" class="text-sm font-semibold text-neutral-600">Urutkan:</label>
                        <select id="sort_filter" onchange="location = this.value;" class="text-sm border-neutral-300 rounded-md focus:ring-byolink-1 focus:border-byolink-1">
                            <option value="{{ route('traffic.index', ['mode' => $mode, 'list' => $list, 'sort' => 'access', 'direction' => 'desc', 'start' => $start, 'end' => $end]) }}" {{ $sort === 'access' && $direction === 'desc' ? 'selected' : '' }}>Terbanyak Access</option>
                            <option value="{{ route('traffic.index', ['mode' => $mode, 'list' => $list, 'sort' => 'access', 'direction' => 'asc', 'start' => $start, 'end' => $end]) }}" {{ $sort === 'access' && $direction === 'asc' ? 'selected' : '' }}>Terkecil Access</option>
                            <option value="{{ route('traffic.index', ['mode' => $mode, 'list' => $list, 'sort' => 'wa_access', 'direction' => 'desc', 'start' => $start, 'end' => $end]) }}" {{ $sort === 'wa_access' && $direction === 'desc' ? 'selected' : '' }}>Terbanyak WhatsApp</option>
                            <option value="{{ route('traffic.index', ['mode' => $mode, 'list' => $list, 'sort' => 'wa_access', 'direction' => 'asc', 'start' => $start, 'end' => $end]) }}" {{ $sort === 'wa_access' && $direction === 'asc' ? 'selected' : '' }}>Terkecil WhatsApp</option>
                        </select>
                    </div>
                </div>

                <div class=" space-y-4">
                    <table class=" w-full">
                        <tr class=" border-b">
                            <th class=" pb-4 text-left capitalize">{{ $list }}</th>
                            <th class=" pb-4 text-right">Access</th>
                            <th class=" pb-4 text-right">WhatsApp</th>
                        </tr>
                        <tbody id="container">
                            @include('admin.traffic.row')
                        </tbody>
                        <tr>
                            <td id="loader" colspan="6" class=" text-center text-neutral-600 h-10">
                                @if ($list === 'guardian')
                                    {{ $guardians->count() > 10 ? 'Loading...' : 'Semua data telah dimuat' }}
                                @endif
                                @if ($list === 'category')
                                    {{ $categories->count() > 10 ? 'Loading...' : 'Semua data telah dimuat' }}
                                @endif
                                @if ($list === 'article')
                                    {{ $articles->count() > 10 ? 'Loading...' : 'Semua data telah dimuat' }}
                                @endif
                            </td>
                        </tr>
                    </table>
                    <script>
                        let page = 2;
                        let loading = false;

                        function tableToggle() {
                            return {
                                openedIds: [],
                                detail(id) {
                                    const index = this.openedIds.indexOf(id);
                                    if (index === -1) {
                                        this.openedIds.push(id); // buka
                                    } else {
                                        this.openedIds.splice(index, 1); // tutup
                                    }
                                }
                            };
                        }

                        window.addEventListener('scroll', () => {
                            if (loading) return;

                            const loader = document.getElementById('loader');

                            const list = "{!! $list ? '&list=' . urlencode($list) : '' !!}";
                            const mode = "{!! $mode ? '&mode=' . urlencode($mode) : '' !!}";
                            const start = "{!! $start ? '&start=' . urlencode($start) : '' !!}";
                            const end = "{!! $end ? '&end=' . urlencode($end) : '' !!}";
                            const sort = "{!! $sort ? '&sort=' . urlencode($sort) : '' !!}";
                            const direction = "{!! $direction ? '&direction=' . urlencode($direction) : '' !!}";

                            // Scroll benar-benar mentok
                            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
                                loading = true;
                                loader.textContent = 'Loading...';

                                fetch(`?page=${page}${list}${mode}${start}${end}${sort}${direction}`, {
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }
                                    })
                                    .then(response => response.text())
                                    .then(html => {
                                        // Tambahkan delay 1 detik sebelum tampilkan data
                                        setTimeout(() => {
                                            if (html.trim() !== '') {
                                                document.getElementById('container').insertAdjacentHTML(
                                                    'beforeend', html);
                                                page++;
                                                loading = false;
                                                loader.textContent = 'Loading...';
                                            } else {
                                                loader.textContent = 'Semua data telah dimuat';
                                            }
                                        }, 500); // delay 1 detik
                                    })
                                    .catch(() => {
                                        loader.textContent = 'Gagal memuat data';
                                        loading = false;
                                    });
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
