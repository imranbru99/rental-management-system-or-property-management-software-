<x-filament-panels::page>
    @php
        $property = $this->getRecord();
        $stats = $this->stats();
        $cards = $this->unitCards();
        $floors = $property->floors->sortByDesc('level');
        $firstUnitId = $floors->first()?->units->first()?->id;
    @endphp

    <div
        class="rb"
        x-data="{ selected: {{ $firstUnitId ? (int) $firstUnitId : 'null' }}, cards: {{ \Illuminate\Support\Js::from($cards) }} }"
    >
        <div class="rb-stats">
            @foreach ([
                ['Floors', $stats['floors'], '#0f766e'],
                ['Units', $stats['units'], '#0e7490'],
                ['Rented', $stats['rented'], '#047857'],
                ['Vacant', $stats['vacant'], '#b45309'],
                ['Potential rent', $stats['rent'], '#1d4ed8'],
                ['Collected', $stats['collected'], '#6d28d9'],
            ] as [$label, $value, $color])
                <div class="rb-stat">
                    <span class="rb-stat-dot" style="background: {{ $color }}"></span>
                    <span class="rb-stat-label">{{ $label }}</span>
                    <strong class="rb-stat-value">{{ $value }}</strong>
                </div>
            @endforeach
        </div>

        @if ($floors->isEmpty())
            <div class="rb-empty">
                <p class="rb-empty-title">No floors yet</p>
                <p class="rb-empty-copy">Generate the building structure, then this page becomes a live picture of every floor and unit.</p>
                <a class="rb-empty-btn" href="{{ \App\Filament\Owner\Resources\Properties\PropertyResource::getUrl('layout', ['record' => $property]) }}">Generate floors</a>
            </div>
        @else
            <div class="rb-stage">
                <section class="rb-sky">
                    <div class="rb-sky-head">
                        <div>
                            <p class="rb-city">{{ $property->city }}</p>
                            <h3 class="rb-name">{{ $property->name }}</h3>
                        </div>
                        <div class="rb-legend">
                            <span><i class="rb-swatch rented"></i> Rented</span>
                            <span><i class="rb-swatch vacant"></i> Vacant</span>
                        </div>
                    </div>

                    <div class="rb-elevation">
                        <div class="rb-roof"></div>
                        <div class="rb-tower">
                            @foreach ($floors as $floor)
                                <div class="rb-floor">
                                    <div class="rb-floor-no">{{ $floor->level }}</div>
                                    <div class="rb-windows" style="--cols: {{ max(1, $floor->units->count()) }}">
                                        @foreach ($floor->units as $unit)
                                            @php $occupied = $unit->status === \App\Enums\PropertyStatus::Occupied; @endphp
                                            <div
                                                role="button"
                                                tabindex="0"
                                                class="rb-pad {{ $occupied ? 'is-rented' : 'is-vacant' }}"
                                                :class="{ 'is-on': selected == {{ $unit->id }} }"
                                                @click="selected = {{ $unit->id }}"
                                                @keydown.enter="selected = {{ $unit->id }}"
                                            >
                                                <span class="rb-glass"></span>
                                                <span class="rb-code">{{ $unit->code ?: $unit->name }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="rb-lobby">
                            <span class="rb-door"></span>
                            Lobby / entrance
                        </div>
                        <div class="rb-lawn"></div>
                    </div>
                </section>

                <aside class="rb-card">
                    <template x-if="selected && cards[selected]">
                        <div>
                            <p class="rb-card-kicker" x-text="cards[selected].floor"></p>
                            <h3 class="rb-card-title" x-text="cards[selected].code"></h3>
                            <p class="rb-card-sub" x-text="cards[selected].name"></p>
                            <span class="rb-pill" :class="cards[selected].occupied ? 'ok' : 'wait'" x-text="cards[selected].status_label"></span>
                            <div class="rb-facts">
                                <div><small>Rooms</small><b x-text="cards[selected].rooms"></b></div>
                                <div><small>Dining</small><b x-text="cards[selected].dining"></b></div>
                                <div><small>Kitchen</small><b x-text="cards[selected].kitchens"></b></div>
                                <div><small>Baths</small><b x-text="cards[selected].baths"></b></div>
                                <div><small>Balcony</small><b x-text="cards[selected].balconies"></b></div>
                                <div><small>Rent</small><b x-text="cards[selected].rent"></b></div>
                            </div>
                            <div class="rb-tenant">
                                <small>Tenant</small>
                                <strong x-text="cards[selected].tenant || 'Vacant — ready to rent'"></strong>
                                <span x-text="cards[selected].phone || ('Deposit '+cards[selected].deposit)"></span>
                            </div>
                        </div>
                    </template>
                    <p class="rb-card-empty" x-show="! selected">Click a unit on the building to see rooms, rent, and who lives there.</p>
                </aside>
            </div>

            <section class="rb-table-wrap">
                <div class="rb-table-head">
                    <h3>All units</h3>
                    <p>{{ $stats['units'] }} units · {{ $stats['rented'] }} rented · {{ $stats['vacant'] }} vacant</p>
                </div>
                <div class="rb-table-scroll">
                    <table class="rb-table">
                        <thead>
                            <tr>
                                <th>Unit</th>
                                <th>Floor</th>
                                <th>Status</th>
                                <th>Rooms</th>
                                <th>Dining</th>
                                <th>Kitchen</th>
                                <th>Baths</th>
                                <th>Balcony</th>
                                <th>Rent</th>
                                <th>Tenant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($property->floors->sortBy('level') as $floor)
                                @foreach ($floor->units as $unit)
                                    @php
                                        $row = $cards[$unit->id] ?? null;
                                        $occupied = (bool) ($row['occupied'] ?? false);
                                    @endphp
                                    @if ($row)
                                        <tr
                                            class="{{ $occupied ? 'is-rented' : 'is-vacant' }}"
                                            :class="{ 'is-on': selected == {{ $unit->id }} }"
                                            @click="selected = {{ $unit->id }}"
                                        >
                                            <td><strong>{{ $row['code'] }}</strong></td>
                                            <td>{{ $row['floor'] }}</td>
                                            <td><span class="rb-pill {{ $occupied ? 'ok' : 'wait' }}">{{ $row['status_label'] }}</span></td>
                                            <td>{{ $row['rooms'] }}</td>
                                            <td>{{ $row['dining'] }}</td>
                                            <td>{{ $row['kitchens'] }}</td>
                                            <td>{{ $row['baths'] }}</td>
                                            <td>{{ $row['balconies'] }}</td>
                                            <td>{{ $row['rent'] }}</td>
                                            <td>{{ $row['tenant'] ?: '—' }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>

    <style>
        .rb [x-cloak] { display: none !important; }
        .rb { display: flex; flex-direction: column; gap: 1.25rem; }
        .rb-stats {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 0.75rem;
        }
        .rb-stat {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            padding: 0.9rem 1rem;
            border-radius: 1rem;
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .rb-stat-dot { width: 0.55rem; height: 0.55rem; border-radius: 999px; }
        .rb-stat-label { font-size: 0.72rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
        .rb-stat-value { font-size: 1.15rem; color: #0f172a; }
        .rb-empty {
            text-align: center;
            padding: 4rem 1.5rem;
            border: 1px dashed #cbd5e1;
            border-radius: 1.5rem;
            background: linear-gradient(#f0f9ff, #fff);
        }
        .rb-empty-title { font-size: 1.15rem; font-weight: 700; }
        .rb-empty-copy { margin: 0.4rem auto 1.2rem; max-width: 28rem; color: #64748b; }
        .rb-empty-btn {
            display: inline-block;
            padding: 0.6rem 1rem;
            border-radius: 0.7rem;
            background: #0f766e;
            color: #fff;
            font-weight: 600;
            text-decoration: none;
        }
        .rb-stage {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 22rem;
            gap: 1.25rem;
            align-items: start;
        }
        .rb-sky {
            border-radius: 1.5rem;
            overflow: hidden;
            border: 1px solid #bae6fd;
            background: linear-gradient(180deg, #dbeafe 0%, #f8fafc 52%, #ecfeff 100%);
            box-shadow: 0 18px 40px rgba(14, 165, 233, 0.12);
        }
        .rb-sky-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 1.1rem 1.3rem 0.4rem;
        }
        .rb-city { margin: 0; font-size: 0.7rem; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: #0369a1; }
        .rb-name { margin: 0.15rem 0 0; font-size: 1.2rem; color: #0f172a; }
        .rb-legend { display: flex; gap: 0.9rem; font-size: 0.78rem; color: #475569; }
        .rb-legend span { display: inline-flex; align-items: center; gap: 0.35rem; }
        .rb-swatch { width: 0.7rem; height: 0.7rem; border-radius: 0.2rem; display: inline-block; }
        .rb-swatch.rented { background: #2dd4bf; box-shadow: 0 0 10px rgba(45, 212, 191, .7); }
        .rb-swatch.vacant { background: #fcd34d; }
        .rb-elevation { max-width: 44rem; margin: 0 auto; padding: 0.6rem 1.2rem 1.4rem; }
        .rb-roof {
            height: 2.6rem;
            width: 78%;
            margin: 0 auto -2px;
            background: linear-gradient(180deg, #0f766e, #115e59);
            clip-path: polygon(6% 100%, 50% 0, 94% 100%);
        }
        .rb-tower {
            border: 8px solid #94a3b8;
            border-bottom: 0;
            background: #e2e8f0;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);
        }
        .rb-floor {
            display: grid;
            grid-template-columns: 2.2rem 1fr;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            min-height: 3.4rem;
        }
        .rb-floor-no {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.68rem;
            font-weight: 800;
            color: #475569;
            background: rgba(255,255,255,0.55);
        }
        .rb-windows {
            display: grid;
            grid-template-columns: repeat(var(--cols), minmax(0, 1fr));
            gap: 0.4rem;
            padding: 0.4rem;
        }
        .rb-pad {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.18rem;
            padding: 0.15rem;
            border-radius: 0.55rem;
            cursor: pointer;
            user-select: none;
        }
        .rb-pad.is-on { outline: 2px solid #0d9488; background: rgba(13, 148, 136, 0.12); }
        .rb-glass {
            display: block;
            width: 100%;
            height: 2.15rem;
            border-radius: 0.28rem;
            background:
                linear-gradient(90deg, transparent 48%, rgba(15,23,42,.28) 48% 52%, transparent 52%),
                linear-gradient(180deg, transparent 48%, rgba(15,23,42,.28) 48% 52%, transparent 52%),
                linear-gradient(160deg, #67e8f9, #155e75);
        }
        .rb-pad.is-rented .rb-glass {
            background:
                linear-gradient(90deg, transparent 48%, rgba(15,23,42,.22) 48% 52%, transparent 52%),
                linear-gradient(180deg, transparent 48%, rgba(15,23,42,.22) 48% 52%, transparent 52%),
                linear-gradient(160deg, #5eead4, #0f766e);
            box-shadow: 0 0 12px rgba(45, 212, 191, 0.45);
        }
        .rb-pad.is-vacant .rb-glass {
            background:
                linear-gradient(90deg, transparent 48%, rgba(120,53,15,.25) 48% 52%, transparent 52%),
                linear-gradient(180deg, transparent 48%, rgba(120,53,15,.25) 48% 52%, transparent 52%),
                linear-gradient(160deg, #fde68a, #d97706);
        }
        .rb-code { font-size: 0.62rem; font-weight: 800; color: #334155; }
        .rb-lobby {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.35rem;
            padding: 0.7rem 0 0.45rem;
            background: linear-gradient(180deg, #94a3b8, #64748b);
            color: #fff;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .rb-door {
            width: 2.1rem;
            height: 2.3rem;
            border-radius: 0.3rem 0.3rem 0 0;
            background: #0f172a;
        }
        .rb-lawn {
            height: 0.9rem;
            margin-top: 0.35rem;
            border-radius: 999px;
            background: linear-gradient(90deg, #86efac, #22c55e);
        }
        .rb-card {
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1.5rem;
            padding: 1.2rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }
        .rb-card-kicker { margin: 0; font-size: 0.72rem; font-weight: 700; color: #0f766e; text-transform: uppercase; letter-spacing: .08em; }
        .rb-card-title { margin: 0.2rem 0 0; font-size: 1.7rem; }
        .rb-card-sub { margin: 0.15rem 0 0; color: #64748b; }
        .rb-card-empty { color: #64748b; }
        .rb-pill {
            display: inline-flex;
            margin-top: 0.7rem;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 700;
        }
        .rb-pill.ok { background: #ccfbf1; color: #0f766e; }
        .rb-pill.wait { background: #fef3c7; color: #b45309; }
        .rb-facts {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .rb-facts div {
            background: #f8fafc;
            border-radius: 0.8rem;
            padding: 0.55rem 0.7rem;
        }
        .rb-facts small { display: block; color: #64748b; font-size: 0.7rem; }
        .rb-facts b { font-size: 0.95rem; }
        .rb-tenant {
            margin-top: 1rem;
            padding: 0.9rem 1rem;
            border-radius: 1rem;
            background: #0f172a;
            color: #fff;
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }
        .rb-tenant small { color: #94a3b8; font-size: 0.68rem; text-transform: uppercase; letter-spacing: .08em; }
        .rb-tenant span { color: #cbd5e1; font-size: 0.85rem; }
        .rb-table-wrap {
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        }
        .rb-table-head { padding: 1rem 1.2rem 0.4rem; }
        .rb-table-head h3 { margin: 0; font-size: 1.05rem; }
        .rb-table-head p { margin: 0.2rem 0 0; color: #64748b; font-size: 0.85rem; }
        .rb-table-scroll { overflow-x: auto; }
        .rb-table { width: 100%; border-collapse: collapse; font-size: 0.86rem; }
        .rb-table th {
            text-align: left;
            padding: 0.7rem 0.9rem;
            background: #f1f5f9;
            color: #475569;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .rb-table td { padding: 0.65rem 0.9rem; border-top: 1px solid #e2e8f0; }
        .rb-table tbody tr { cursor: pointer; }
        .rb-table tbody tr:hover { background: #f0fdfa; }
        .rb-table tbody tr.is-on { background: #ccfbf1; }
        .rb-table tbody tr.is-rented td:first-child { box-shadow: inset 3px 0 0 #0d9488; }
        .rb-table tbody tr.is-vacant td:first-child { box-shadow: inset 3px 0 0 #d97706; }
        @media (max-width: 1100px) {
            .rb-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .rb-stage { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .rb-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .dark .rb-stat, .dark .rb-card, .dark .rb-table-wrap { background: #111827; border-color: rgba(255,255,255,.08); }
        .dark .rb-stat-value, .dark .rb-name, .dark .rb-card-title, .dark .rb-table-head h3 { color: #f8fafc; }
        .dark .rb-table th { background: #1f2937; color: #94a3b8; }
        .dark .rb-table td { border-top-color: #1f2937; }
        .dark .rb-facts div { background: #1f2937; }
    </style>
</x-filament-panels::page>
