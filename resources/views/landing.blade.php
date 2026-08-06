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
                x-text="lang === 'fil' ? 'EN' : 'FL'"
                :aria-label="lang === 'fil' ? 'Switch to English' : 'Switch to Filipino'"></button>
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
                    <a href="{{ route('login') }}" wire:navigate class="btn-outline border-white/30 text-white hover:bg-white/10 text-lg px-8 py-4">
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
                        x-text="lang === 'fil' ? '1. Humiling' : '1. Request'"></h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed"
                        x-text="lang === 'fil' ? 'Punan ang online form ng iyong mga detalye at piliin ang dokumentong kailangan mo. I-upload ang iyong ID para sa beripikasyon.' : 'Fill out the online form with your details and select the document you need. Upload your ID for verification.'"></p>
                </div>

                <div class="card-hover text-center">
                    <div class="w-16 h-16 bg-accent-100 dark:bg-accent-900/30 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <svg class="w-8 h-8 text-accent-700 dark:text-accent-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2"
                        x-text="lang === 'fil' ? '2. Subaybayan' : '2. Track'"></h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed"
                        x-text="lang === 'fil' ? 'Mag-log in anumang oras upang tingnan ang real-time na katayuan at posisyon sa pila ng iyong mga kahilingan sa dokumento.' : 'Log in anytime to check the real-time status and queue position of your document requests.'"></p>
                </div>

                <div class="card-hover text-center">
                    <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <svg class="w-8 h-8 text-green-700 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2"
                        x-text="lang === 'fil' ? '3. Tanggapin' : '3. Receive'"></h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed"
                        x-text="lang === 'fil' ? 'Makatanggap ng abiso kapag handa na ang iyong dokumento. Bisitahin ang barangay hall upang kunin ang iyong dokumento.' : 'Get notified when your document is ready. Visit the barangay hall to claim your document.'"></p>
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
                        x-text="lang === 'fil' ? 'Wala nang mahabang pila sa barangay hall.' : 'No more long lines at the barangay hall.'"></p>
                </div>
                <div class="card text-center">
                    <div class="w-12 h-12 bg-accent-100 dark:bg-accent-900/30 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-accent-700 dark:text-accent-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-1"
                        x-text="lang === 'fil' ? 'Ligtas' : 'Secure'"></h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400"
                        x-text="lang === 'fil' ? 'Ang iyong datos ay naka-encrypt at protektado.' : 'Your data is encrypted and protected.'"></p>
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
                        x-text="lang === 'fil' ? 'Dinisenyo para sa lahat ng residente, kabilang ang mga nakatatanda at PWDs.' : 'Designed for all residents, including seniors and PWDs.'"></p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
