<div class="modal fade" id="config-modal" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="mb-0">Suiveillants de l'évaluation</h5>
        <a href="#" class="avtar avtar-s btn-link-danger btn-pc-default" data-bs-dismiss="modal">
          <i class="ti ti-x f-20"></i>
        </a>
      </div>
      <div class="modal-body">
        <form method="post" action="{{ route('admin.fiches.store') }}" id="config-form">
          <input type="hidden" name="evaluation_id" id="evaluation_id" value="">
          @csrf
          @method('put')
          <div class="form-group">
            <x-forms.label for="show-type" content="Surveillant.e 1"/>
            <select name="surveillant_1" class="form-select" id="surveillant-1">
              <option value>Sélection un surveillant pour cette évaluation</option>
              @foreach ($enseignants as $enseignant)
                <option value="{{ $enseignant->slug }}">{{ $enseignant->nom . ' ' . $enseignant->prenom }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
						<x-forms.label for="show-group" content="Surveillant.e 2" required="{{ 0 }}" />
            <select name="surveillant_2" class="form-select" id="surveillant-2">
							<option value>Sélection un surveillant pour cette évaluation</option>
              @foreach ($enseignants as $enseignant)
                <option value="{{ $enseignant->slug }}">{{ $enseignant->nom . ' ' . $enseignant->prenom }}</option>
              @endforeach
            </select>
          </div>

          <button class="btn btn-primary" type="submit">Enrégistrer</button>
        </form>
      </div>
    </div>
  </div>
</div>
