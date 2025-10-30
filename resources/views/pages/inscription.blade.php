@extends("layouts.master")

@section('content')

    <div class="w-full  bg-center bg-cover" style="background-image: url(https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80)">
        <div class=" w-full flex items-center justify-center">
            <div class="w-full h-full text-white bg-gray-700 bg-opacity-80 flex flex-col py-12 lg:py-0 gap-8 justify-center items-center">
                <div class="md:grid md:grid-cols-2 px-8 py-8 w-full lg:w-1/2">
                    <div class="mt-5 md:col-span-2 md:mt-0">
                        @if(session('success'))
                            <div class="mb-6 rounded-md bg-green-100 text-green-900 p-4">{{ session('success') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="mb-6 rounded-md bg-red-100 text-red-900 p-4">
                                <ul class="list-disc pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('inscription.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                                <div class="overflow-hidden shadow sm:rounded-lg">
                                    <div class="bg-gray-100 px-4 py-5 sm:p-6 mt-16 mb-16 rounded-lg">
                                        <div class=" flex flex-col">
                                            <h1 class="text-2xl text-black font-bold mb-8 flex justify-center">
                                                Formulaire d'inscription étudiants
                                            </h1>
                                            <hr>
                                            <h1 class="text-xl text-black mt-8">
                                                Remplissez soigneusement le formulaire d'inscription
                                            </h1>
                                            <div class="mt-2 text-sm text-gray-700 bg-yellow-100 border border-yellow-300 rounded p-3">
                                                <strong>Important:</strong> Seules les séries <strong>C</strong> et <strong>D</strong> sont acceptées.
                                            </div>
                                        </div>

                                            <div class="flex flex-wrap -mx-3 mb-6 mt-8">
                                                <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                                                    <label class="block uppercase tracking-wide text-gray-700 text-md font-bold mb-2" for="numero_table">
                                                        Numéro de table <span class="text-red-600">*</span>
                                                    </label>
                                                    <input name="numero_table" value="{{ old('numero_table') }}" class="appearance-none block w-full bg-gray-200 text-gray-700 border @error('numero_table') border-red-500 @else border-gray-200 @enderror rounded py-3 px-4 mb-1 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="numero_table" type="text" placeholder="Ex: 12345/2024" required>
                                                </div>
                                                <div class="w-full md:w-1/2 px-3">
                                                    <label class="block uppercase tracking-wide text-gray-700 text-md font-bold mb-2" for="annee_bac">
                                                        Année BAC <span class="text-red-600">*</span>
                                                    </label>
                                                    <input name="annee_bac" value="{{ old('annee_bac') }}" class="appearance-none block w-full bg-gray-200 text-gray-700 border @error('annee_bac') border-red-500 @else border-gray-200 @enderror rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="annee_bac" type="number" min="1990" max="{{ date('Y') }}" placeholder="Ex: 2024" required>
                                                </div>
                                            </div>

                                            <div class="flex flex-wrap -mx-3 mb-6">
                                                <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                                                    <label class="block uppercase tracking-wide text-gray-700 text-md font-bold mb-2" for="serie">
                                                        Série acceptée <span class="text-red-600">*</span>
                                                    </label>
                                                    <select name="serie" id="serie" class="block appearance-none w-full bg-gray-200 border @error('serie') border-red-500 @else border-gray-200 @enderror text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500" required>
                                                        <option value="">-- Sélectionnez --</option>
                                                        @foreach (['C','D'] as $s)
                                                            <option value="{{ $s }}" {{ old('serie')===$s ? 'selected' : '' }}>{{ $s }}</option>
                                                        @endforeach
                                                    </select>
                                                    <p class="text-sm text-gray-600 mt-1">Séries acceptées: C, D</p>
                                                </div>
                                                <div class="w-full md:w-1/2 px-3">
                                                    <label class="block uppercase tracking-wide text-gray-700 text-md font-bold mb-2" for="lettre_motivation">
                                                        Lettre de motivation <span class="text-red-600">*</span>
                                                    </label>
                                                    <textarea name="lettre_motivation" id="lettre_motivation" rows="4" class="appearance-none block w-full bg-gray-200 text-gray-700 border @error('lettre_motivation') border-red-500 @else border-gray-200 @enderror rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" placeholder="Expliquez votre motivation..." required>{{ old('lettre_motivation') }}</textarea>
                                                </div>
                                            </div>

                                            <div class="flex flex-wrap -mx-3 mb-6">
                                                <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                                                    <label class="block uppercase tracking-wide text-gray-700 text-md font-bold mb-2" for="email">
                                                        E-mail (optionnel)
                                                    </label>
                                                    <input name="email" value="{{ old('email') }}" class="appearance-none block w-full bg-gray-200 text-gray-700 border @error('email') border-red-500 @else border-gray-200 @enderror rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="email" type="email" placeholder="nom@example.com">
                                                </div>

                                                <div class="w-full md:w-1/2 px-3">
                                                    <label class="block uppercase tracking-wide text-gray-700 text-md font-bold mb-2" for="tuteur_lieu">
                                                        Lieu du tuteur (obligatoire)
                                                    </label>
                                                    <input name="tuteur_lieu" value="{{ old('tuteur_lieu') }}" class="appearance-none block w-full bg-gray-200 text-gray-700 border @error('tuteur_lieu') border-red-500 @else border-gray-200 @enderror rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="tuteur_lieu" type="text" placeholder="Ex: Lomé" required>
                                                </div>
                                            </div>

                                            <div class="flex flex-wrap -mx-3 mb-6">
                                                <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                                                    <label class="block uppercase tracking-wide text-gray-700 text-md font-bold mb-2" for="phone1">
                                                        Téléphone 1 <span class="text-red-600">*</span>
                                                    </label>
                                                    <input name="phone1" value="{{ old('phone1') }}" class="appearance-none block w-full bg-gray-200 text-gray-700 border @error('phone1') border-red-500 @else border-gray-200 @enderror rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="phone1" type="text" placeholder="Ex: +228 90 00 00 00" required>
                                                </div>
                                                <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                                                    <label class="block uppercase tracking-wide text-gray-700 text-md font-bold mb-2" for="phone2">
                                                        Téléphone 2 <span class="text-red-600">*</span>
                                                    </label>
                                                    <input name="phone2" value="{{ old('phone2') }}" class="appearance-none block w-full bg-gray-200 text-gray-700 border @error('phone2') border-red-500 @else border-gray-200 @enderror rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="phone2" type="text" placeholder="Ex: +228 91 00 00 00" required>
                                                </div>
                                                <div class="w-full md:w-1/3 px-3">
                                                    <label class="block uppercase tracking-wide text-gray-700 text-md font-bold mb-2" for="phone3">
                                                        Téléphone 3 (optionnel)
                                                    </label>
                                                    <input name="phone3" value="{{ old('phone3') }}" class="appearance-none block w-full bg-gray-200 text-gray-700 border @error('phone3') border-red-500 @else border-gray-200 @enderror rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="phone3" type="text" placeholder="Ex: +228 92 00 00 00">
                                                </div>
                                            </div>

                                            <div class="flex flex-wrap -mx-3 mb-6">
                                                <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                                                    <label class="block uppercase tracking-wide text-gray-700 text-md font-bold mb-2" for="accepte">
                                                        Acceptation des conditions <span class="text-red-600">*</span>
                                                    </label>
                                                    <div class="flex items-center gap-3 bg-gray-200 rounded px-4 py-3">
                                                        <input id="accepte" type="checkbox" name="accepte" class="h-5 w-5" {{ old('accepte') ? 'checked' : '' }}>
                                                        <label for="accepte">Je confirme l'exactitude de mes informations</label>
                                                    </div>
                                                    <p class="text-sm text-gray-600 mt-1">Cochez pour activer le bouton de soumission.</p>
                                                </div>
                                                <div class="w-full md:w-1/2 px-3">
                                                    <label class="block uppercase tracking-wide text-gray-700 text-md font-bold mb-2" for="certificat_medical">
                                                        Certificat médical (optionnel)
                                                    </label>
                                                    <input name="certificat_medical" id="certificat_medical" type="file" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-gray-700">
                                                </div>
                                            </div>

                                            <div class="flex flex-wrap -mx-3 mb-6">
                                                <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                                                    <label class="block uppercase tracking-wide text-gray-700 text-md font-bold mb-2">
                                                        Bulletins du lycée par niveau — min 2 par niveau (PDF/JPG/PNG) <span class="text-red-600">*</span>
                                                    </label>
                                                    <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">
                                                        <div>
                                                            <label class="block text-sm font-semibold text-gray-800 mb-1" for="bulletins_seconde">Seconde <span class="text-red-600">(min 2)</span></label>
                                                            <input name="bulletins_seconde[]" id="bulletins_seconde" type="file" multiple accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-gray-700" required>
                                                            <div class="mt-1 text-sm">Sélectionnés: <span id="cnt-seconde" class="px-2 py-0.5 rounded bg-gray-200">0</span></div>
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-semibold text-gray-800 mb-1" for="bulletins_premiere">Première <span class="text-red-600">(min 2)</span></label>
                                                            <input name="bulletins_premiere[]" id="bulletins_premiere" type="file" multiple accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-gray-700" required>
                                                            <div class="mt-1 text-sm">Sélectionnés: <span id="cnt-premiere" class="px-2 py-0.5 rounded bg-gray-200">0</span></div>
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-semibold text-gray-800 mb-1" for="bulletins_terminale">Terminale <span class="text-red-600">(min 2)</span></label>
                                                            <input name="bulletins_terminale[]" id="bulletins_terminale" type="file" multiple accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-gray-700" required>
                                                            <div class="mt-1 text-sm">Sélectionnés: <span id="cnt-terminale" class="px-2 py-0.5 rounded bg-gray-200">0</span></div>
                                                        </div>
                                                    </div>
                                                    <p class="text-sm text-gray-600 mt-2">Total attendu ≥ 6 fichiers avec minimum 2 fichiers pour chaque niveau.</p>
                                                </div>
                                                <div class="w-full md:w-1/2 px-3">
                                                    <label class="block uppercase tracking-wide text-gray-700 text-md font-bold mb-2" for="releve_bac1">
                                                        Relevé BAC 1 ou Attestation (PDF/JPG/PNG) <span class="text-red-600">*</span>
                                                    </label>
                                                    <input name="releve_bac1" id="releve_bac1" type="file" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-gray-700" required>
                                                </div>
                                            </div>

                                            <div class="flex flex-wrap -mx-3 mb-6">
                                                <div class="w-full md:w-1/2 px-3">
                                                    <label class="block uppercase tracking-wide text-gray-700 text-md font-bold mb-2" for="releve_bac2">
                                                        Relevé BAC 2 ou Attestation (PDF/JPG/PNG) <span class="text-red-600">*</span>
                                                    </label>
                                                    <input name="releve_bac2" id="releve_bac2" type="file" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-gray-700" required>
                                                </div>
                                            </div>

                                            <div class="bg-gray-50 px-4 py-3 text-right sm:px-6">
                                                <button id="submitBtn" type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-green-800 disabled:bg-gray-400 py-2 px-4 text-md font-medium text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2" disabled>Soumettre l'inscription</button>
                                            </div>
                                    </div>

                                </div>
                        </form>
                        <script>
                            (function(){
                                const accept = document.getElementById('accepte');
                                const submitBtn = document.getElementById('submitBtn');
                                const inSeconde = document.getElementById('bulletins_seconde');
                                const inPremiere = document.getElementById('bulletins_premiere');
                                const inTerminale = document.getElementById('bulletins_terminale');
                                const cntS = document.getElementById('cnt-seconde');
                                const cntP = document.getElementById('cnt-premiere');
                                const cntT = document.getElementById('cnt-terminale');
                                function toggleSubmit() {
                                    submitBtn.disabled = !accept.checked;
                                }
                                accept && accept.addEventListener('change', toggleSubmit);
                                toggleSubmit();
                                // Client validation: at least 2 per level and total >= 6
                                const form = accept && accept.form;
                                form && form.addEventListener('submit', function(e){
                                    const s = (inSeconde && inSeconde.files) ? inSeconde.files.length : 0;
                                    const p = (inPremiere && inPremiere.files) ? inPremiere.files.length : 0;
                                    const t = (inTerminale && inTerminale.files) ? inTerminale.files.length : 0;
                                    const total = s + p + t;
                                    if (s < 2 || p < 2 || t < 2 || total < 6) {
                                        e.preventDefault();
                                        let msg = [];
                                        if (s < 2) msg.push('Seconde (min 2)');
                                        if (p < 2) msg.push('Première (min 2)');
                                        if (t < 2) msg.push('Terminale (min 2)');
                                        if (total < 6) msg.push('Total < 6');
                                        alert('Veuillez corriger les bulletins: ' + msg.join(', ') + '.');
                                        return;
                                    }
                                });
                                function updateBadge(el, count) {
                                    if (!el) return;
                                    el.textContent = count;
                                    el.classList.remove('bg-gray-200','bg-red-200','bg-green-200');
                                    el.classList.add(count >= 2 ? 'bg-green-200' : 'bg-red-200');
                                }
                                function recalc() {
                                    const s = (inSeconde && inSeconde.files) ? inSeconde.files.length : 0;
                                    const p = (inPremiere && inPremiere.files) ? inPremiere.files.length : 0;
                                    const t = (inTerminale && inTerminale.files) ? inTerminale.files.length : 0;
                                    updateBadge(cntS, s);
                                    updateBadge(cntP, p);
                                    updateBadge(cntT, t);
                                }
                                inSeconde && inSeconde.addEventListener('change', recalc);
                                inPremiere && inPremiere.addEventListener('change', recalc);
                                inTerminale && inTerminale.addEventListener('change', recalc);
                                recalc();
                            })();
                        </script>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
