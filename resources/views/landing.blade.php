@extends('layouts.guest')

@section('title', 'Online Barangay Document Request System')

@section('content')
<div x-data="{
    lang: localStorage.getItem('landing_lang') || 'fil',
    toggle() {
        this.lang = this.lang === 'fil' ? 'en' : 'fil';
        localStorage.setItem('landing_lang', this.lang);
    }
}" class="min-h-[calc(100vh-4rem)]">
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <section class="relative overflow-hidden bg-gradient-to-br from-primary-900 via-primary-800 to-primary-900 dark:from-gray-950 dark:via-primary-900 dark:to-gray-950">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-20 -right-20 sm:-top-40 sm:-right-40 w-40 h-40 sm:w-80 sm:h-80 bg-accent-500 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 sm:-bottom-40 sm:-left-40 w-40 h-40 sm:w-80 sm:h-80 bg-accent-400 rounded-full blur-3xl"></div>
        </div>

        <div class="absolute top-4 right-4 sm:top-6 sm:right-6 z-10">
            <button type="button" @@click="toggle()"
                class="px-3 py-1.5 text-sm font-semibold rounded-lg bg-white/10 backdrop-blur text-white hover:bg-white/20 border border-white/20 transition"
                aria-label="Switch language">
                <span :class="lang === 'fil' ? 'text-white' : 'text-white/50'">FIL</span>
                <span class="text-white/50 mx-1">|</span>
                <span :class="lang === 'en' ? 'text-white' : 'text-white/50'">ENG</span>
            </button>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 lg:py-32">
            <div class="text-center max-w-3xl mx-auto">
                <div class="flex justify-center mb-6">
                    <div class="w-20 h-20 bg-white/10 backdrop-blur rounded-2xl flex items-center justify-center ring-1 ring-white/20">
                        <span class="text-white font-bold text-3xl">EC</span>
                    </div>
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-4 tracking-tight">
                    E-CiudAgad
                </h1>
                <p class="text-xl sm:text-2xl text-accent-300 font-medium mb-4"
                    x-text="lang === 'fil' ? 'Sistema ng Online na Paghiling ng Dokumento ng Barangay' : 'Online Barangay Document Request System'"></p>
                <p class="text-base sm:text-lg text-blue-100 max-w-2xl mx-auto mb-10 leading-relaxed"
                    x-text="lang === 'fil' ? 'Humingi ng mga dokumento ng barangay online nang hindi na kailangang pumila nang matagal.
                    Maginhawang subaybayan ang iyong mga kahilingan mula sa bahay at makatanggap ng mga abiso kapag handa na ang iyong mga dokumento para sa pagkuha.' : 'Request barangay documents online without waiting in long lines. Conveniently track your requests from home and receive notifications when your documents are ready for pickup.'"></p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" wire:navigate class="btn-accent text-lg px-8 py-4 shadow-xl hover:shadow-2xl">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        <span x-text="lang === 'fil' ? 'Ako Ay Magpaparehistro Pa Lamang' : 'I\u0027m a New Register'"></span>
                    </a>
                    <a href="{{ route('login') }}" wire:navigate class="inline-flex items-center justify-center rounded-xl border-2 border-white/70 bg-white/10 text-white font-semibold text-lg px-8 py-4 shadow-sm transition-all duration-200 hover:bg-white hover:text-primary-900 hover:border-white focus:outline-none focus:ring-4 focus:ring-white/20">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        <span x-text="lang === 'fil' ? 'Mag-log In Kung May Account Ka Na' : 'Returning Resident \u2014 Log in'"></span>
                    </a>
                </div>
                <p class="mt-4 text-sm text-blue-100" x-html="lang === 'fil' ? 'Ang mga bumabalik na residente ay mag-log in gamit ang inyong <strong>Tracking Number</strong> at <strong>PIN</strong>.' : 'Returning residents log in using your <strong>Tracking Number</strong> and <strong>PIN</strong>.'"></p>
            </div>
        </div>
    </section>

    <section
        class="py-12 sm:py-16
            bg-gradient-to-b
            from-slate-50 to-blue-50/60
            dark:from-gray-950 dark:to-slate-900"
        aria-labelledby="queue-monitor-title"
    >
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2
                            px-3 py-1.5 rounded-full
                            bg-primary-100
                            dark:bg-primary-900/30
                            text-primary-700
                            dark:text-primary-300
                            text-xs font-semibold mb-3">

                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full
                                    rounded-full bg-green-400
                                    opacity-75 animate-ping"></span>
                        <span class="relative inline-flex rounded-full
                                    h-2.5 w-2.5 bg-green-500"></span>
                    </span>

                    <span x-text="lang === 'fil'
                        ? 'Live Queue Status'
                        : 'Live Queue Status'"></span>
                </div>

                <h2 id="queue-monitor-title"
                    class="text-2xl sm:text-3xl font-bold
                        text-gray-900 dark:text-white"
                    x-text="lang === 'fil'
                        ? 'Monitor ng Pila'
                        : 'Queue Monitor'">
                </h2>

                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                    <span x-text="lang === 'fil'
                        ? 'Oras sa Maynila (Asia/Manila)'
                        : 'Manila time (Asia/Manila)'"></span>

                    <span class="mx-1">•</span>

                    <span id="public-queue-time">—</span>
                </p>
            </div>

            <div class="card-hover text-center">
                <div class="grid grid-cols-1 md:grid-cols-2">

                    {{-- NOW SERVING --}}
                    <div class="relative p-8 sm:p-10 text-center
                                border-b md:border-b-0 md:border-r
                                border-gray-200 dark:border-gray-700">

                        <div class="w-12 h-12 mx-auto mb-4
                                    rounded-2xl
                                    bg-primary-100
                                    dark:bg-primary-900/40
                                    flex items-center justify-center">

                            <svg class="w-6 h-6
                                        text-primary-700
                                        dark:text-primary-300"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032
                                        2.032 0 0118 14.158V11a6.002
                                        6.002 0 00-4-5.659V5a2 2
                                        0 10-4 0v.341C7.67 6.165 6
                                        8.388 6 11v3.159c0 .538-.214
                                        1.055-.595 1.436L4 17h5m6
                                        0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>

                        <p class="text-xs sm:text-sm
                                font-bold uppercase tracking-[0.16em]
                                text-gray-500 dark:text-gray-400"
                        x-text="lang === 'fil'
                            ? 'Kasalukuyang Tinatawag'
                            : 'Now Serving'">
                        </p>

                        <p id="public-now-serving"
                        class="text-5xl sm:text-6xl
                                font-black
                                text-primary-700
                                dark:text-primary-300
                                mt-4 font-mono">
                            —
                        </p>

                        <p id="public-now-serving-detail"
                        class="text-sm
                                text-gray-600
                                dark:text-gray-400
                                mt-3">
                        </p>

                    </div>


                    {{-- NEXT UP --}}
                    <div class="p-8 sm:p-10 text-center">

                        <div class="w-12 h-12 mx-auto mb-4
                                    rounded-2xl
                                    bg-amber-100
                                    dark:bg-amber-900/30
                                    flex items-center justify-center">

                            <svg class="w-6 h-6
                                        text-amber-700
                                        dark:text-amber-300"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9
                                        0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-xs sm:text-sm
                                font-bold uppercase tracking-[0.16em]
                                text-gray-500 dark:text-gray-400"
                        x-text="lang === 'fil'
                            ? 'Susunod'
                            : 'Next Up'">
                        </p>
                        <div id="public-next-up"
                            class="flex flex-wrap gap-3
                                    justify-center mt-5">
                        </div>
                        <p id="public-remaining"
                        class="text-sm font-medium
                                text-gray-600
                                dark:text-gray-400
                                mt-5">
                        </p>
                    </div>
                </div>

                <div class="px-6 py-3
                            border-t
                            border-gray-200
                            dark:border-gray-700
                            bg-gray-50/80
                            dark:bg-gray-950/50
                            text-center">

                    <p class="text-xs
                            text-gray-500
                            dark:text-gray-400">

                        <span x-text="lang === 'fil'
                            ? 'Huling na-update'
                            : 'Last updated'"></span>:

                        <span id="public-last-updated">—</span>

                    </p>

                </div>

            </div>
        </div>
    </section>
    <script>
    (function () {
        function renderPublicQueue(data) {
            var nowServing = data.now_serving;

            var nowServingEl =
                document.getElementById('public-now-serving');

            if (nowServing) {
                nowServingEl.textContent = nowServing.queue_number;
                nowServingEl.className =
                    'text-5xl sm:text-6xl font-black text-primary-700 dark:text-primary-300 mt-4 font-mono';
            } else {
                nowServingEl.textContent = 'Waiting';
                nowServingEl.className =
                    'text-3xl sm:text-4xl font-bold text-gray-400 dark:text-gray-500 mt-4';
            }

            document.getElementById('public-now-serving-detail').textContent =
                nowServing
                    ? ((nowServing.document || '') + ' — Window ' + nowServing.window)
                    : '';

            var nextUpEl = document.getElementById('public-next-up');
            nextUpEl.innerHTML = '';

            (data.next_up || []).forEach(function (req) {
                var box = document.createElement('div');

                box.className =
                    'rounded-2xl px-5 py-3 text-center min-w-[110px] ' +
                    'border border-amber-200 dark:border-amber-800/60 ' +
                    'bg-amber-50 dark:bg-amber-900/20 ' +
                    'shadow-sm transition';

                var num = document.createElement('p');
                num.className =
                    'text-2xl font-black text-amber-700 dark:text-amber-300 font-mono';
                num.textContent = req.queue_number;

                var doc = document.createElement('p');
                doc.className =
                    'text-xs text-gray-500 dark:text-gray-400 mt-1';
                doc.textContent = req.document || '';

                box.appendChild(num);
                box.appendChild(doc);

                nextUpEl.appendChild(box);
            });

            var remainingEl =
                document.getElementById('public-remaining');

            if (data.remaining_count > 0) {
                remainingEl.textContent =
                    data.remaining_count + ' in queue';
            } else {
                remainingEl.textContent =
                    'No residents are currently waiting.';
            }

            document.getElementById('public-last-updated').textContent =
                data.last_updated
                    ? new Date(
                        data.last_updated.replace(' ', 'T') + '+08:00'
                    ).toLocaleTimeString(
                        'en-PH',
                        {
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                            hour12: true,
                            timeZone: 'Asia/Manila'
                        }
                    )
                    : '—';
            }
            function refreshPublicQueue() {
                fetch('{{ route('queue.public-status') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (res) { return res.json(); })
                    .then(function (data) { if (data.ok) renderPublicQueue(data); })
                    .catch(function () {});
            }
            function tickPublicClock() {
                try {
                    document.getElementById('public-queue-time').textContent = new Date().toLocaleString('en-PH', { timeZone: 'Asia/Manila', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                } catch (e) {}
            }
            refreshPublicQueue();
            tickPublicClock();
            setInterval(refreshPublicQueue, 30000);
            setInterval(tickPublicClock, 1000);
        })();
    </script>

    <section class="py-16 sm:py-20 bg-white dark:bg-gray-950" aria-labelledby="how-it-works-title">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 id="how-it-works-title" class="text-3xl sm:text-4xl font-bold text-center text-gray-900 dark:text-white mb-4"
                x-text="lang === 'fil' ? 'Paano Ba Ito Gumagana?' : 'How It Works'"></h2>
            <p class="text-center text-gray-600 dark:text-gray-400 mb-12 max-w-xl mx-auto"
                x-text="lang === 'fil' ? 'Tatlong simpleng hakbang para makuha ang iyong mga dokumento ng barangay' : 'Three simple steps to get your barangay documents'"></p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card-hover text-center">
                    <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <svg class="w-8 h-8 text-primary-700 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2"
                        x-text="lang === 'fil' ? '1. Sagutan ang Form' : '1. Fill Out the Form'"></h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed"
                        x-text="lang === 'fil' ? 'Punan ang online form ng iyong mga detalye, piliin ang dokumentong kailangan mo, at i-upload ang iyong patunay ng pagkakakilanlan.' : 'Complete the online form with your details, choose the document you need, and upload your proof of identity.'"></p>
                </div>

                <div class="card-hover text-center">
                    <div class="w-16 h-16 bg-accent-100 dark:bg-accent-900/30 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <svg class="w-8 h-8 text-accent-700 dark:text-accent-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2"
                        x-text="lang === 'fil' ? '2. Hintayin ang Iyong Queue Number' : '2. Wait for Your Queue Number'"></h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed"
                        x-text="lang === 'fil' ? 'Matatanggap mo ang iyong queue number at control number. Subaybayan ang monitor ng pila upang makita kung kailan ka tatawagin.' : 'You will receive your queue number and control number. Watch the queue monitor to see when you will be called.'"></p>
                </div>

                <div class="card-hover text-center">
                    <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <svg class="w-8 h-8 text-green-700 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2"
                        x-text="lang === 'fil' ? '3. Magpakita sa Window' : '3. Present at the Window'"></h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed"
                        x-text="lang === 'fil' ? 'Kapag tinawag ang iyong queue number, magpakita sa takdang window dala ang iyong control number upang makuha ang iyong dokumento.' : 'When your queue number is called, present yourself at the assigned window with your control number to claim your document.'"></p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 bg-gray-50 dark:bg-gray-900" aria-labelledby="benefits-title">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 id="benefits-title" class="text-3xl sm:text-4xl font-bold text-center text-gray-900 dark:text-white mb-12"
                x-text="lang === 'fil' ? 'Bakit Mahalagang Gamitin ang E-CiudAgad?' : 'Why Use E-CiudAgad?'"></h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="card text-center">
                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-primary-700 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-1"
                        x-text="lang === 'fil' ? 'Makatipid ng Oras' : 'Save Time'"></h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400"
                        x-text="lang === 'fil' ? 'Bibihira nalang ang pila sa barangay hall.' : 'Less long lines at the barangay hall.'"></p>
                </div>
                <div class="card text-center">
                    <div class="w-12 h-12 bg-accent-100 dark:bg-accent-900/30 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-accent-700 dark:text-accent-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-1"
                        x-text="lang === 'fil' ? 'Ligtas' : 'Secure'"></h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400"
                        x-text="lang === 'fil' ? 'Ang iyong datos ay protektado.' : 'Your data is protected.'"></p>
                </div>
                <div class="card text-center">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-green-700 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-1"
                        x-text="lang === 'fil' ? 'Patas na Pila' : 'Fair Queueing'"></h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400"
                        x-text="lang === 'fil' ? 'Pagpoproseso na nakabatay sa priyoridad para sa mga agarang pangangailangan.' : 'Priority-based processing for urgent needs.'"></p>
                </div>
                <div class="card text-center">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-purple-700 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-1"
                        x-text="lang === 'fil' ? 'Madaling Gamitin' : 'Accessible'"></h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400"
                        x-text="lang === 'fil' ? 'Dinisenyo para sa lahat ng residente, kabilang ang mga nakatatanda at non-techies.' : 'Designed for all residents, including seniors and non-tech users.'"></p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
