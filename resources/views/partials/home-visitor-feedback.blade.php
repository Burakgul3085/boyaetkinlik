{{-- Yalnızca ana sayfa: footer öncesi, ortalanmış geri bildirim + onaylı yorumlar --}}
@php
    $oldRating = old('rating');
    $initialRating = is_numeric($oldRating) ? max(0, min(5, (int) $oldRating)) : 0;
@endphp
<section id="ziyaretci-geri-bildirim" class="w-full scroll-mt-28">
    <div class="rounded-3xl border border-violet-200/90 bg-gradient-to-br from-white via-violet-50/50 to-indigo-50/60 p-6 shadow-sm shadow-violet-100/30 dark:border-slate-700 dark:from-slate-900 dark:via-slate-900 dark:to-slate-900 sm:p-8 md:p-10">
        <header class="mx-auto w-full max-w-3xl text-center">
            <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-slate-100 md:text-2xl">İstek, öneri, şikayet veya yorum</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                Görüşlerinizi paylaşın; mesajınız yönetici onayından sonra bu alanda listelenir. Yıldız değerlendirmesi zorunludur.
            </p>
        </header>

        @if(session('feedback_success'))
            <div class="mx-auto mt-6 w-full max-w-3xl rounded-2xl border border-emerald-200/80 bg-emerald-50/90 px-4 py-3.5 text-center text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200">
                {{ session('feedback_success') }}
            </div>
        @endif

        @if($errors->any() && (old('_form') === 'visitor_feedback'))
            <div class="mx-auto mt-6 w-full max-w-3xl rounded-2xl border border-rose-200/80 bg-rose-50/90 px-4 py-3.5 text-sm text-rose-900 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-200">
                <ul class="list-disc space-y-1 pl-5 text-left">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mx-auto mt-8 w-full">
            <form method="post" action="{{ route('visitor-feedback.store') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="_form" value="visitor_feedback">

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label for="vf_first_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">İsim</label>
                        <input
                            id="vf_first_name"
                            type="text"
                            name="first_name"
                            value="{{ old('first_name') }}"
                            required
                            maxlength="120"
                            autocomplete="given-name"
                            placeholder="Adınız"
                            class="input-ui w-full placeholder:text-slate-400 dark:placeholder:text-slate-500"
                        >
                    </div>
                    <div class="space-y-1.5">
                        <label for="vf_last_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Soyad</label>
                        <input
                            id="vf_last_name"
                            type="text"
                            name="last_name"
                            value="{{ old('last_name') }}"
                            required
                            maxlength="120"
                            autocomplete="family-name"
                            placeholder="Soyadınız"
                            class="input-ui w-full placeholder:text-slate-400 dark:placeholder:text-slate-500"
                        >
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label for="vf_email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">E-posta</label>
                    <input
                        id="vf_email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        maxlength="255"
                        autocomplete="email"
                        placeholder="ornek@posta.com"
                        class="input-ui w-full placeholder:text-slate-400 dark:placeholder:text-slate-500"
                    >
                </div>

                {{-- Etkileşimli yıldız: gizli alan sunucuya 1–5 gönderir --}}
                <div
                    class="space-y-2 rounded-2xl border border-violet-100 bg-white/60 px-4 py-4 dark:border-slate-600 dark:bg-slate-800/40"
                    x-data="{ rating: {{ $initialRating }}, stars: [1, 2, 3, 4, 5] }"
                >
                    <div class="flex flex-wrap items-end justify-between gap-2">
                        <span id="vf-rating-label" class="text-sm font-medium text-slate-700 dark:text-slate-300">Puanınız (1–5 yıldız)</span>
                        <span class="text-xs font-medium tabular-nums text-violet-600 dark:text-violet-300" x-show="rating > 0" x-cloak x-text="rating + ' / 5'"></span>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2" role="group" aria-labelledby="vf-rating-label">
                        <template x-for="star in stars" :key="star">
                            <button
                                type="button"
                                class="rounded-lg p-0.5 text-3xl leading-none transition hover:scale-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900 sm:text-[2rem]"
                                :class="rating >= star ? 'text-amber-400 drop-shadow-[0_1px_2px_rgba(245,158,11,0.35)]' : 'text-slate-200 dark:text-slate-600'"
                                :aria-pressed="rating >= star"
                                :aria-label="star + ' yıldız'"
                                @click="rating = star"
                            >★</button>
                        </template>
                    </div>
                    <input type="hidden" name="rating" :value="rating > 0 ? rating : ''">
                    <p class="text-xs text-slate-500 dark:text-slate-400">1–5 arası puanı seçmek için yıldızların üzerine tıklayın.</p>
                </div>

                <div class="space-y-1.5">
                    <label for="vf_body" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Mesaj</label>
                    <textarea
                        id="vf_body"
                        name="body"
                        rows="5"
                        required
                        minlength="10"
                        maxlength="4000"
                        placeholder="İstek, öneri, şikayet veya yorumunuzu yazın…"
                        class="input-ui min-h-[8rem] w-full resize-y placeholder:text-slate-400 dark:placeholder:text-slate-500"
                    >{{ old('body') }}</textarea>
                    <p class="text-xs text-slate-500 dark:text-slate-400">En az 10 karakter.</p>
                </div>

                <div class="flex flex-col gap-3 border-t border-violet-100 pt-6 dark:border-slate-700 sm:flex-row sm:items-center sm:justify-end">
                    <button type="submit" class="btn-primary order-2 w-full justify-center px-8 py-3 text-[15px] shadow-md transition hover:opacity-95 active:scale-[0.99] sm:order-1 sm:w-auto">
                        Gönder
                    </button>
                </div>
            </form>
        </div>

        @if($approvedVisitorFeedback->isNotEmpty())
            <div class="mx-auto mt-12 w-full border-t border-violet-200/70 pt-10 dark:border-slate-700">
                <h3 class="text-center text-lg font-semibold text-slate-900 dark:text-slate-100">Onaylanmış yorumlar</h3>
                <p class="mx-auto mt-2 max-w-2xl text-center text-xs text-slate-500 dark:text-slate-400">Yalnızca yönetici onayından geçen mesajlar burada listelenir.</p>

                <div class="mt-6 max-h-[28rem] space-y-4 overflow-y-auto overscroll-contain rounded-2xl border border-violet-100/80 bg-violet-50/30 p-3 dark:border-slate-700 dark:bg-slate-800/30 sm:p-4">
                    @foreach($approvedVisitorFeedback as $fb)
                        <article class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-600 dark:bg-slate-800/95">
                            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-3 dark:border-slate-600/80">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-base font-semibold text-slate-900 dark:text-slate-100">{{ $fb->first_name }} {{ $fb->last_name }}</p>
                                    @if($fb->show_email_public)
                                        <p class="mt-1 text-xs">
                                            <a class="text-indigo-600 hover:underline dark:text-indigo-400" href="mailto:{{ $fb->email }}">{{ $fb->email }}</a>
                                        </p>
                                    @endif
                                    @if($fb->approved_at)
                                        <time class="mt-1 block text-[11px] font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500" datetime="{{ $fb->approved_at->toIso8601String() }}">
                                            {{ $fb->approved_at->format('d.m.Y') }}
                                        </time>
                                    @endif
                                </div>
                                <div class="flex shrink-0 items-center gap-0.5 text-amber-400" aria-label="{{ $fb->rating }} üzerinden 5 yıldız">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="text-lg leading-none sm:text-xl">{{ $i <= (int) $fb->rating ? '★' : '☆' }}</span>
                                    @endfor
                                </div>
                            </div>
                            <div class="mt-4 text-sm leading-relaxed text-slate-700 dark:text-slate-200">
                                <p class="whitespace-pre-wrap">{{ $fb->body }}</p>
                            </div>

                            @if($fb->admin_reply_published && trim((string) $fb->admin_reply) !== '')
                                <div class="mt-5 rounded-xl border border-indigo-200/90 bg-gradient-to-br from-indigo-50 to-violet-50/80 p-4 dark:border-indigo-800 dark:from-indigo-950/60 dark:to-slate-900/40">
                                    <p class="text-xs font-bold uppercase tracking-wide text-indigo-700 dark:text-indigo-300">Sayfa yöneticisi yanıtladı</p>
                                    <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-slate-800 dark:text-slate-200">{{ $fb->admin_reply }}</p>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
