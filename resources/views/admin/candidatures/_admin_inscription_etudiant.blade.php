  <div class="modal fade" id="candidatureModal" tabindex="-1" aria-labelledby="candidatureModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">


              <form id="wizardForm" method="POST" action='{{ route('candidatures.store') }}'
                  enctype="multipart/form-data">
                  @csrf
                  <div class="modal-body">
                      <!-- Onglets -->
                      <ul class="nav nav-tabs mb-3" id="wizardTabs" role="tablist">
                          <li class="nav-item">
                              <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#etape1"
                                  type="button">1. Infos</button>
                          </li>
                          <li class="nav-item">
                              <button class="nav-link " data-bs-toggle="tab" data-bs-target="#etape2" type="button">2.
                                  Responsable frais</button>
                          </li>
                          <li class="nav-item">
                              <button class="nav-link " data-bs-toggle="tab" data-bs-target="#etape3" type="button">3.
                                  Tuteur</button>
                          </li>
                          <li class="nav-item">
                              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#etape4" type="button">4.
                                  Académiques</button>
                          </li>
                          <li class="nav-item">
                              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#etape5" type="button">5.
                                  Fichiers</button>
                          </li>
                          <li class="nav-item">
                              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#etape6" type="button">6.
                                  Confirmation</button>
                          </li>
                      </ul>

                      <!-- Contenu des étapes -->
                      <div class="tab-content">
                          <div class="tab-pane fade show active" id="etape1">
                              <div class="row">
                                  <div class="col-md-6">
                                      <label>Nom *</label>
                                      <input type="text" name="nom" class="form-control"
                                          value="{{ old('nom') }}" required>
                                  </div>
                                  <div class="col-md-6">
                                      <label>Prénom *</label>
                                      <input type="text" name="prenom" class="form-control " required
                                          value="{{ old('prenom') }}">
                                  </div>
                                  <div class="col-md-6">
                                      <label>Nom jeune fille *</label>
                                      <input type="text" name="nom_jeune_fille" class="form-control "
                                          value="{{ old('nom_jeune_fille') }}" required>
                                  </div>
                                  <div class="col-md-6">
                                      <label>Genre *</label>
                                      <select name="genre" id="" class="form-select">
                                          <option value="Féminin">Masculin</option>
                                          <option value="Masculin">Féminin</option>
                                      </select>
                                  </div>
                                  <div class="col-md-6">
                                      <label>Date naissance *</label>
                                      <input value="{{ old('date_naissance') }}" type="date" name="date_naissance"
                                          class="form-control " required>
                                  </div>
                                  <div class="col-md-6">
                                      <label>Lieu de naissance *</label>
                                      <input value="{{ old('lieu_naissance') }}" type="date" name="lieu_naissance"
                                          class="form-control " required>
                                  </div>
                                  <div class="col-md-6">
                                      <label>E-mail *</label>
                                      <input type="email" name="email" class="form-control " required
                                          value="{{ old('email') }}">
                                  </div>
                                  <div class="col-md-6">
                                      <label>Nationalité *</label>
                                      <input type="text" name="nationalite" class="form-control "
                                          value="{{ old('nationalite') }}" required>
                                  </div>
                                  <div class="col-md-6">
                                      <label>Hobbit *</label>
                                      <textarea name="hobbit" class="form-control " required>{{ old('tel_resp') }} </textarea>
                                  </div>
                                  <div class="form-group col-md-6">
                                      <x-forms.label for="tel" content="Téléphone" />
                                      <input type="tel" style="max-width: 165%;" class="form-control phone-input"
                                          id="tel-input" name="tel" value="{{ old('tel') }}" placeholder="" />
                                      {!! errorAlert($errors->first('tel'), 'tel') !!}
                                  </div>
                                  <input type="hidden" name="indicatif" id="indicatif">

                                  <div class="col-md-6">
                                      <label>BP *</label>
                                      <input type="text" name="bp" class="form-control "
                                          value="{{ old('bp') }}" required>
                                  </div>
                                  <div class="col-md-6">
                                      <label>Fax *</label>
                                      <input type="text" name="fax" class="form-control "
                                          value="{{ old('fax') }}" required>
                                  </div>


                              </div>
                          </div>

                          <div class="tab-pane fade show active" id="etape2">
                              <div class="row">
                                  <div class="col-md-6">
                                      <label>Nom *</label>
                                      <input type="text" name="nom_resp" class="form-control  "
                                          value="{{ old('nom_resp') }}" required>
                                  </div>
                                  <div class="col-md-6">
                                      <label>Prénom *</label>
                                      <input type="text" name="prenom_resp" class="form-control "
                                          value="{{ old('prenom_resp') }}" required>
                                  </div>
                                  <div class="col-md-6">
                                      <label>Profession *</label>
                                      <input type="text" name="profession_resp" class="form-control "
                                          value="{{ old('profession_resp') }}" required>
                                  </div>

                                  <div class="col-md-6">
                                      <label>Employeur *</label>
                                      <input type="text" name="employeur_resp" class="form-control "
                                          value="{{ old('employeur_resp') }}" required>
                                  </div>
                                  <div class="col-md-6">
                                      <label>Email *</label>
                                      <input type="email" name="email_resp" class="form-control "
                                          value="{{ old('email_resp') }}" required>
                                  </div>

                                  <div class="col-md-6">
                                      <div class="form-group">
                                          <label class="form-label" for="tel_resp">Téléphone
                                              <x-forms.required-field /></label>
                                          <input type="text" class="form-control" placeholder="Téléphone"
                                              name="tel_resp" value="{{ old('tel_resp') }}" id="tel_resp" />
                                          {!! errorAlert($errors->first('tel_resp')) !!}
                                      </div>
                                  </div>


                                  <div class="col-md-6">
                                      <label>Adresse *</label>
                                      <input type="text" name="adresse_resp"
                                          class="form-control "value="{{ old('adresse_resp') }}" required>
                                  </div>
                                  <div class="col-md-6">
                                      <label>BP *</label>
                                      <input type="text" name="bp_resp" class="form-control "
                                          value="{{ old('bp_resp') }}" required>
                                  </div>
                                  <div class="col-md-6">
                                      <label>Fax *</label>
                                      <input type="text" name="fax_resp" class="form-control "
                                          value="{{ old('fax_resp') }}" required>
                                  </div>


                              </div>
                          </div>
                          <div class="tab-pane fade show active" id="etape3">
                              <div class="row">
                                  <div class="col-md-6">
                                      <label>Nom *</label>
                                      <input type="text" name="nom_tuteur" class="form-control"
                                          value="{{ old('nom_tuteur') }}" required>
                                  </div>
                                  <div class="col-md-6">
                                      <label>Prénom *</label>
                                      <input type="text" name="prenom_tuteur" class="form-control "
                                          value="{{ old('prenom_tuteur') }}" required>
                                  </div>
                                  <div class="col-md-6">
                                      <label>Profession *</label>
                                      <input type="text" name="profession_tuteur" class="form-control "
                                          value="{{ old('profession_tuteur') }}" required>
                                  </div>

                                  <div class="col-md-6">
                                      <label>Employeur *</label>
                                      <input type="text" name="employeur_tuteur" class="form-control "
                                          value="{{ old('employeur_tuteur') }}" required>
                                  </div>
                                  <div class="col-md-6">
                                      <label>Email *</label>
                                      <input type="email" name="email_tuteur" class="form-control "
                                          value="{{ old('email_tuteur') }}" required>
                                  </div>
                                  <div class="form-group col-md-6">
                                      <x-forms.label for="tel" content="Téléphone" />
                                      <input type="tel" style="max-width: 165%;" class="form-control phone-input"
                                          id="tel-input" name="tel_tuteur" value="{{ old('tel_tuteur') }}"
                                          placeholder="" />
                                      {!! errorAlert($errors->first('tel_tuteur'), 'tel_tuteur') !!}
                                  </div>
                                  <input type="hidden" name="indicatif" id="indicatif">

                                  <div class="col-md-6">
                                      <label>Adresse *</label>
                                      <input type="text" name="adresse_tuteur" class="form-control "
                                          value="{{ old('adresse_tuteur') }}" required>
                                  </div>
                                  <div class="col-md-6">
                                      <label>BP *</label>
                                      <input type="text" name="bp_tuteur" class="form-control "
                                          value="{{ old('bp_tuteur') }}" required>
                                  </div>
                                  <div class="col-md-6">
                                      <label>Fax *</label>
                                      <input type="text" name="fax_tuteur" class="form-control "
                                          value="{{ old('fax_tuteur') }}" required>
                                  </div>


                              </div>
                          </div>

                          <div class="tab-pane fade" id="etape4">
                              <div class="row">

                                  <div class="col-md-6">
                                      <label>Filière *</label>
                                      <select name="filiere_id" class="form-select">
                                          @foreach ($filieres as $filiere)
                                              <option value="{{ $filiere->id }}">{{ $filiere->nom }}
                                              </option>
                                          @endforeach
                                      </select>
                                  </div>
                                  <div class="col-md-6">
                                      <label>Niveau *</label>
                                      <select class='form-select' name="niveau_id">
                                          @foreach ($niveaux as $niveau)
                                              <option value="{{ $niveau->id }}">{{ $niveau->libelle }}
                                              </option>
                                          @endforeach
                                      </select>
                                  </div>
                                  <div class="form-group mt-3">
                                      <label class="form-label" for="type_diplome">Type du Diplôme
                                          <x-forms.required-field />
                                      </label>
                                      <select class="mb-3 form-select form-select-lg" name="type_diplome"
                                          id="type_diplome">
                                          @foreach (App\Enums\TypeDiplomeEnum::cases() as $type)
                                              <option value="{{ $type->value }}">{{ $type->value }}</option>
                                          @endforeach
                                      </select>
                                      {!! errorAlert($errors->first('type_diplome'), 'type_diplome') !!}
                                  </div>
                              </div>
                          </div>

                          <div class="tab-pane fade" id="etape5">
                              <div class="row">
                                  <div class="mb-3 col-md-6 ">
                                      <label>Photo d'identité *</label>
                                      <input type="file" name="photo_identite_file" class="form-control"
                                          accept="image/*" required>
                                  </div>
                                  <div class="mb-3 col-md-6">
                                      <label>Lettre de motivation *</label>
                                      <input type="file" name="lettre_file" class="form-control" accept=".pdf"
                                          required>
                                  </div>
                                  <div class="mb-3 col-md-6">
                                      <label>Naissance *</label>
                                      <input type="file" name="naissance_file" class="form-control"
                                          accept=".pdf" required>
                                  </div>
                                  <div class="mb-3 col-md-6">
                                      <label>Diplome *</label>
                                      <input type="file" name="diplome_file" class="form-control" accept=".pdf"
                                          required>
                                  </div>

                                  <div class="mb-3 col-md-6">
                                      <label>Nationalite *</label>
                                      <input type="file" name="nationalite_file" class="form-control"
                                          accept=".pdf" required>
                                  </div>
                                 
                                  <div class="mb-3 col-md-6">
                                      <label>certificat_medical *</label>
                                      <input type="file" name="certificat_medical_file" class="form-control"
                                          accept=".pdf" required>
                                  </div>

                                  <div class="mb-3 col-md-6">
                                      <label>Coupon *</label>
                                      <input type="file" name="coupon_file" class="form-control" accept=".pdf"
                                          required>
                                  </div>
                              </div>

                              <div class="tab-pane fade" id="etape6">
                                  <p>Veuillez vérifier vos informations avant de soumettre.</p>
                                  <div class="form-check">
                                      <input class="form-check-input" type="checkbox" id="confirm" checked="true"
                                          required>
                                      <label class="form-check-label" for="confirm">Je confirme
                                          l'exactitude
                                          des informations.</label>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>

                  <div class="modal-footer">

                      <button type="button" class="btn btn-secondary" id="prevBtn">Précédent</button>
                      <button type="button" class="btn btn-primary" id="nextBtn">Suivant</button>
                      <button type="submit" class="btn btn-success d-none" id="submitBtn">Soumettre</button>
                  </div>
              </form>
          </div>
      </div>
  </div>
