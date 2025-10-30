<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Carte Étudiant</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding-top: 100px
            padding: 0;
            font-size: 12px;
        }

        .card {
            width: 550px;
            height: 300px;
            border: 2px solid #457b9d;
            border-radius: 10px;
            padding: 10px;
            margin: auto;
            box-sizing: border-box;
        }

        table.layout {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }

        td.photo {
            width: 35%;
            text-align: center;
            vertical-align: top;
            border-right: 2px solid #457b9d;
            padding: 5px;
            padding-top: 70px;
        }

        .photo-img {
            width: 120px;
            height: 140px;
            border: 2px solid #457b9d;
        }

        td.details {
            padding: 8px;
            vertical-align: top;
        }

        .title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 13px;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #a8dadc;
        }

        .logo-img {
            width: 40px;
            height: 40px;
            vertical-align: middle;
        }

        .info-row {
            margin-bottom: 4px;
        }

        .label {
         font-weight: 500; 
            display: inline-block;
            width:130px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .mrz {
            font-size: 10px;
            background: #000;
            color: #00ff88;
            padding: 3px;
            margin-top: 10px;
            font-family: monospace;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="card">
        <table class="layout">
            <tr>
                @php
                  $photos= public_path('storage/' . $photo);
                
                  @endphp
                <td class="photo">
                    <img class="photo-img" src="{{$photos  }}" alt="Photo étudiant">
                </td>
                <td class="details">
                  @php
                  $logo= public_path('storage/' . AppGetters::getAppLogo()) ;
                  @endphp
                    <div class="title" style="margin-bottom: 20px">
                        <img class="logo-img" src="{{ $logo }}" alt="Logo">
                        <span  style="margin-left: 30px">École Supérieure de Commerce</span><br><span style="margin-left:73px">et d’économie Numérique </span> 
                   
                      </div>
                    <div class="info">
                        <div class="info-row"><span class="label">Nom:</span> {{ $etudiant->nom }}</div>
                        <div class="info-row"><span class="label">Prénoms:</span> {{ $etudiant->prenom }}</div>
                        <div class="info-row"><span class="label">Date de naissance:</span> {{ \Carbon\Carbon::parse($etudiant->date_naissance)->format('d-m-Y') }}</div>
                        <div class="info-row"><span class="label">Matricule:</span> {{ $etudiant->matricule }}</div>
                        <div class="info-row"><span class="label">Filière:</span> {{$filiere}}</div>
                        <div class="info-row"><span class="label">Année scolaire:</span> {{$annee}}</div>
                    </div>
                    <div class="mrz">
                        &lt;{{ strtoupper($etudiant->nom) }}&lt;{{ strtoupper($etudiant->prenom) }}&lt;<<<<<<<<<<<<
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
