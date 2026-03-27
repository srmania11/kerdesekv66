<div id="storage-info-content" class="bg-slate-800/30 border border-slate-700 rounded-none p-6 space-y-8 animate-fade-in relative">

    <button onclick="closeStorageInfo()" class="absolute top-4 right-4 text-slate-400 hover:text-white transition-colors z-20" aria-label="Bezárás">
        <i class="fas fa-times fa-lg"></i>
    </button>

    <!-- Header -->
    <div class="border-b border-slate-700 pb-4 mb-6">
        <h2 class="text-2xl font-bold text-white flex items-center gap-3">
            <i class="fas fa-server text-cyan-400"></i>
            <span class="bg-clip-text text-transparent bg-gradient-to-r from-cyan-400 to-blue-500">Tárhely és Pontrendszer Működése</span>
        </h2>
        <p class="text-slate-400 mt-2 text-sm">Részletes útmutató a tárhely bővítéséről, a szintekről és a minőségi válaszok fontosságáról.</p>
    </div>

    <!-- Intro / Default Storage -->
    <div class="bg-slate-900/50 p-6 rounded-lg border border-slate-700">
        <h3 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
            <i class="fas fa-hdd text-emerald-400"></i> Alapértelmezett Tárhely
        </h3>
        <p class="text-slate-300 text-sm leading-relaxed">
            Minden regisztrált felhasználó automatikusan <strong class="text-emerald-400">10 MB</strong> tárhelyet kap. Ez a tárhely dinamikusan növekszik az aktivitásod alapján.
        </p>
    </div>

    <!-- Levels Table -->
    <div>
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i class="fas fa-layer-group text-purple-400"></i> Szintek és Bővítések
        </h3>
        <div class="overflow-x-auto rounded-lg border border-slate-700">
            <table class="w-full text-sm text-left text-slate-400">
                <thead class="text-xs text-slate-300 uppercase bg-slate-800 border-b border-slate-700">
                    <tr>
                        <th scope="col" class="px-6 py-3">Szint</th>
                        <th scope="col" class="px-6 py-3">Feltétel (VAGY)</th>
                        <th scope="col" class="px-6 py-3 text-right">Tárhely</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-slate-900/30 border-b border-slate-800 hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-white">Alap</td>
                        <td class="px-6 py-4">Regisztráció</td>
                        <td class="px-6 py-4 text-right text-emerald-400 font-bold">10 MB</td>
                    </tr>
                    <tr class="bg-slate-900/30 border-b border-slate-800 hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-white">1. Szint</td>
                        <td class="px-6 py-4">5 Kérdés / 10 Válasz</td>
                        <td class="px-6 py-4 text-right text-emerald-400 font-bold">15 MB</td>
                    </tr>
                    <tr class="bg-slate-900/30 border-b border-slate-800 hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-white">2. Szint</td>
                        <td class="px-6 py-4">30 Kérdés / 60 Válasz</td>
                        <td class="px-6 py-4 text-right text-emerald-400 font-bold">20 MB</td>
                    </tr>
                    <tr class="bg-slate-900/30 border-b border-slate-800 hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-white">3. Szint</td>
                        <td class="px-6 py-4">100 Kérdés / 200 Válasz</td>
                        <td class="px-6 py-4 text-right text-emerald-400 font-bold">30 MB</td>
                    </tr>
                    <tr class="bg-slate-900/30 border-b border-slate-800 hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-white">4. Szint</td>
                        <td class="px-6 py-4">200 Kérdés / 400 Válasz</td>
                        <td class="px-6 py-4 text-right text-emerald-400 font-bold">100 MB</td>
                    </tr>
                    <tr class="bg-slate-900/30 border-b border-slate-800 hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-white">5. Szint</td>
                        <td class="px-6 py-4">400 Kérdés / 800 Válasz</td>
                        <td class="px-6 py-4 text-right text-emerald-400 font-bold">500 MB</td>
                    </tr>
                    <tr class="bg-slate-900/30 border-b border-slate-800 hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-white">6. Szint</td>
                        <td class="px-6 py-4">500 Kérdés / 1000 Válasz</td>
                        <td class="px-6 py-4 text-right text-emerald-400 font-bold">800 MB</td>
                    </tr>
                    <tr class="bg-slate-900/30 border-b border-slate-800 hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-white">7. Szint</td>
                        <td class="px-6 py-4">700 Kérdés / 2000 Válasz</td>
                        <td class="px-6 py-4 text-right text-emerald-400 font-bold">1 GB</td>
                    </tr>
                    <tr class="bg-slate-900/30 border-b border-slate-800 hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-amber-400"><i class="fas fa-crown mr-1"></i> 8. Szint</td>
                        <td class="px-6 py-4">1000 Kérdés / 4000 Válasz</td>
                        <td class="px-6 py-4 text-right text-amber-400 font-bold">2 GB (Max)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Image Compression & Technical -->
    <div class="bg-indigo-900/20 border border-indigo-500/30 p-5 rounded-lg flex items-start gap-4">
        <div class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0">
            <i class="fas fa-compress-arrows-alt"></i>
        </div>
        <div>
            <h4 class="font-bold text-indigo-300 mb-1">Intelligens Képtömörítés</h4>
            <p class="text-sm text-slate-300 leading-relaxed">
                Nem az általad feltöltött eredeti fájlméret számít! Rendszerünk nagyon hatékony, szerveroldali <strong class="text-white">veszteséges tömörítést (WebP)</strong> használ.
                Így egy 5 MB-os képből gyakran csak <strong class="text-white">100-200 KB</strong> méretű, optimalizált fájl lesz. A tárhelyedből csak ez a tényleges, tömörített méret vonódik le.
            </p>
        </div>
    </div>

    <!-- Bonus System -->
    <div class="bg-slate-900/50 p-6 rounded-lg border border-slate-700">
        <h3 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
            <i class="fas fa-gift text-pink-400"></i> Bónusz Rendszer
        </h3>
        <p class="text-slate-300 text-sm leading-relaxed mb-2">
            A maximális 2 GB-on felül további bónusz tárhely szerezhető.
        </p>
        <ul class="list-disc list-inside text-slate-400 text-sm space-y-1 ml-2">
            <li><strong class="text-pink-400">+200 MB</strong> minden 100. elfogadott válasz után.</li>
            <li>Nincs felső határ, a 2 GB limiten felül is hozzáadódik.</li>
        </ul>
    </div>

    <!-- SPAM WARNING & QUALITY -->
    <div class="bg-red-900/20 border border-red-500/30 p-6 rounded-lg relative overflow-hidden">
        <div class="absolute -right-6 -top-6 text-9xl text-red-500/5 rotate-12 pointer-events-none">
            <i class="fas fa-exclamation-triangle"></i>
        </div>

        <h3 class="text-lg font-bold text-red-400 mb-4 flex items-center gap-2 relative z-10">
            <i class="fas fa-ban"></i> Figyelem: A minőség kötelező!
        </h3>

        <div class="space-y-4 relative z-10">
            <p class="text-slate-300 text-sm leading-relaxed">
                <strong class="text-red-400">Ne spamolj</strong> abban a hitben, hogy így több pontot érsz el! A rendszer csak az igazán hasznos válaszokat fogadja el. Törekedj arra, hogy válaszaid hossza elérje a <strong class="text-white">150 karaktert</strong>.
            </p>

            <div class="bg-slate-900/80 p-4 rounded border-l-4 border-green-500">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-bold text-green-400 uppercase">Példa egy hasznos válaszra (Pontosan 150 karakter):</span>
                    <span class="text-[10px] bg-slate-800 text-slate-400 px-2 py-0.5 rounded">150 kar.</span>
                </div>
                <p class="text-sm text-slate-300 italic font-serif">
                    "A hűtő felszerelésekor ügyelj arra, hogy a pasztából csak egy borsónyi mennyiséget tegyél a közepére, mert a rászorítás nyomása szépen eloszlatja azt."
                </p>
            </div>

            <p class="text-slate-400 text-xs italic border-t border-red-500/20 pt-3 mt-2">
                <i class="fas fa-info-circle mr-1"></i> Hiába kapod meg a pontokat a rövid válaszokért, ha azok nem hasznosak, a rendszer később levonja tőled, és a tárhelyed is csökkenhet!
            </p>
        </div>
    </div>

</div>
