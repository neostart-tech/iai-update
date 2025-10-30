<div class="card">
    <div class="card-body row">
        <div class="form-group col-6 col-lg-3 col-xl-2">
            <label class="col-form-label">Type d'évaluation :
                <small class="text-muted d-block"> {{ $evaluation->type->value }} </small>
            </label>
        </div>
        <div class="form-group col-6 col-lg-3 col-xl-2">
            <label class="col-form-label">Groupe concerné :
                <small class="text-muted d-block"> {{ $evaluation->group->nom }} </small>
            </label>
        </div>
        <div class="form-group col-6 col-lg-3 col-xl-2">
            <label class="col-form-label">Matière :
                <small class="text-muted d-block"> {{ $evaluation->matiere->nom }} </small>
            </label>
        </div>
        <div class="form-group col-6 col-lg-3 col-xl-2">
            <label class="col-form-label">Salle :
                <small class="text-muted d-block"> {{ $evaluation->salle->nom }} </small>
            </label>
        </div>
        <div class="form-group col-6 col-lg-3 col-xl-2">
            <label class="col-form-label">Plage horaire :
                <small class="text-muted d-block">
                    {{ $evaluation->debut->translatedFormat('d F Y \de H:i') . ' à ' . $evaluation->fin->translatedFormat('H:i') }}
                </small>
            </label>
        </div>


        @if (!empty($evaluation->fiche->surveillants))

            @foreach ($evaluation->fiche->surveillants as $key => $surveillant)
                <div class="form-group col-6 col-lg-3 col-xl-2">
                    <label class="col-form-label">Surveillant {{ ++$key }} :
                        <small class="text-muted d-block"> {{ $surveillant->completName() }} </small>
                    </label>
                </div>
            @endforeach
        @else
            <div class="form-group col-6 col-lg-3 col-xl-2">
                <label class="col-form-label">Surveillant :
                    <small class="text-muted d-block"> Aucun surveillant affecté </small>
                </label>
            </div>
        @endif





        <div class="form-group col-6 col-lg-3 col-xl-2">
            <label class="col-form-label">À corriger avant :
                <small class="text-muted d-block"> {{ $evaluation->correction_end_date->translatedFormat('d F Y') }}
                </small>
            </label>
        </div>
        <div class="form-group col-6 col-lg-3 col-xl-2">
            <label class="col-form-label">Date de publication :
                <small class="text-muted d-block">
                    {{ $evaluation->correction_submission_date
                        ? $evaluation->correction_submission_date->translatedFormat('d F Y')
                        : 'Pas encore publiée' }}
                </small>
            </label>
        </div>
    </div>
</div>
